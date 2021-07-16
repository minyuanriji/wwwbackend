<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 跳转小程序
 * Author: zal
 * Date: 2020-04-14
 * Time: 15:50
 */

namespace app\plugins\mpwx\forms;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\mpwx\models\WxappJumpAppid;

class JumpAppidForm extends BaseModel
{
    public $appid_list;

    public function rules()
    {
        return [
            ['appid_list', 'each', 'rule' => ['trim']],
            ['appid_list', 'each', 'rule' => ['string', 'max' => 64]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'appid_list' => 'appid',
        ];
    }

    public function getResponseData()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        WxappJumpAppid::deleteAll([
            'mall_id' => \Yii::$app->mall->id,
        ]);
        foreach ($this->appid_list as $appid) {
            if ($appid) {
                $model = new WxappJumpAppid();
                $model->mall_id = \Yii::$app->mall->id;
                $model->appid = $appid;
                if (!$model->save()) {
                    return $this->responseErrorInfo($model);
                }
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功。',
        ];
    }
}
