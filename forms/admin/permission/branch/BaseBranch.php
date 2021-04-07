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


use app\logic\AuthLogic;
use app\models\Admin;
use app\models\AdminInfo;
use app\models\BaseModel;

abstract class BaseBranch extends BaseModel
{
    public $ignore;

    /**
     * @param $menu
     * @return mixed
     * @throws \Exception
     * 删除非本分支菜单
     */
    abstract public function deleteMenu($menu);

    /**
     * @return mixed
     * 获取商城退出跳转链接
     */
    abstract public function logoutUrl();

    /**
     * 获取子账户权限
     * @param $flag 1前台请求2后台请求
     * @return array
     */
    
    public function childPermission($flag = 2)
    {
        if($flag == 2){
            
            $admin = \Yii::$app->admin;
            $adminId = $admin->id;
            $admin = Admin::findOne($adminId);
            if ($admin->admin_type == Admin::ADMIN_TYPE_SUPER) {
                return AuthLogic::getAllPermission();
            }
            /** @var AdminInfo $adminInfo */
            $adminInfo = AdminInfo::find()->where(["admin_id"=>$admin->id])->one();
            $permission = [];
            if ($adminInfo->permissions) {
                $permission = json_decode($adminInfo->permissions, true);
            }
        }else{
            
            $permission = AuthLogic::getAllPermission();
        }
        return $permission;
    }

    protected function getKey($list)
    {
        $newList = [];
        foreach ($list as $item) {
            if (isset($item['name'])) {
                $newList[] = $item['name'];
            } elseif (is_array($item)) {
                $newList = array_merge($newList, $this->getKey($item));
            } else {
                continue;
            }
        }
        return $newList;
    }

    /**
     * @param AdminInfo $adminInfo
     * @return array|mixed
     */
    public function getSecondaryPermission($adminInfo)
    {
        if (isset($adminInfo->admin->admin_type) && $adminInfo->admin->admin_type == Admin::ADMIN_TYPE_SUPER) {
            return AuthLogic::getSecondaryPermissionAll();
        }
        $permission = [];
        if (isset($adminInfo->secondary_permissions)) {
            $permission = json_decode($adminInfo->secondary_permissions, true);
        }
        return $permission;
    }
}
