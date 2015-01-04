<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\FlagCounter;

use Piwik\Settings\SystemSetting;

class Settings extends \Piwik\Plugin\Settings
{
    /** @var SystemSetting */
    public $cacheLifeTime;

    protected function init()
    {
        $this->setIntroduction('Here you can specify some global configuration settings.');

        $this->createCacheSetting();
    }

    private function createCacheSetting()
    {
        $this->cacheLifeTime                        = new SystemSetting('metric', 'Cache time');
        $this->cacheLifeTime->type                  = static::TYPE_INT;
        $this->cacheLifeTime->uiControlType         = static::CONTROL_TEXT;
        $this->cacheLifeTime->introduction          = 'To avoid permanent load to the piwik server the country data will be cached for the given time';
        $this->cacheLifeTime->description           = 'amount in seconds';
        $this->cacheLifeTime->defaultValue          = 3600;
        $this->cacheLifeTime->readableByCurrentUser = true;

        $this->addSetting($this->cacheLifeTime);
    }
}
