<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 角色用户
 * Author: zal
 * Date: 2020-04-14
 * Time: 17:19
 */

namespace app\forms\mall\shop\role_user;

use app\core\ApiCode;
use app\forms\common\RoleSettingForm;
use app\models\Admin;
use app\models\AdminInfo;
use app\models\BaseModel;
use app\models\Role;
use app\models\RoleUser;

class RoleUserForm extends BaseModel
{
    public $id;
    public $password;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['password', 'keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '操作员ID',
            'password' => '密码',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = Admin::find()->alias('u')->where(['u.is_delete' => 0, 'u.mall_id' => \Yii::$app->mall->id])
            ->andWhere(['u.admin_type' => Admin::ADMIN_TYPE_OPERATE])
            ->andWhere(['=','u.mch_id','0']);

        if ($this->keyword) {
            $query->andWhere(['like', 'username', $this->keyword]);
        }

        $list = $query->page($pagination, 10)->orderBy('created_at DESC')->asArray()->all();


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }

    public function roleList()
    {
        $query = Role::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);

        $list = $query->asArray()->all();


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function getDetail()
    {
        $detail = Admin::find()->with('role')->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->asArray()->one();

        if ($detail) {
            $checkedKeys = [];
            if ($detail['role'] && is_array($detail['role'])) {
                foreach ($detail['role'] as $item) {
                    $checkedKeys[] = $item['id'];
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                    'checkedKeys' => $checkedKeys,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '请求失败',
        ];
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $admin = Admin::find()->alias('u')->where([
                'u.id' => $this->id, 'u.mall_id' => \Yii::$app->mall->id, 'u.is_delete' => 0])
                /*->joinWith(['identity' => function ($query) {
                    $query->andWhere(['u.admin_type' => Admin::ADMIN_TYPE_OPERATE]);
                }])*/->one();

            if (!$admin) {
                throw new \Exception('数据异常,该条数据不存在');
            }

            $admin->is_delete = 1;
            $res = $admin->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($admin));
            }

            $adminInfo = AdminInfo::find()->where(['admin_id' => $admin->id])->one();
            if ($adminInfo) {
                $adminInfo->is_delete = 1;
                $res = $adminInfo->save();
                if (!$res) {
                    throw new \Exception($this->responseErrorMsg($adminInfo));
                }
            }

            $roleUserRes = RoleUser::find()->where(['admin_id' => $admin->id])->one();
            if ($roleUserRes) {
                $roleUserRes->is_delete = 1;
                $result = $roleUserRes->save();
                if (!$result) {
                    throw new \Exception($this->responseErrorMsg($roleUserRes));
                }
            }
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
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
     * 修改密码
     * @return array
     * @throws \yii\base\Exception
     */
    public function editPassword()
    {
        /** @var Admin $admin */
        $admin = Admin::find()->alias('u')
            ->joinWith(['identity' => function ($query) {
                $query->andWhere(['is_operator' => 1]);
            }])
            ->where([
                'u.mall_id' => \Yii::$app->mall->id,
                'u.id' => $this->id,
                'u.is_delete' => 0
            ])->one();

        if (!$admin) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '用户不存在',
            ];
        }


        $admin->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
        $res = $admin->save();

        if ($res) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '密码修改成功',
            ];
        }

        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '密码修改失败',
        ];
    }

    public function route()
    {
        $mallId = base64_encode(\Yii::$app->mall->id);
        $url = \Yii::$app->urlManager->createAbsoluteUrl('admin/admin/login&mall_id=' . $mallId);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'url' => urldecode($url),
            ]
        ];
    }

    public function updatePassword()
    {
        try {
            $setting = (new RoleSettingForm())->getSetting();
            if (!$setting['update_password_status']) {
                throw new \Exception('员工无权限修改密码');
            }
            $user = Admin::findOne(\Yii::$app->admin->id);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $res = $user->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($user));
            }

            $logout = \Yii::$app->admin->logout();
            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '密码修改成功',
                ];
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}
