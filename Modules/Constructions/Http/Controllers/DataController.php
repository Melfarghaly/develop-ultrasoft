<?php

namespace Modules\Constructions\Http\Controllers;

use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'constructions_module',
                'label' => __('constructions::lang.constructions_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds cms menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();

        $is_constructions_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'constructions_module');

        $commonUtil = new Util();
        $is_admin = $commonUtil->is_admin(auth()->user(), $business_id);

        if (auth()->user()->can('constructions.access_constructions_module') && $is_constructions_enabled) {
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu) {
                    $menu->url(action([\Modules\Constructions\Http\Controllers\ConstructionsController::class, 'index']), __('constructions::lang.constructions'), ['icon' => 'fas fa-building fa', 'style' => config('app.env') == 'demo' ? 'background-color: #D483D9;' : '', 'active' => request()->segment(1) == 'constructions'])->order(55);
                }
            );
        }
    }

    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'constructions.access_constructions_module',
                'label' => __('constructions::lang.access_constructions_module'),
                'default' => false,
            ],
            [
                'value' => 'constructions.manage_projects',
                'label' => __('constructions::lang.manage_projects'),
                'default' => false,
            ],
            [
                'value' => 'constructions.view_project',
                'label' => __('constructions::lang.view_project'),
                'default' => false,
            ],
            [
                'value' => 'constructions.add_project',
                'label' => __('constructions::lang.add_project'),
                'default' => false,
            ],
            [
                'value' => 'constructions.edit_project',
                'label' => __('constructions::lang.edit_project'),
                'default' => false,
            ],
            [
                'value' => 'constructions.delete_project',
                'label' => __('constructions::lang.delete_project'),
                'default' => false,
            ],
            [
                'value' => 'constructions.manage_materials',
                'label' => __('constructions::lang.manage_materials'),
                'default' => false,
            ],
            [
                'value' => 'constructions.manage_tasks',
                'label' => __('constructions::lang.manage_tasks'),
                'default' => false,
            ],
            [
                'value' => 'constructions.manage_labor',
                'label' => __('constructions::lang.manage_labor'),
                'default' => false,
            ],
            [
                'value' => 'constructions.view_reports',
                'label' => __('constructions::lang.view_reports'),
                'default' => false,
            ],
        ];
    }
} 