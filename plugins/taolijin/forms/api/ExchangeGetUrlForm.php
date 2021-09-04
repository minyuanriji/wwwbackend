<?php

namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinExchangeLog;
use app\plugins\taolijin\models\TaolijinGoods;
use app\plugins\taolijin\models\TaolijinUrls;
use lin010\taolijin\Ali;

class ExchangeGetUrlForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function get(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            //获取商品信息
            $goods = TaolijinGoods::findOne($this->id);
            if(!$goods || $goods->is_delete){
                throw new \Exception("商品不存在");
            }

            $acc = AliAccForm::get($goods->ali_type);

            $data = ["tlj_send_url" => "http://xxxxx.com", "spread_url" => "http://xxxxx.com", "ali_type" => $goods->ali_type];
            return [
                "code" => ApiCode::CODE_SUCCESS,
                "data" => $data
            ];

            if(false && $goods->ali_type == "ali"){ //淘宝联盟
                //$ali = new Ali($acc->app_key, $acc->secret_key);
                //$test = $ali->item->convert($goods->ali_unique_id, $acc->adzone_id, "ab123");

                //如果有待使用礼金，返回礼金领取链接
                $exchangeLog = TaolijinExchangeLog::findOne([
                    "mall_id"      => \Yii::$app->mall->id,
                    "user_id"      => \Yii::$app->user->id,
                    "tlj_goods_id" => $goods->id,
                    "status"       => "unused"
                ]);
                if($exchangeLog){
                    $resultData = json_decode($exchangeLog->result_data, true);
                    $data['tlj_send_url'] = isset($resultData['send_url']) ? $resultData['send_url'] : "http://";
                }


                $data['spread_url'] = $ali->spread->toShort($goods->ali_url); //长链转短链
            }else{
                throw new \Exception("联盟类型错误");
            }

            return [
                "code" => ApiCode::CODE_SUCCESS,
                "data" => $data
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}