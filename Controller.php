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
use Piwik\Plugins\UserCountry\API;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    protected function getCountryData()
    {
        $countries = array();

        // fetch data from API as superuser so we can get them even without view access
        $countryData = Access::getInstance()->doAsSuperUser(function(){
            $idSite  = Common::getRequestVar('idSite', null, 'int');
            $period  = Common::getRequestVar('period', null, 'string');
            $date    = Common::getRequestVar('date', null, 'string');
            $segment = Request::getRawSegmentFromRequest();

            return API::getInstance()->getCountry($idSite, $period, $date, $segment);
        });

        foreach ($countryData->getRows() AS $country) {
            $countries[] = array (
                'name' => $country->getColumn('label'),
                'icon' => $country->getMetadata('logo'),
                'hits' => $country->getColumn(2),
            );
        }

        return $countries;
    }

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

    public function image()
    {
        header('Content-type: image/png');

        $countries = $this->getCountryData();

        $rows = Common::getRequestVar('rows', 5, 'int');
        $cols = Common::getRequestVar('cols', 2, 'int');

        if (count($countries) < $rows * $cols) {
            $rows = ceil(count($countries)/$cols);
        }

        $im = imagecreatetruecolor($cols*100, $rows*25);

        $color = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $color);

        $currentRow = 0;
        $currentCol = 0;

        foreach ($countries as $country) {

            $icon = imagecreatefrompng(PIWIK_INCLUDE_PATH . DIRECTORY_SEPARATOR . $country['icon']);

            imagecopy($im, $icon, 5 + ($currentCol)*100, (5 + $currentRow*25), 0, 0, 16, 12);

            imagestring($im, 3, 25 + ($currentCol)*100, (5 + $currentRow*25), $country['hits'], imagecolorallocate($im, 0, 0, 0));

            imagedestroy($icon);

            $currentCol = ++$currentCol%$cols;

            if ($currentCol == 0) {
                $currentRow++;
            }

            if ($currentRow >= $rows) {
                break;
            }
        }

        imagesavealpha($im, TRUE);
        imagepng($im);
        imagedestroy($im);
        exit;
    }
}
