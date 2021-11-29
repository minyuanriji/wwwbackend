<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\forms\api\identity\RegisterForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchGroup;

class MchManaGroupAddItemForm extends BaseModel{

    public $mch_id;

    public $name;
    public $realname;
    public $address;
    public $latitude;
    public $longitude;
    public $provice_id;
    public $district_id;
    public $city_id;
    public $mobile;
    public $captcha;
    public $license_name;
    public $license_num;
    public $license_pic;
    public $zk;

    public function rules(){
        return [
            [['name', 'realname', 'address', 'latitude', 'longitude', 'provice_id', 'district_id', 'city_id',
             'mobile', 'captcha', 'license_name', 'license_pic', 'zk'], 'required'],
            [['zk'], 'number'],
            [['license_num'], 'safe']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $mchId = $this->mch_id ?: MchAdminController::$adminUser['mch_id'];
            $mchGroup = MchGroup::findOne([
                "mch_id" => $mchId
            ]);
            if(!$mchGroup || $mchGroup->is_delete){
                throw new \Exception("商户[ID{$mchId}]非连锁总店");
            }

            if($this->zk < 1 || $this->zk > 10){
                throw new \Exception("折扣只能为1-10折范围之间");
            }

            //判断手机号是否注册过门店
            $exists = Mch::find()->where(["mobile" => $this->mobile])->exists();
            if($exists){
                throw new \Exception("手机号“{$this->mobile}”已注册过门店了");
            }

            $applyModel = MchApply::findOne(["mobile" => $this->mobile]);
            if($applyModel){
                if($applyModel->status == "verifying"){
                    throw new \Exception("请勿重复申请！若无问题，管理员将在24小时内为您审核通过");
                }
            }else{
                $applyModel = new MchApply([
                    "mall_id"  => \Yii::$app->mall->id,
                    "realname" => $this->realname,
                    "mobile"   => $this->mobile,
                    "created_at" => time()
                ]);
            }

            $mchModel = $mchGroup->mch;

            //获取该手机号所绑定的用户，若无注册过，新注册一个
            $user = User::findOne(["mobile" => $this->mobile]);
            if(!$user){
                $parentUser = User::findOne($mchModel->user_id);
                $form = new RegisterForm([
                    "mall_id"       => $parentUser->mall_id,
                    "parent_mobile" => $parentUser->mobile,
                    "recommend_id"  => $parentUser->parent_id,
                    "mobile"        => $this->mobile,
                    "captcha"       => $this->captcha,
                    "password"      => uniqid()
                ]);
                $res = $form->register();
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
                $user = User::findOne(["mobile" => $this->mobile]);
            }
            $applyData = [
                "store_name"              => $this->name,
                "store_province_id"       => $this->provice_id,
                "store_city_id"           => $this->city_id,
                "store_district_id"       => $this->district_id,
                "store_address"           => $this->address,
                "store_longitude"         => $this->longitude,
                "store_latitude"          => $this->latitude,
                "license_name"            => $this->license_name,
                "license_num"             => $this->license_num,
                "license_pic"             => $this->license_pic,
                "settle_discount"         => $this->zk,
                "store_mch_common_cat_id" => $mchModel->mch_common_cat_id,
                "cor_num"                 => "",
                "cor_pic1"                => "",
                "cor_pic2"                => "",
                "settle_num"              => "",
                "settle_bank"             => "",
                "settle_realname"         => ""
            ];

            $applyModel->realname        = $this->realname;
            $applyModel->user_id         = $user->id;
            $applyModel->status          = "verifying";
            $applyModel->updated_at      = time();
            $applyModel->json_apply_data = json_encode($applyData);
            $applyModel->mch_group_id    = $mchGroup->id;
            if(!$applyModel->save()){
                throw new \Exception($this->responseErrorMsg($applyModel));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}