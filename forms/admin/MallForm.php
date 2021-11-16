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
use app\logic\OptionLogic;
use app\models\Admin;
use app\models\BaseModel;
use app\models\Mall;
use app\models\Option;
use app\models\User;
use app\plugins\mpwx\models\MpwxConfig;
use EasyWeChat\Kernel\Exceptions\HttpException;
use jianyan\easywechat\Wechat;
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

        $userResult = User::findOne($data["user_id"]);
        if (!$userResult || $userResult->is_delete)
            $this->returnApiResultData(ApiCode::CODE_FAIL, '该用户不存在');

        $t = \Yii::$app->db->beginTransaction();
        $model->name                = $data["name"];
        $model->app_id              = isset($data["app_id"]) ? $data["app_id"] : '';
        $model->app_secret          = isset($data["app_secret"]) ? $data["app_secret"] : '';
        $model->user_id             = intval($data["user_id"]);
        $model->logo                = $data["logo"];
        $model->app_share_title     = isset($data["app_share_title"]) ? $data["app_share_title"] : '';
        $model->app_share_desc      = isset($data["app_share_desc"]) ? $data["app_share_desc"] : '';
        $model->app_share_pic       = isset($data["app_share_pic"]) ? $data["app_share_pic"] : '';
        $model->expired_at          = $data["expired_at"] != 0 ? strtotime($data["expired_at"]) : $data["expired_at"];
        if ($model->save()) {
            /** @var Wechat $wechat */
            if ((isset($data["app_id"]) && $data["app_id"]) && (isset($data["app_secret"]) && $data["app_secret"])) {
                $config = \Yii::$app->params['wechatMiniProgramConfig'];
                $config['app_id'] = $data["app_id"];
                $config['secret'] = $data["app_secret"];
                \Yii::$app->params['wechatMiniProgramConfig'] = $config;
                if (!$data["app_id"]) {
                    throw new \Exception('小程序AppId有误');
                }
                if (!$data["app_secret"]) {
                    throw new \Exception('小程序appSecret有误');
                }
                $wechat = \Yii::$app->wechat;
                $app = $wechat->miniProgram;
                $accessToken = $app->access_token;
                try {
                    $token = $accessToken->getToken(); // token 数组  token['access_token'] 字符串
                    $token = $accessToken->getToken(true); // 强制重新从微信服务器获取 token.
                } catch (HttpException $e) {
                    if ($e->formattedResponse['errcode'] == '41002') {
                        $message = '小程序AppId有误(' . $e->formattedResponse['errmsg'] . ')';
                        return [
                            'code' => ApiCode::CODE_FAIL,
                            'msg' => $message,
                        ];
                    }
                    if ($e->formattedResponse['errcode'] == '41013') {
                        $message = '小程序AppId有误(' . $e->formattedResponse['errmsg'] . ')';
                        return [
                            'code' => ApiCode::CODE_FAIL,
                            'msg' => $message,
                        ];
                    }
                    if ($e->formattedResponse['errcode'] == '40125') {
                        $message = '小程序密钥有误(' . $e->formattedResponse['errmsg'] . ')';
                        return [
                            'code' => ApiCode::CODE_FAIL,
                            'msg' => $message,
                        ];
                    }
                }
            }
            try {
                $config = null;
                if ($data["id"]) {
                    $config = MpwxConfig::findOne(['mall_id' => $data["id"], 'is_delete' => 0]);
                    if (!$config) {
                        $t->rollBack();
                        return [
                            'code' => ApiCode::CODE_FAIL,
                            'msg' => '数据异常,该条数据不存在',
                        ];
                    }
                } else {
                    $config = new MpwxConfig();
                }
                $config->mall_id    = $model->id;
                $config->name       = $model->name;
                $config->app_id     = isset($data["app_id"]) ? $data["app_id"] : '';
                $config->secret     = isset($data["app_secret"]) ? $data["app_secret"] : '';
                if ($config->save()) {
                    $t->commit();
//                    OptionLogic::set(Option::NAME_MPWX, $this->attributes, $model->id, Option::GROUP_APP);
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '保存成功',
                        'data' => $model
                    ];
                }
                $t->rollBack();
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '保存失败',
                    'error' => $config->getErrors()
                ];
            } catch (\Exception $e) {
                $t->rollBack();
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $e->getMessage(),
                ];
            }
        }
        $t->rollBack();
        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '保存失败。',
            'data' => $model->getErrors(),
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
        $model = Admin::findOne($data["user_id"]);
        $count = Mall::find()->where([
            'admin_id' => $data["user_id"],
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
            if($id == self::MY_Mall_ID) {
                throw new \Exception('不能删除自己商城');
            }
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
