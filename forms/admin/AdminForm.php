<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 后台用户表单类
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:16
 */

namespace app\forms\admin;

use app\core\ApiCode;
use app\logic\AuthLogic;
use app\models\Admin;
use app\models\BaseModel;
use app\models\Mall;
use app\models\UserIdentity;
use yii\data\Pagination;

class AdminForm extends BaseModel
{
    public $limit;
    public $page;
    public $id;
    public $password;
    public $keyword;
    public $admin_type = 0;

    public function rules()
    {
        return [
            [['page'], 'default', 'value' => 1],
            [['id'],'integer'],
            [['password'], 'string', 'min' => 6, 'max' => 16],
            [['keyword'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '用户ID',
            'password' => '密码',
        ];
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @Note: 获取管理账户列表
     * @return array
     */
    public function getAdminList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = Admin::find()->alias('u')->where([
            'u.is_delete' => 0,
            'u.mall_id' => \Yii::$app->admin->identity->mall_id
        ])->with(['mall' => function ($query) {
            $query->andWhere(['is_delete' => 0]);
        }]);

        if ($this->keyword) {
            $query->andWhere(['like', 'u.username', $this->keyword]);
        }

        if (!$this->admin_type) {
            $query->andWhere(['u.admin_type' => 2]);
        }
        $query->joinWith("adminInfo");
        $list = $query->orderBy('u.id DESC')->page($pagination)->asArray()->all();
        foreach ($list as &$item) {
            $item['create_app_count'] = count($item['mall']);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @Note: 获取管理账户详情
     * @return array
     */
    public function getDetail()
    {
        $detail = Admin::find()->where(['id' => $this->id])->with(['adminInfo'])->asArray()->one();
        if ($detail) {
            $detail['adminInfo']['permissions'] = json_decode($detail['adminInfo']['permissions'], true);
            $detail['adminInfo']['secondary_permissions'] = AuthLogic::getSecondaryPermissionList($detail['adminInfo']['secondary_permissions']);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }

        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '请求失败',
        ];
    }

    /**
     * 删除
     * @return array
     */
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var Admin $admin */
            $admin = Admin::find()->where(['id' => $this->id])->one();
            if (!$admin) {
                throw new \Exception('数据异常,该条数据不存在');
            }

            if ($admin->admin_type == Admin::ADMIN_TYPE_SUPER) {
                throw new \Exception('超级管理员账号不可删除');
            }
            $admin->is_delete = 1;
            $res = $admin->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($admin));
            }
            $superAdmin = Admin::findOne(['admin_type' => Admin::ADMIN_TYPE_SUPER, 'is_delete' => 0]);
            if (!$superAdmin) {
                throw new \Exception('无超级管理员');
            }

            $res = Mall::updateAll([
                'admin_id' => $superAdmin->id,
            ], [
                'admin_id' => $admin->id,
                'is_delete' => 0,
            ]);

            if($res === false){
                throw new \Exception('删除失败');
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
                    'line' => $e->getLine().";file:".$e->getFile()
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
        $admin = Admin::find()->alias('u')->where(['u.id' => $this->id, 'u.is_delete' => 0])->one();

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
}
