<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商户设置操作
 * Author: zal
 * Date: 2020-04-18
 * Time: 14:50
 */

namespace app\forms\mall\mch;

use app\core\ApiCode;
use app\forms\common\version\Compatible;
use app\models\BaseModel;
use app\plugins\mch\models\MchSetting;

class MchSettingEditForm extends BaseModel
{
    public $id;
    public $is_distribution;
    public $is_sms;
    public $is_mail;
    public $is_print;
    public $is_territorial_limitation;
    public $send_type;
    public $is_web_service;
    public $web_service_url;
    public $web_service_pic;

    public function rules()
    {
        return [
            [['id', 'is_distribution', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'is_web_service'], 'integer'],
            [['is_distribution', 'is_sms', 'is_mail', 'is_print', 'is_territorial_limitation', 'send_type'], 'required'],
            [['web_service_url', 'web_service_pic'], 'string']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            if ($this->id) {
                $model = MchSetting::findOne($this->id);

                if (!$model) {
                    throw new \Exception('商户设置异常');
                }
            } else {
                $model = new MchSetting();
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = \Yii::$app->admin->identity->mch_id;
            }
            $this->send_type = Compatible::getInstance()->sendType($this->send_type);

            $model->is_mail = $this->is_mail;
            $model->is_print = $this->is_print;
            $model->is_distribution = $this->is_distribution;
            $model->is_sms = $this->is_sms;
            $model->is_territorial_limitation = $this->is_territorial_limitation;
            $model->send_type = \Yii::$app->serializer->encode($this->send_type);
            $model->is_web_service = $this->is_web_service;
            $model->web_service_pic = $this->web_service_pic;
            $model->web_service_url = $this->web_service_url;
            $res = $model->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
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
}
