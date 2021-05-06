<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-10
 * Time: 09:55
 */

namespace app\forms\admin\permission\branch;

use app\forms\common\CommonAuth;
use app\logic\AuthLogic;
use app\logic\OptionLogic;
use app\models\Option;
class OfflineBranch extends BaseBranch
{
    public $ignore = 'offline';
    /**
     * @param $menu
     * @return mixed
     * @throws \Exception
     * 删除非本分支菜单
     */
    public function deleteMenu($menu)
    {
        if (isset($menu['ignore']) && in_array($this->ignore, $menu['ignore'])) {
            return true;
        }
        return false;
    }

    public function logoutUrl()
    {
        return \Yii::$app->urlManager->createUrl('mall/we7-entry/logout');
    }

    public function childPermission($adminInfo)
    {
        if ($adminInfo->is_default == 1) {
            $status = OptionLogic::get(
                Option::NAME_PERMISSIONS_STATUS,
                0,
                Option::GROUP_ADMIN
            );
            if ($status && $status == 1) {
                $default = AuthLogic::getPermissionsList();
                $permission = $this->getKey($default);
            } else {
                $permission = [];
            }
        } else {
            $permission = parent::childPermission($adminInfo);
        }
        return $permission;
    }

    public function getSecondaryPermission($adminInfo)
    {
        if ($adminInfo->is_default == 1) {
            $status = OptionLogic::get(
                Option::NAME_PERMISSIONS_STATUS,
                0,
                Option::GROUP_ADMIN
            );
            if ($status && $status == 1) {
                $permission = AuthLogic::getSecondaryPermissionAll();
            } else {
                $permission = AuthLogic::getSecondaryPermission();
            }
        } else {
            $permission = parent::getSecondaryPermission($adminInfo);
        }
        return $permission;
    }
}
