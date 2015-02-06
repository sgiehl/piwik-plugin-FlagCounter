<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\FlagCounter;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\API;
use Piwik\Site;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{

    protected $cacheKey = 'FlagCounterCountries';

    protected function getCacheLifetime()
    {
        $settings = new Settings('FlagCounter');
        $lifeTime = $settings->cacheLifeTime->getValue();
        return $lifeTime ? $lifeTime : 3600;
    }

    /**
     * Return cached country data
     *
     * @return mixed|null
     */
    protected function getCachedData()
    {
        if (class_exists('\Piwik\CacheFile')) {
            // Caching in Piwik < 2.10
            $cache = new \Piwik\CacheFile('cache', $this->getCacheLifetime());
            return $cache->get($this->cacheKey);

        } else if (class_exists('\Piwik\Cache')) {
            // Caching in Piwik >= 2.10
            $cache = \Piwik\Cache::getLazyCache();
            if ($cache->contains($this->cacheKey)) {
                return $cache->fetch($this->cacheKey);
            }
        }

        return null;
    }

    /**
     * Set the given data to cache
     *
     * @param array $data
     */
    protected function saveDataInCache($data)
    {
        if (class_exists('\Piwik\CacheFile')) {
            $cache = new \Piwik\CacheFile('cache', $this->getCacheLifetime());
            $cache->set($this->cacheKey, $data);
        } else if (class_exists('\Piwik\Cache')) {
            $cache = \Piwik\Cache::getLazyCache();
            $cache->save($this->cacheKey, $data, $this->getCacheLifetime());
        }
    }

    /**
     * Return the country data for the given request params
     *
     * @return array
     * @throws \Exception
     */
    protected function getCountryData()
    {
        $countries = array();

        $cacheData = $this->getCachedData();

        if (!empty($cacheData)) {
            return $cacheData;
        }

        try {
            // fetch data from API as superuser so we can get them even without view access
            /* @var \Piwik\DataTable $countryData */
            $countryData = Access::getInstance()->doAsSuperUser(function () {
                $idSite = Common::getRequestVar('idSite', null, 'int');
                $period = Common::getRequestVar('period', null, 'string');
                $date = Common::getRequestVar('date', null, 'string');
                $segment = Request::getRawSegmentFromRequest();

                return API::getInstance()->getCountry($idSite, $period, $date, $segment);
            });
        } catch (\Exception$e) {
            return array();
        }

        foreach ($countryData->getRows() AS $country) {
            $countries[] = array(
                'name' => $country->getColumn('label'),
                'icon' => $country->getMetadata('logo'),
                'code' => $country->getMetadata('code') != 'xx' ? $country->getMetadata('code') : '  ',
                'hits' => $country->getColumn(2),
            );
        }

        $this->saveDataInCache($countries, 3600);

        return $countries;
    }

    /**
     * Renders the country data in an iframe
     *
     * @return string
     */
    public function iframe()
    {
        // should not use self::renderTemplate since that uses setBasicVariablesView. this will cause
        // an error when setBasicVariablesAdminView is called, and MenuTop is requested (the idSite query
        // parameter is required)
        $view = new View("@FlagCounter/counter");
        $view->setXFrameOptions('allow');
        $view->countries = $this->getCountryData();
        return $view->render();
    }

    /**
     * Generates a image showing the country flags and their hits
     *
     * @throws \Exception
     */
    public function image()
    {
        header('Content-type: image/png');

        $countries = $this->getCountryData();

        $rows = Common::getRequestVar('rows', 5, 'int');
        $cols = Common::getRequestVar('cols', 2, 'int');
        $showCountryCode = Common::getRequestVar('showcode', 0, 'int');
        $showFlag = Common::getRequestVar('showflag', 1, 'int');

        if (!$showCountryCode) {
            $showFlag = 1;
        }

        if (count($countries) < $rows * $cols) {
            $cols = ceil(count($countries) / $rows);
        }

        $length     = 0;

        foreach($countries AS $country) {
            $length = max($length, strlen($country['hits']));
        }

        $colWidth = 5 + $showFlag*20 + $showCountryCode*10 + $length*10 + 20;

        $im = imagecreatetruecolor($cols * $colWidth + 1, $rows * 25 + 1);

        $color = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $color);

        try {
            $currentRow = 0;
            $currentCol = 0;

            foreach ($countries as $country) {

                if ($showFlag) {
                    $icon = imagecreatefrompng(PIWIK_INCLUDE_PATH . DIRECTORY_SEPARATOR . $country['icon']);
                    imagecopy($im, $icon, 5 + ($currentCol) * $colWidth, (5 + $currentRow * 25), 0, 0, 16, 12);
                    imagedestroy($icon);
                }

                imagestring($im, 3, 5 + $showFlag*20 + ($currentCol) * $colWidth, (5 + $currentRow * 25), ($showCountryCode ? strtoupper($country['code']).' ' : '') . str_pad(number_format($country['hits'], 0, '', '.'), $length, ' ', STR_PAD_LEFT), imagecolorallocate($im, 0, 0, 0));

                $currentRow = ++$currentRow % $rows;

                if ($currentRow == 0) {
                    $currentCol++;
                }

                if ($currentCol >= $cols) {
                    break;
                }
            }
        } catch (\Exception $e) {
        }

        imagesavealpha($im, TRUE);
        imagepng($im);
        imagedestroy($im);
        exit;
    }

    public function index()
    {
        Piwik::checkUserIsNotAnonymous();

        $view = new View('@FlagCounter/index');

        $view->idSite = Common::getRequestVar('idSite', $this->idSite, 'int');
        $view->defaultReportSiteName = Site::getNameFor($view->idSite);
        $view->date = $this->date;

        self::setPeriodVariablesView($view);
        $this->setBasicVariablesView($view);
        return $view->render();
    }
}
