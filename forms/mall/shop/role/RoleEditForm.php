<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 角色操作
 * Author: zal
 * Date: 2020-04-14
 * Time: 17:19
 */

namespace app\forms\mall\shop\role;

use app\core\ApiCode;
use app\forms\admin\Menus;
use app\models\BaseModel;
use app\models\Role;
use app\models\RolePermission;


class RoleEditForm extends BaseModel
{
    public $name;
    public $remark;
    public $permissions;
    public $id;

    private $newPermissions = [];

    public function rules()
    {
        return [
            [['name', 'remark'], 'string'],
            [['permissions'], 'safe'],
            [['permissions'], 'default', 'value' => []],
            [['id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '角色名称',
            'remark' => '备注',
            'permissions' => '权限'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $authRole = Role::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$authRole) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $authRole = new Role();
                $authRole->mall_id = \Yii::$app->mall->id;
                $authRole->creator_id = \Yii::$app->admin->id;
            }

            $authRole->name = $this->name;
            $authRole->remark = $this->remark;
            $res = $authRole->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($authRole));
            }

            $authRolePermission = RolePermission::findOne(['role_id' => $this->id]);
            if (!$authRolePermission) {
                $authRolePermission = new RolePermission();
            }
            // TODO 特殊处理
            if (in_array('mall/coupon-auto-send/index', $this->permissions)) {
                $this->permissions[] = 'mall/coupon-auto-send/edit';
            }

            $menus = Menus::getMallMenus(true);
            // 获取action 权限路由
            $this->getPermissions($menus);
            $newPermissions = array_merge($this->permissions, $this->newPermissions);
            $newPermissions = array_unique($newPermissions);
            $newPermissions = array_values($newPermissions);

            $authRolePermission->role_id = $authRole->id;
            $authRolePermission->permissions = json_encode($newPermissions, JSON_UNESCAPED_UNICODE);
            $res = $authRolePermission->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($authRolePermission));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ]
            ];
        }
    }


    /**
     * 获取路由权限路由
     * @param $menus
     * @return array
     */
    private function getPermissions($menus)
    {
        foreach ($menus as $k => $item) {
            if (!isset($item['route'])) {
                $item['route'] = '';
            }
            if (in_array($item['route'], $this->permissions) && isset($item['action'])) {
                foreach ($item['action'] as $actionItem) {
                    $this->newPermissions[] = $actionItem['route'];
                }
            }

            if (isset($item['children'])) {
                $menus[$k]['children'] = $this->getPermissions($item['children']);
            }
        }
        $menus = array_values($menus);
        return $menus;
    }
}
