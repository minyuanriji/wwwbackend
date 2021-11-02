<?php

namespace app\plugins\oil\forms\api;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\helpers\MobileHelper;
use app\helpers\TencentMapHelper;
use app\models\BaseModel;
use app\models\User;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;

class OilSubmitPreviewForm extends BaseModel{

    public $product_id;
    public $use_integral;
    public $mobile;
    public $lat; //纬度
    public $lng; //经度

    public function rules(){
        return [
            [['product_id', 'use_integral', 'mobile', 'lat', 'lng'], 'required'],
            [['product_id', 'use_integral'], 'integer']
        ];
    }

    public function preview(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            //获取产品
            $product = OilProduct::findOne($this->product_id);
            if(!$product || !$product->status || $product->is_delete){
                throw new \Exception("产品不存在或已下架");
            }

            //获取平台
            $platModel = OilPlateforms::findOne($product->plat_id);
            if(!$platModel || $platModel->is_delete){
                throw new \Exception("平台不存在");
            }

            //获取用户信息
            $user = User::findOne(\Yii::$app->user->id);
            if(!$user){
                throw new \Exception("用户不存在");
            }

            $region = $this->getRegion();

            //检查订单
            $this->check($platModel, $product, $region);

            //订单信息
            $orderData = [];
            $orderData['user_integral']   = intval($user->static_integral);  //用户红包总数
            $orderData['remain_integral'] = intval($user->static_integral); //用户剩余红包总数
            $orderData['total_price']     = (float)$product->price; //待支付总金额

            //如果使用红包计算需要扣取的红包
            $orderData['integral_deduction_price'] = 0;
            if($this->use_integral){
                $needIntegralTotalNum = $orderData['total_price']; //需要的红包总数
                if($orderData['remain_integral'] > $needIntegralTotalNum){
                    $orderData['integral_deduction_price'] = $needIntegralTotalNum;
                    $orderData['remain_integral'] -= $needIntegralTotalNum;
                }else{
                    $orderData['integral_deduction_price'] = $orderData['remain_integral'];
                    $orderData['remain_integral'] = 0;
                }
                $integralDeductionPrice = $orderData['integral_deduction_price']; //使用红包抵扣的金额
                $orderData['total_price'] -= $integralDeductionPrice;
            }

            $orderData['product']      = $product->getAttributes();
            $orderData['region']       = $region;
            $orderData['use_integral'] = (int)$this->use_integral;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $orderData
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

    /**
     * 下单检查
     * @throws \Exception
     */
    private function check(OilPlateforms $platModel, OilProduct $product, $cityInfo){

        if(!MobileHelper::isMobile($this->mobile)){
            throw new \Exception("手机号码格式不正确");
        }


        if(empty($cityInfo['province_id'])){
            throw new \Exception("抱歉~无法获取到您的所在位置");
        }

        //区域限制
        $regionDenys = !empty($platModel->region_deny) ? @json_decode($platModel->region_deny, true) : [];
        foreach($regionDenys as $region){
            if(!empty($region['province_id']) && !empty($region['city_id']) && !empty($region['district_id'])){
                if($region['province_id'] == $cityInfo['province_id'] &&
                    $region['city_id'] == $cityInfo['city_id'] &&
                    $region['district_id'] == $cityInfo['district_id']){
                    throw new \Exception("暂不支持" . $region['province'] . $region['city'] . $region['district'] . "地区加油操作");
                }
            }elseif(!empty($region['province_id']) && !empty($region['city_id'])){
                if($region['province_id'] == $cityInfo['province_id'] &&
                    $region['city_id'] == $cityInfo['city_id']){
                    throw new \Exception("暂不支持" . $region['province'] . $region['city'] . "地区加油操作");
                }
            }elseif(!empty($region['province_id'])){
                if($region['province_id'] == $cityInfo['province_id']){
                    throw new \Exception("暂不支持" . $region['province'] . "地区加油操作");
                }
            }
        }
    }

    /**
     * TODO 通过经纬度查询到所在区域
     * @return array
     */
    private function getRegion(){
        $hostInfo = \Yii::$app->getRequest()->getHostInfo();
        $hostInfo = "https://www.mingyuanriji.cn";
        $poi = TencentMapHelper::toPoi($hostInfo, $this->lng, $this->lat);

        $province = isset($poi['province']) ? $poi['province'] : "";
        $city     = isset($poi['city']) ? $poi['city'] : "";
        $district = isset($poi['district']) ? $poi['district'] : "";
        $cityInfo = CityHelper::likeSearch($province, $city, $district);

        $cityInfo['poi'] = ['lng' => $this->lng, 'lat' => $this->lat];

        return $cityInfo;
    }
}