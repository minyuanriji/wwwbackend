<?php

namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\forms\common\UserIntegralModifyForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinExchangeLog;
use app\plugins\taolijin\models\TaolijinGoods;
use lin010\taolijin\Ali;

class ExchangeIntegralToLjForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function create(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            //获取商品信息
            $goods = TaolijinGoods::findOne($this->id);
            if(!$goods || $goods->is_delete){
                throw new \Exception("商品不存在");
            }

            //获取用户信息
            $user = User::findOne(\Yii::$app->user->id);
            if(!$user || $user->is_delete){
                throw new \Exception("用户不存在");
            }

            //创建兑换记录
            $uniqueData = [
                "mall_id"      => $goods->mall_id,
                "user_id"      => $user->id,
                "tlj_goods_id" => $goods->id,
                "status"       => "unused"
            ];
            $exchangeLog = TaolijinExchangeLog::findOne($uniqueData);
            if(!$exchangeLog){

                $exchangeLog = new TaolijinExchangeLog(array_merge($uniqueData, [
                    "integral_num" => $goods->deduct_integral,
                    "gift_price"   => $goods->gift_price,
                    "updated_at"   => time(),
                    "created_at"   => time()
                ]));
                if(!$exchangeLog->save()){
                    throw new \Exception($this->responseErrorMsg($exchangeLog));
                }

                //扣取金豆
                if($user->static_integral < $goods->deduct_integral){
                    throw new \Exception("金豆不足");
                }
                $modifyForm = new UserIntegralModifyForm([
                    "type"        => 2,
                    "integral"    => $goods->deduct_integral,
                    "desc"        => "礼金商品兑换，扣除金豆",
                    "source_id"   => $exchangeLog->id,
                    "source_type" => "tlj_exchange",
                    "is_manual"   => 0
                ]);
                $modifyForm->modify($user);

                $acc = AliAccForm::get($goods->ali_type);

                if($goods->ali_type == "ali"){ //阿里联盟

                    $ali = new Ali($acc->app_key, $acc->secret_key);

                    $response = $ali->tlj->vegasTljCreate([
                        "adzone_id"                => $acc->adzone_id, //妈妈广告位Id
                        "send_start_time"          => date("Y-m-d H:i:s"),
                        "per_face"                 => $goods->gift_price,
                        "security_switch"          => "true",
                        "user_total_win_num_limit" => "1",
                        "name"                     => "补商会金豆抵扣",
                        "total_num"                => "1",
                        "item_id"                  => $goods->ali_unique_id
                    ]);
                    if($response->code){
                        throw new \Exception($response->msg);
                    }

                    $modelData = $response->getModelData();

                    $exchangeLog->rights_id = $modelData['rights_id'];
                    $exchangeLog->result_data = json_encode($modelData);
                    if(!$exchangeLog->save()){
                        throw new \Exception($this->responseErrorMsg($exchangeLog));
                    }

                }else{
                    throw new \Exception("联盟类型错误");
                }
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => "兑换成功"
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}