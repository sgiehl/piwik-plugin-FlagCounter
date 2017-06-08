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
use Piwik\Cache;
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
        $settings = new SystemSettings('FlagCounter');
        $lifeTime = $settings->cacheLifeTime->getValue();
        return $lifeTime ? $lifeTime : 3600;
    }

    protected function getCacheKey($idSite, $period, $date)
    {
        return preg_replace('/[^a-z0-9]/i', '', $this->cacheKey.$idSite.$period.$date);
    }

    /**
     * Return cached country data
     *
     * @return mixed|null
     */
    protected function getCachedData($idSite, $period, $date)
    {
        $cache = Cache::getLazyCache();
        if ($cache->contains($this->getCacheKey($idSite, $period, $date))) {
            return $cache->fetch($this->getCacheKey($idSite, $period, $date));
        }

        return null;
    }

    /**
     * Set the given data to cache
     *
     * @param array $data
     */
    protected function saveDataInCache($data, $idSite, $period, $date)
    {
            $cache = Cache::getLazyCache();
            $cache->save($this->getCacheKey($idSite, $period, $date), $data, $this->getCacheLifetime());
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
        $period = Common::getRequestVar('period', null, 'string');
        $date = Common::getRequestVar('date', null, 'string');
        $idSite = Common::getRequestVar('idSite', null, 'int');

        $cacheData = $this->getCachedData($idSite, $period, $date);

        if (!empty($cacheData)) {
            return $cacheData;
        }

        try {
            // fetch data from API as superuser so we can get them even without view access
            /* @var \Piwik\DataTable $countryData */
            $countryData = Access::getInstance()->doAsSuperUser(function () use ($idSite, $period, $date) {

                $segment = Request::getRawSegmentFromRequest();

                return API::getInstance()->getCountry($idSite, $period, $date, $segment);
            });
        } catch (\Exception $e) {
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

        usort($countries, function($a, $b){
            if ($a['hits'] == $b['hits']) {
                return 0;
            }
            return ($a['hits'] > $b['hits']) ? -1 : 1;
        });

        $this->saveDataInCache($countries, $idSite, $period, $date);

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
        $fontSize = Common::getRequestVar('fontsize', 12, 'int');
        $fontColor = Common::getRequestVar('fontcolor', '0,0,0', 'string');
        $font = preg_replace("/[^a-z0-9_-]/i", '', Common::getRequestVar('font', '', 'string'));
        $showCountryCode = Common::getRequestVar('showcode', 0, 'int');
        $showFlag = Common::getRequestVar('showflag', 1, 'int');

        if (substr_count($fontColor, ',') != 2) {
            $fontColor = '0,0,0';
        }

        $fontColor = explode(',', $fontColor);

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

        try {
            $currentRow = 0;
            $currentCol = 0;

            $fontSize = $fontSize < 2 || $fontSize > 30 ? 12 : $fontSize;
            $fontFilePattern = dirname(__FILE__).'/fonts/%s.ttf';

            $fontFile = sprintf($fontFilePattern, 'Cantarell-Regular');

            if (file_exists(sprintf($fontFilePattern, $font))) {
                $fontFile = sprintf($fontFilePattern, $font);
            }

            $dimensions = imagettfbbox($fontSize, 0, $fontFile, str_pad('', $length, '0'));
            $maxNumberWidth = abs($dimensions[4] - $dimensions[0]);

            $countryCodeWidth = 1;
            if ($showCountryCode) {
                $dimensions = imagettfbbox($fontSize, 0, $fontFile, 'XXX');
                $countryCodeWidth = abs($dimensions[4] - $dimensions[0]);
            }

            $colWidth = 5 + $showFlag*25 + $showCountryCode*$countryCodeWidth + $maxNumberWidth + 20;

            $im = imagecreatetruecolor($cols * $colWidth + 1, $rows * 25 + 1);

            $color = imagecolorallocatealpha($im, 0, 0, 0, 127);
            imagefill($im, 0, 0, $color);


            foreach ($countries as $country) {

                if ($showFlag) {
                    $icon = imagecreatefrompng(PIWIK_INCLUDE_PATH . DIRECTORY_SEPARATOR . $country['icon']);
                    imagecopyresampled($im, $icon, 5 + ($currentCol) * $colWidth, (5 + $currentRow * 25), 0, 0, 16, 12,64,48);
                    imagedestroy($icon);
                }

                if ($showCountryCode) {
                    imagettftext(
                        $im,
                        $fontSize,
                        0,
                        5 + $showFlag * 25 + ($currentCol) * $colWidth,
                        (17 + ($currentRow) * 25),
                        imagecolorallocate($im, $fontColor[0], $fontColor[1], $fontColor[2]),
                        $fontFile,
                        strtoupper($country['code'])
                    );
                }

                $dimensions = imagettfbbox($fontSize, 0, $fontFile, number_format($country['hits'], 0, '', '.'));
                $numberWidth = abs($dimensions[4] - $dimensions[0]);

                imagettftext(
                    $im,
                    $fontSize,
                    0,
                    5 + $showFlag * 25 + $showCountryCode * $countryCodeWidth + ($currentCol) * $colWidth + $maxNumberWidth-$numberWidth,
                    (17 + ($currentRow) * 25),
                    imagecolorallocate($im, $fontColor[0], $fontColor[1], $fontColor[2]),
                    $fontFile,
                    number_format($country['hits'], 0, '', '.')
                );

                $currentRow = ++$currentRow % $rows;

                if ($currentRow == 0) {
                    $currentCol++;
                }

                if ($currentCol >= $cols) {
                    break;
                }
            }
        } catch (\Exception $e) {
            $im = imagecreatetruecolor(1, 1);
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

        $fonts = glob(dirname(__FILE__).'/fonts/*.ttf');
        $view->fonts = array_map(function($x) {
            return basename($x, '.ttf');
        }, $fonts);

        self::setPeriodVariablesView($view);
        $this->setBasicVariablesView($view);
        return $view->render();
    }
}
