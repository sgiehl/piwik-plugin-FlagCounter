<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\FlagCounter;

/**
 *
 */
class FlagCounter extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        );
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'FlagCounter_PluginDescription';
        $translationKeys[] = 'FlagCounter_GeneratorDescription';
        $translationKeys[] = 'General_Website';
        $translationKeys[] = 'FlagCounter_NumberOfRows';
        $translationKeys[] = 'FlagCounter_NumberOfColumns';
        $translationKeys[] = 'FlagCounter_Font';
        $translationKeys[] = 'FlagCounter_FontSize';
        $translationKeys[] = 'FlagCounter_FontColor';
        $translationKeys[] = 'General_Date';
        $translationKeys[] = 'General_Period';
        $translationKeys[] = 'FlagCounter_ShowCountryCode';
        $translationKeys[] = 'FlagCounter_HideFlag';
        $translationKeys[] = 'FlagCounter_UrlToImage';
    }
}
