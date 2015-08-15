<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
            if (!method_exists($menu, 'addManageItem')) {
                // menu fallback for piwik < 1.11
                $menu->add(
                    'CoreAdminHome_MenuManage', 'FlagCounter',
                    array('module' => 'FlagCounter', 'action' => 'index'),
                    true,
                    $order = 3
                );
                return;
            }

            $menu->addManageItem(
                'FlagCounter',
                $this->urlForAction('index'),
                $order = 9
            );
        }

    }

}
