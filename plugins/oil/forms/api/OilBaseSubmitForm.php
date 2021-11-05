<?php

namespace app\plugins\oil\forms\api;

use app\helpers\CityHelper;
use app\helpers\MobileHelper;
use app\helpers\TencentMapHelper;
use app\models\BaseModel;
use app\models\User;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;

class OilBaseSubmitForm extends BaseModel{

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

    /**
     * 构建订单数据
     * @return array
     * @throws \Exception
     */
    protected function buildOrderData(){

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

        $orderData = [];
        $orderData['mall_id']         = $user->mall_id;
        $orderData['user_id']         = $user->id;
        $orderData['mobile']          = $this->mobile;
        $orderData['province_id']     = $region['province_id'];
        $orderData['province']        = $region['province_name'];
        $orderData['city_id']         = $region['city_id'];
        $orderData['city']            = $region['city_name'];
        $orderData['district_id']     = $region['district_id'];
        $orderData['district']        = $region['district_name'];
        $orderData['location']        = $region['poi']['lng'] . "," . $region['poi']['lat'];
        $orderData['poi_type']        = "tx"; //腾讯坐标
        $orderData['address']         = $region['poi']['addr'];
        $orderData['product_id']      = $product->id;
        $orderData['user_integral']   = intval($user->static_integral);  //用户红包总数
        $orderData['remain_integral'] = intval($user->static_integral); //用户剩余红包总数
        $orderData['order_price']     = (float)$product->price; //订单金额
        $orderData['total_price']     = (float)$product->price; //待支付总金额

        //如果使用红包计算需要扣取的红包
        $orderData['integral_deduction_price'] = 0;
        $orderData['integral_fee_rate'] = 0;
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
            "order_data" => $orderData,
            "product"    => $product,
            "plateform"  => $platModel,
            "user"       => $user,
            "region"     => $region
        ];
    }

    /**
     * 下单检查
     * @throws \Exception
     */
    protected function check(OilPlateforms $platModel, OilProduct $product, $cityInfo){

        if(!MobileHelper::isMobile($this->mobile)){
            throw new \Exception("手机号码格式不正确");
        }


        if(empty($cityInfo['province_id'])){
            throw new \Exception("抱歉~无法获取到您的所在位置");
        }


        //区域限制
        $regionDenysList = !empty($platModel->region_deny) ? @json_decode($platModel->region_deny, true) : [];
        $allowRegions = [];
        $denyRegions = [];
        foreach($regionDenysList as $item){
            if(empty($item['type'])) continue;
            if($item['type'] == "allow"){
                $allowRegions[] = $item;
            }else{
                $denyRegions[] = $item;
            }
        }

        //禁止区域
        foreach($denyRegions as $region){
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

        //允许区域
        $isAllow = false;
        foreach($allowRegions as $region){
            if(!empty($region['province_id']) && !empty($region['city_id']) && !empty($region['district_id'])){
                if($region['province_id'] == $cityInfo['province_id'] &&
                    $region['city_id'] == $cityInfo['city_id'] &&
                    $region['district_id'] == $cityInfo['district_id']){
                    $isAllow = true;
                    break;
                }
            }elseif(!empty($region['province_id']) && !empty($region['city_id'])){
                if($region['province_id'] == $cityInfo['province_id'] &&
                    $region['city_id'] == $cityInfo['city_id']){
                    $isAllow = true;
                    break;
                }
            }elseif(!empty($region['province_id'])){
                if($region['province_id'] == $cityInfo['province_id']){
                    $isAllow = true;
                    break;
                }
            }
        }
        if(!$isAllow){
            throw new \Exception("非常抱歉~暂不支持" . $cityInfo['province_name'] . "/" . $cityInfo['city_name']  . "/" . $cityInfo['district_name'] . "所属地区");
        }
    }

    /**
     * TODO 通过经纬度查询到所在区域
     * @return array
     */
    protected function getRegion(){
        $hostInfo = \Yii::$app->getRequest()->getHostInfo();
        $hostInfo = "https://www.mingyuanriji.cn";
        $poi = TencentMapHelper::toPoi($hostInfo, $this->lng, $this->lat);

        $province = isset($poi['province']) ? $poi['province'] : "";
        $city     = isset($poi['city']) ? $poi['city'] : "";
        $district = isset($poi['district']) ? $poi['district'] : "";
        $cityInfo = CityHelper::likeSearch($province, $city, $district);

        $cityInfo['poi'] = ['addr' => $poi['address'], 'lng' => $this->lng, 'lat' => $this->lat];

        return $cityInfo;
    }
}