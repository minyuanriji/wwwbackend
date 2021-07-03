<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 角色用户操作
 * Author: zal
 * Date: 2020-04-14
 * Time: 17:39
 */

namespace app\forms\mall\shop\role_user;

use app\core\ApiCode;
use app\models\Admin;
use app\models\AdminInfo;
use app\models\BaseModel;
use app\models\RoleUser;

class RoleUserEditForm extends BaseModel
{
    public $username;
    public $password;
    public $roles;
    public $admin_id;

    public $admin;
    public $isNewRecord;

    public function rules()
    {
        return [
            [['username', 'password'], 'string'],
            [['admin_id',], 'integer'],
            [['roles'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'roles' => '角色'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // 区分是添加还是编辑
            if ($this->admin_id) {
                $admin = Admin::findOne($this->admin_id);
                if (!$admin) {
                    throw new \Exception('数据异常,该条数据不存在');
                }

                $adminInfo = AdminInfo::find()->where(['admin_id' => $admin->id])->one();
            } else {
                $admin = new Admin();
                $adminInfo = new AdminInfo();
            }
            $this->admin = $admin;

            // 检测是否有重复数据
            $query = Admin::find()->alias('u')->where([
                'u.username' => $this->username,
                'u.is_delete' => 0,
                'u.mall_id' => \Yii::$app->mall->id,
                'u.mch_id' => \Yii::$app->admin->identity->mch_id
            ]);

            if ($this->admin_id) {
                $query = $query->andWhere(['!=', 'u.id', $this->admin_id]);
            }

            if ($query->one()) {
                throw new \Exception('用户名已存在');
            }

            if ($admin->isNewRecord) {
                $admin->username = $this->username;
                $admin->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
                $admin->access_token = \Yii::$app->security->generateRandomString();
                $admin->auth_key = \Yii::$app->security->generateRandomString();
                $admin->mall_id = \Yii::$app->mall->id;
                $admin->admin_type = Admin::ADMIN_TYPE_OPERATE;
            }
            $res = $admin->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($admin));
            }

//            if ($adminInfo->isNewRecord) {
//                $adminInfo->admin_id = $admin->id;
//                $res = $adminInfo->save();
//                if (!$res) {
//                    throw new \Exception($this->responseErrorMsg($adminInfo));
//                }
//            }

            $this->setRoleUser();

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
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * 设置用户角色关联
     */
    private function setRoleUser()
    {
        if (!$this->admin->isNewRecord) {
            RoleUser::updateAll([
                'is_delete' => 1,
            ], [
                'admin_id' => $this->admin->id
            ]);
        }
        if ($this->roles && is_array($this->roles)) {
            $attributes = [];
            foreach ($this->roles as $item) {
                $authRoleUser = RoleUser::findOne(['admin_id' => $this->admin->id, 'role_id' => $item]);
                if ($authRoleUser) {
                    $authRoleUser->is_delete = 0;
                    $res = $authRoleUser->save();

                    if (!$res) {
                        throw new \Exception($this->responseErrorMsg($authRoleUser));
                    }
                } else {
                    $attributes[] = [
                        $item, $this->admin->id,
                    ];
                }
            }

            $query = \Yii::$app->db->createCommand();
            $res = $query->batchInsert(RoleUser::tableName(), ['role_id', 'admin_id'], $attributes)
                ->execute();

            if ($res != count($attributes)) {
                throw new \Exception('保存失败, 角色数据异常');
            }
        }
    }
}
