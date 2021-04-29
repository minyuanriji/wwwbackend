<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 后台管理员编辑表单类
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:16
 */

namespace app\forms\admin;

use app\component\jobs\UserUpdateJob;
use app\core\ApiCode;
use app\logic\AuthLogic;
use app\models\Admin;
use app\models\BaseModel;


class AdminEditForm extends BaseModel
{
    public $admin_id;
    public $username;
    public $password;
    public $mall_num;
    public $remark;
    public $expired_at;
    public $permissions;
    public $isCheckExpired;
    public $isAppMaxCount;
    public $secondary_permissions;

    public function rules()
    {
        return [
            [['username', 'password', 'mall_num',
                'expired_at', 'isCheckExpired', 'isAppMaxCount'], 'required'],
            [['admin_id'], 'integer'],
            [['remark', 'secondary_permissions'], 'safe'],
            [['permissions'], 'default', 'value' => []],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            //'mobile' => '手机号',
            'mall_num' => '小程序数量'
        ];
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @Note:数据保存
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->isAppMaxCount && $this->mall_num < 0) {
                throw new \Exception('可创建小程序数量不能小于0');
            }
            $expiredAt = !$this->isCheckExpired ? $this->expired_at : '0';
            //if (!$this->secondary_permissions) {
            //    $this->secondary_permissions = CommonAuth::secondaryDefault();;
            //}

            if ($this->admin_id) {
                $adminUser = Admin::updateAdminUser([
                    'id' => $this->admin_id,
                    'mall_num' => $this->mall_num,
                    'remark' => $this->remark,
                    'expired_at' => $expiredAt,
                    'permissions' => $this->permissions,
                    'secondary_permissions' => $this->secondary_permissions
                ]);
            } else {
                // 判断可创建最大子账户
                $this->checkAuth();

                $adminUser = Admin::createAdminUser([
                    'username' => $this->username,
                    'password' => $this->password,
                    'admin_type' => Admin::ADMIN_TYPE_ADMIN,
                    'mall_num' => $this->mall_num,
                    'remark' => $this->remark,
                    'expired_at' => $expiredAt,
                    'permissions' => $this->permissions,
                    'secondary_permissions' => $this->secondary_permissions
                ]);
            }

            if (!$this->isCheckExpired) {
                $expiredAt = strtotime($this->expired_at) - time();
                \Yii::$app->queue->delay($expiredAt > 0 ? $expiredAt : 0)->push(new UserUpdateJob([
                    'user_id' => $adminUser->user_id
                ]));
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'data' => [
                    'url' => \Yii::$app->urlManager->createUrl('admin/index/index'),
                    'admin_id' => $adminUser->id
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '服务器错误:' . $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    private function checkAuth()
    {
        //$res = \Yii::$app->cloud->auth->getAuthInfo();
        $res['host']['account_num'] = '-1';
        $userNum = AuthLogic::getChildrenNum();

        $accountNum = $res['host']['account_num'];

        // 总管理员自身不算入总数限制 -1
        if ($accountNum > -1 && $userNum >= $accountNum) {
            throw new \Exception('子账户数量超出限制');
        }
    }
}
