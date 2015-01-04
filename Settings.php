<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\FlagCounter;

use Piwik\Piwik;
use Piwik\Settings\SystemSetting;

class Settings extends \Piwik\Plugin\Settings
{
    /** @var SystemSetting */
    public $cacheLifeTime;

    protected function init()
    {
        $this->createCacheSetting();
    }

    private function createCacheSetting()
    {
        $this->cacheLifeTime                        = new SystemSetting('metric', Piwik::translate('FlagCounter_CacheTime'));
        $this->cacheLifeTime->type                  = static::TYPE_INT;
        $this->cacheLifeTime->uiControlType         = static::CONTROL_TEXT;
        $this->cacheLifeTime->introduction          = Piwik::translate('FlagCounter_CacheIntroduction');
        $this->cacheLifeTime->description           = Piwik::translate('FlagCounter_CacheDescription');
        $this->cacheLifeTime->defaultValue          = 3600;
        $this->cacheLifeTime->readableByCurrentUser = true;

        $this->addSetting($this->cacheLifeTime);
    }
}
