<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\FlagCounter;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings for FlagCounter.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $cacheLifeTime;


    protected function init()
    {
        // System setting --> allows selection of a single value
        $this->cacheLifeTime = $this->createCacheSetting();
    }

    private function createCacheSetting()
    {
        return $this->makeSetting('cacheLifeTime', $default = 3600, FieldConfig::TYPE_INT, function (FieldConfig $field) {
            $field->title = Piwik::translate('FlagCounter_CacheTime');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->availableValues = array('nb_visits' => 'Visits', 'nb_actions' => 'Actions', 'visitors' => 'Visitors');
            $field->description = Piwik::translate('FlagCounter_CacheDescription');
            $field->introduction = Piwik::translate('FlagCounter_CacheIntroduction');
        });
    }
}
