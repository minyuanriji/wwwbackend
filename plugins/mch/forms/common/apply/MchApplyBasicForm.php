<?php
namespace app\plugins\mch\forms\common\apply;


use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\CityHelper;
use app\helpers\PoiHelper;
use app\helpers\sms\Sms;
use app\helpers\TencentMapHelper;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchCommonCat;

class MchApplyBasicForm extends BaseModel{

    public $user_id;

    public $store_name;
    public $store_mch_common_cat_id;
    public $store_province_id;
    public $store_city_id;
    public $store_district_id;
    public $store_address;
    public $store_longitude;
    public $store_latitude;

    public $realname;
    public $mobile;
    public $captcha;

    public function rules(){
        return [
            [['user_id', 'captcha', 'realname', 'mobile', 'store_name', 'store_mch_common_cat_id', 'store_province_id', 'store_city_id', 'store_longitude', 'store_latitude', 'store_address'], 'required'],
            [['user_id', 'store_mch_common_cat_id', 'store_province_id', 'store_city_id'], 'integer'],
            [['store_district_id'], 'safe']
        ];
    }

    public function save(){

        $this->setPos();

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile  = $this->mobile;
            if(!$smsForm->checkCode()){
                throw new \Exception("手机验证码不正确");
            }

            $user = User::findOne($this->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("无法获取到用户信息");
            }

            $commonCat = MchCommonCat::findOne($this->store_mch_common_cat_id);
            if(!$commonCat || $commonCat->is_delete){
                throw new \Exception("行业类信息不存在");
            }

            $applyModel = MchApply::findOne([
                "user_id" => $this->user_id
            ]);
            if(!$applyModel){
                $applyModel = new MchApply([
                    "mall_id"    => \Yii::$app->mall->id,
                    "user_id"    => $this->user_id,
                    "realname"   => $this->realname,
                    "mobile"     => "mobile",
                    "status"     => "applying",
                    "created_at" => time()
                ]);
            }

            if($applyModel->status != "applying"){
                throw new \Exception("申请操作还未结束，请耐心等待");
            }

            $applyModel->updated_at = time();
            $applyModel->json_apply_data = json_encode([
                "store_name"              => $this->store_name,
                "store_mch_common_cat_id" => $this->store_mch_common_cat_id,
                "store_province_id"       => $this->store_province_id,
                "store_city_id"           => $this->store_city_id,
                "store_district_id"       => $this->store_district_id,
                "store_address"           => $this->store_address,
                "store_longitude"         => $this->store_longitude,
                "store_latitude"          => $this->store_latitude,
            ]);

            if(!$applyModel->save()){
                throw new \Exception($this->responseErrorMsg($applyModel));
            }


            Sms::updateCodeStatus($this->mobile, $this->captcha);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

    private function setPos(){
        $hostInfo = \Yii::$app->getRequest()->getHostInfo();
        $hostInfo = "https://dev.mingyuanriji.cn";

        if(PoiHelper::isPoi($this->store_longitude, $this->store_latitude)){
            //通过坐标获取地址信息
            $info = TencentMapHelper::toPoi($hostInfo, $this->store_longitude, $this->store_latitude);
            if($info){
                $city = CityHelper::likeSearch($info['province'], $info['city'], $info['district']);
                $this->store_province_id = $city['province_id'];
                $this->store_city_id     = $city['city_id'];
                $this->store_district_id = $city['district_id'];
                if(empty($this->store_address)){
                    $this->store_address = $info['address'];
                }
            }
        }else{
            //通过地址获取坐标信息
            $city = CityHelper::reverseData($this->store_district_id, $this->store_city_id, $this->store_province_id);
            $region = null;
            if(!empty($city['province'])){
                $this->store_province_id = $city['province']['id'];
                if(!empty($city['city'])){
                    $this->store_city_id = $city['city']['id'];
                    $region = $city['city']['name'];
                    if(!empty($city['district'])){
                        $this->store_district_id = $city['district']['id'];
                    }
                }
            }
            $info = TencentMapHelper::toAddr($hostInfo, $this->store_address, $region);
            if($info){
                $this->store_longitude = $info['longitude'];
                $this->store_latitude  = $info['latitude'];
            }
        }
    }
}