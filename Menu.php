<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\FlagCounter;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (!Piwik::isUserIsAnonymous()) {
            $menu->addPersonalItem(
                'FlagCounter',
                $this->urlForAction('index'),
                $order = 9
            );
        }
    }
}
