<?php

namespace app\plugins\mch\forms\api;


use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\EfpsMchReviewInfo;
use app\models\Goods;
use app\models\Order;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchPriceLog;

class MchBaseInfoForm extends BaseModel{

    public $mch_id;

    public function rules(){
        return [
            [['mch_id'], 'required'],
        ];
    }

    public function get()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $baseData = [
                'store'      => null,
                'category'   => null,
                'stat'       => null,
                'mch_mobile' => '',
                'settle'     => []
            ];

            $mchInfo = Mch::find()->where([
                'id' => $this->mch_id,
                'is_delete' => 0
            ])->with(["store", "category"])->asArray()->one();

            if(!$mchInfo || $mchInfo['is_delete']){
                throw new \Exception("商户不存在");
            }

            if($mchInfo['review_status'] == Mch::REVIEW_STATUS_CHECKED){
                $mchInfo['mch_status'] = "passed";
            }else{
                $mchApply = MchApply::findOne(["user_id" => $mchInfo['user_id']]);
                if($mchApply && $mchApply->status == "passed"){
                    $mchApply->status = "applying";
                    if(!$mchApply->save()){
                        throw new \Exception($this->responseErrorMsg($mchApply));
                    }
                }
                if(!$mchApply){
                    $mchInfo['mch_status'] = "applying";
                }else{
                    $mchInfo['mch_status'] = $mchApply->status;
                }
            }

            $baseData['store'] = $mchInfo['store'];
            $city = CityHelper::reverseData($mchInfo['store']['district_id'],
                $mchInfo['store']['city_id'], $mchInfo['store']['province_id']);
            $baseData['store']['province'] = $city['province'] ? $city['province']['name'] : '';
            $baseData['store']['city'] = $city['city'] ? $city['city']['name'] : '';
            $baseData['store']['district'] = $city['district'] ? $city['district']['name'] : '';

            $baseData['mch_status']  = $mchInfo['mch_status'];
            $baseData['category']    = $mchInfo['category'];
            $baseData['bind_mobile'] = $mchInfo['mobile'];
            $baseData['stat']        = [
                'account_money' => (float)$mchInfo['account_money'],
                'order_num'     => 0,
                'goods_num'     => 0
            ];

            //商户订单数量
            $baseData['stat']['order_num'] = (int)Order::find()->where([
                'is_delete'  => 0,
                'is_recycle' => 0,
                'mch_id'     => $mchInfo['id']
            ])->count();

            //商户商品数量
            $baseData['stat']['goods_num'] = (int)Goods::find()->where([
                'is_delete'  => 0,
                'is_recycle' => 0,
                'mch_id'     => $mchInfo['id']
            ])->count();

            $baseData['mch_mobile'] = $mchInfo['mobile'];

            //获取结算信息
            $efpsReviewInfo = EfpsMchReviewInfo::find()->where([
                "mch_id" => $mchInfo['id']
            ])->select([
                "paper_settleAccountType", "paper_settleAccountNo",
                "paper_settleAccount", "paper_settleTarget", "paper_openBank"
            ])->one();
            if($efpsReviewInfo){
                $baseData['settle'] = $efpsReviewInfo;
            }

            //获取商户待结算金额
            $fzAccountMoney = (float)MchPriceLog::find()->where([
                "status" => "unconfirmed",
                "mch_id" => $mchInfo['id']
            ])->sum("price");
            $baseData['stat']['fz_account_money'] = $fzAccountMoney;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'base_info' => $baseData
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ];
        }
    }
}