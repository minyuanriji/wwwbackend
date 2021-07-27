<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-商城后台配置
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\sign_in\forms\common\Common;
use app\plugins\sign_in\models\SignInAwardConfig;
use app\plugins\sign_in\models\SignInConfig;
use yii\helpers\ArrayHelper;

class ConfigForm extends BaseModel
{
    public $rule;
    public $name;
    public $push_url;

    public function rules(){
        return [
            [['rule','name','push_url'],'string'],
        ];
    }

    public function search()
    {
        $common = Common::getCommon($this->mall);
        $config = $common->getConfig();
        if (!$config->isNewRecord) {
            $awardConfig = $common->getAwardConfigAll();
            $config = ArrayHelper::toArray($config);
            $config['continue'] = [];
            $config['total'] = [];
            foreach ($awardConfig as $award) {

                $number = price_format($award->number, 'float', 2);
                //转换type
                $award->type = $award->type==SignInAwardConfig::TYPE_SCORE?SignInAwardConfig::TYPE_SCORE_NAME:SignInAwardConfig::TYPE_COUPON_NAME;

                $item = [
                    'number' => $number,
                    'day' => $award->day,
                    'type' => $award->type,
                    'coupon_id' => $award->coupon_id,
                ];
                if ($award->status == 1 && $award->type == SignInAwardConfig::TYPE_SCORE_NAME) {
                    $config['normal'] = $number;
                    $config['normal_type'] = $award->type;
                }
                if ($award->status == 1 && $award->type == SignInAwardConfig::TYPE_COUPON_NAME) {
                    $config['coupon_num'] = $number;
                    $config['normal_type'] = $award->type;
                    $config['coupon'] = $award->coupon_id;
                }

                if ($award->status == 2 && $award->type == SignInAwardConfig::TYPE_COUPON_NAME) {
                    $config['continue'][] = $item;
                }
                if ($award->status == 2 && $award->type == SignInAwardConfig::TYPE_SCORE_NAME) {
                    $config['total'][] = $item;
                }
            }

        } else {
            $config = null;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'config' => $config
            ]
        ];
    }


    public function save(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };
        $config = SignInConfig::findOne(['mall_id'=>\Yii::$app->mall]);
        $config->updated_at = time();
        $config->rule = $this->attributes['rule'];

        $config->save();
        if ($config) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorInfo($config);
        }
    }

    public function getOne(){
        return SignInConfig::find()->where(['mall_id' =>\Yii::$app->mall])->one();
    }
}
