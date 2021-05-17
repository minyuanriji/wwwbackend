<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商城表单类
 * Author: zal
 * Date: 2020-04-09
 * Time: 15:16
 */

namespace app\forms\admin;

use app\core\ApiCode;
use app\models\Admin;
use app\models\BaseModel;
use app\models\Mall;
use yii\web\NotFoundHttpException;

class MallForm extends BaseModel
{

    /**
     * @Note: 保存
     * @param array $data
     * @return array
     */
    public function save($data)
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo($this);
        }
        if (isset($data["id"]) && !empty($data["id"])) {
            $model = Mall::findOne($data["id"]);
        } else {
            $user = \Yii::$app->admin->identity;
            $count = Mall::find()->where([
                'admin_id' => \Yii::$app->admin->id,
                'is_delete' => 0,
            ])->count();
            if ($user->mall_num >= 0 && $count >= $user->mall_num && $user->admin_type != 1) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '超出创建商城最大数量',
                ];
            }
            $model = new Mall();
            $model->admin_id = \Yii::$app->admin->id;
        }
        $model->name = $data["name"];
        $model->expired_at = $data["expired_at"] != 0 ? strtotime($data["expired_at"]) : $data["expired_at"];
        if (!$model->save()) {
            return $this->responseErrorInfo($model);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功。',
            'data' => $model,
        ];
    }

    /**
     * @Note: 迁移
     * @param array $data
     * @return array
     */
    public function transfer($data)
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo($this);
        }
        $model = Admin::findOne($data["id"]);
        $count = Mall::find()->where([
            'admin_id' => $data["id"],
            'is_delete' => 0,
        ])->count();
        if ($model->mall_num >= 0 && $count >= $model->mall_num && $model->admin_type != 1) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '超出创建商城最大数量',
            ];
        }
        $mall_model = Mall::findOne($data["id"]);
        if (!$mall_model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '商城不存在',
            ];
        }
        $mall_model->admin_id = $data["user_id"];
        if (!$mall_model->save()) {
            return $this->responseErrorInfo($mall_model);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功。',
            'data' => $mall_model,
        ];
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @Note: 修改
     * @param $id
     * @return array
     */
    public function edit($id)
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo($this);
        }

        try {
            $mall = Mall::find()->where([
                'id' => $id,
                'is_delete' => 0,
            ])->one();

            if (!$mall) {
                throw new \Exception('商城不存在');
            }

            if (\Yii::$app->admin->identity->admin_type != Admin::ADMIN_TYPE_SUPER) {
                if ($mall->admin_id != \Yii::$app->admin->id) {
                    throw new \Exception('用户无操作权限');
                }
            }
            $mall->is_recycle = $mall->is_recycle ? 0 : 1;
            $res = $mall->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($mall));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
            ];
        } catch (\Exception $e) {
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
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @Note: 获取单个商城数据
     * @param int $id
     * @return Mall|null
     * @throws \Exception
     */
    protected function getOneMall($id)
    {
        if (!$this->validate()) {
            throw new \Exception($this->responseErrorMsg($this));
        }
        if (\Yii::$app->admin->identity->admin_type == Admin::ADMIN_TYPE_SUPER) {
            $mall = Mall::findOne([
                'id' => $id,
                'is_delete' => 0,
            ]);
        } else {
            $mall = Mall::findOne([
                'id' => $id,
                'is_delete' => 0,
                'admin_id' => \Yii::$app->admin->identity->id,
            ]);
        }
        if (!$mall) {
            throw new \Exception('商城不存在。');
        }
        return $mall;
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @Note: 禁用或启用
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function disable($id)
    {
        try {
            $mall = $this->getOneMall($id);
            $mall->is_disable = $mall->is_disable ? 0 : 1;
            if (!$mall->save()) {
                throw new \Exception($this->responseErrorMsg($mall));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
            ];
        } catch (\Exception $e) {
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
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @Note: 回收站删除
     * @return array
     */
    public function delete($id)
    {
        try {
            $mall = $this->getOneMall($id);
            $mall->is_delete = 1;
            if (!$mall->save()) {
                throw new \Exception($this->responseErrorMsg($mall));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
            ];
        } catch (\Exception $e) {
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
     * 进入商城
     * @Author: 广东七件事 zal
     * @Date: 2020-04-09
     * @Time: 09:28
     * @param $id
     * @return \yii\console\Response|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function entryJxMall($id)
    {
        /** @var Admin $admin */
        $admin = \Yii::$app->admin->identity;
        $identity = $admin;
        if ($identity->admin_type == Admin::ADMIN_TYPE_SUPER) {
            $model = Mall::findOne([
                'id' => $id,
                'is_recycle' => 0,
                'is_delete' => 0,
            ]);
        } else {
            $model = Mall::findOne([
                'admin_id' => $admin->id,
                'id' => $id,
                'is_recycle' => 0,
                'is_delete' => 0,
            ]);
        }
        if (!$model) {
            throw new NotFoundHttpException('商城不存在');
        }
        \Yii::$app->setSessionJxMallId($id);
      /*  return \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['mall/overview/index']));*/
        return \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['mall/data-statistics/index']));
    }
}
