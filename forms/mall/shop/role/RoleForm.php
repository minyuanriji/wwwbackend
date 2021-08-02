<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 角色
 * Author: zal
 * Date: 2020-04-14
 * Time: 17:19
 */

namespace app\forms\mall\shop\role;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Role;
use app\models\RolePermission;

class RoleForm extends BaseModel
{
    public $id;
    public $page;
    public $keyword;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '角色ID',
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = Role::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->with('admin');
        if ($this->keyword) {
            $query->where(['like', 'name', $this->keyword]);
        }

        $list = $query->page($pagination, 10)->orderBy('created_at DESC')->asArray()->all();


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        $detail = Role::find()->where(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id])
            ->asArray()->one();

        if ($detail) {
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

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $role = Role::find()->where(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id])->one();

            if (!$role) {
                throw new \Exception('数据异常,该条数据不存在');
            }

            $role->is_delete = Role::IS_DELETE_YES;
            $res = $role->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($role));
            }

            $rolePermission = RolePermission::find()->where(['role_id' => $this->id])->one();
            $rolePermission->is_delete = RolePermission::IS_DELETE_YES;
            $res = $rolePermission->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($rolePermission));
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
                    'line' => $e->getLine(),
                ]
            ];
        }
    }
}
