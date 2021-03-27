<?php
namespace app\mch\forms\api;


use app\core\ApiCode;
use app\forms\common\mptemplate\MpTplMsgCSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\forms\common\version\Compatible;
use app\models\BaseModel;
use app\models\DistrictData;
use app\models\Option;
use app\models\Store;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchMallSetting;
use app\plugins\mch\models\MchSetting;

class MchApplyForm extends BaseModel {

    public $user_id;                //绑定用户ID

    public $mobile;                 //手机号
    public $realname;               //申请人真实姓名

    public $cat_id;                 //店铺类型
    public $name;                   //店铺名称
    public $address;                //店铺地址
    public $province_id;            //省
    public $city_id;                //市
    public $district_id;            //区
    public $longitude;              //经度
    public $latitude;               //纬度

    public function rules(){
        return array_merge(parent::rules(), [
            [['user_id', 'mobile', 'realname', 'cat_id', 'name', 'address', 'longitude', 'latitude', 'province_id', 'city_id', 'district_id'], 'safe']
        ]);
    }

    public function attributeLabels(){
        return [
            'user_id'               => '绑定用户ID',
            'mobile'                => '申请人手机号码',
            'realname'              => '申请人真实姓名',
            'pic_id_card_front'     => '身份证正面图片',
            'pic_id_card_back'      => '身份证背面图片',
            'pic_business_license'  => '营业执照图片',
            'cat_id'                => '店铺类型',
            'name'                  => '店铺名称',
            'address'               => '店铺地址',
            'province_id'           => '所在省份',
            'city_id'               => '所在城市',
            'district_id'           => '所在地区',
            'longitude'             => '经度',
            'latitude'              => '纬度'
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {

            //$this->checkDistrict();

            $mchModel = Mch::find()->where([
                'user_id'   => $this->user_id,
                'is_delete' => 0,
                'mall_id'   => \Yii::$app->mall->id,
            ])->one();
            if($mchModel){
                if($mchModel->review_status == Mch::REVIEW_STATUS_UNCHECKED){
                    throw new \Exception('您的商家入驻申请正在审核中！');
                }
                if($mchModel->review_status == Mch::REVIEW_STATUS_CHECKED){
                    throw new \Exception('您的商家入驻申请已通过！');
                }
            }else{
                $mchModel = new Mch();
                $mchModel->mall_id    = \Yii::$app->mall->id;
                $mchModel->created_at = time();
            }

            $mchModel->review_status      = Mch::REVIEW_STATUS_UNCHECKED;
            $mchModel->user_id            = $this->user_id;
            $mchModel->realname           = $this->realname;
            $mchModel->mobile             = $this->mobile;
            $mchModel->mch_common_cat_id  = $this->cat_id;
            $mchModel->updated_at         = time();
            $mchModel->form_data          = json_encode([]);

            if (!$mchModel->save()) {
                throw new \Exception($this->responseErrorMsg($mchModel));
            }

            $this->setStore($mchModel);
            $this->setMallMchSetting($mchModel);
            $this->setMchSetting($mchModel);
            $this->setUser($mchModel);

            $transaction->commit();

            $this->sendMpTpl();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    protected function setUser(Mch $mchModel){
        $user = \Yii::$app->user->identity;
        if ($user->mch_id && $user->mch_id != $mchModel->id) {
            throw new \Exception('商户账号已存在！');
        }

        $user->mch_id = $mchModel->id;
        if (!$user->save()) {
            throw new \Exception($this->responseErrorMsg($user));
        }

    }

    protected function setStore(Mch $mchModel){
        $store = Store::findOne(['mch_id' => $mchModel->id]);
        if (!$store) {
            $store = new Store();
            $store->mall_id     = \Yii::$app->mall->id;
            $store->mch_id      = $mchModel->id;
            $store->description = '欢迎来到' . $this->name;
            $store->scope       = $this->name;
            $store->is_default  = 1;
        }
        $store->name        = $this->name;
        $store->address     = $this->address;
        $store->mobile      = $this->mobile;
        /*$store->province_id = $this->province_id;
        $store->city_id     = $this->city_id;
        $store->district_id = $this->district_id;*/
        $store->province_id = 0;
        $store->city_id     = 0;
        $store->district_id = 0;
        $store->longitude   = $this->longitude;
        $store->latitude    = $this->latitude;
        $store->pic_url     = "http://";
        if (!$store->save()) {
            throw new \Exception($this->responseErrorMsg($store));
        }
    }

    protected function setMchSetting(Mch $mchModel) {
        // 多商户设置
        $mchSetting = MchSetting::findOne(['mch_id' => $mchModel->id]);
        if (!$mchSetting) {
            $mchSetting = new MchSetting();
            $mchSetting->mall_id = \Yii::$app->mall->id;
            $mchSetting->mch_id  = $mchModel->id;
        }
        $sendType = Compatible::getInstance()->sendType($mchSetting->send_type);
        try {
            $sendType = \Yii::$app->serializer->encode($sendType);
        }catch (\Exception $exception) {
            $sendType = \Yii::$app->serializer->encode([]);
        }
        $mchSetting->send_type = $sendType;
        $res = $mchSetting->save();
        if (!$res) {
            throw new \Exception($this->responseErrorMsg($mchSetting));
        }
    }

    protected function setMallMchSetting(Mch $mchModel){

        // 多商户商城设置
        $mchMallSetting = MchMallSetting::findOne(['mch_id' => $mchModel->id]);
        if (!$mchMallSetting) {
            $mchMallSetting = new MchMallSetting();
            $mchMallSetting->mall_id = \Yii::$app->mall->id;
            $mchMallSetting->mch_id  = $mchModel->id;
        }
        $res = $mchMallSetting->save();
        if (!$res) {
            throw new \Exception($this->responseErrorMsg($mchMallSetting));
        }
    }

    /**
     * 检查地区数据
     */
    protected function checkDistrict(){
        $districtData = DistrictData::getTerritorial();

        $districtData = array_combine(array_column($districtData, 'id'), $districtData) ;
        if(!isset($districtData[$this->province_id])){
            throw new \Exception('省份信息选择有误！');
        }

        $districtData = $districtData[$this->province_id]['list'];
        $districtData = array_combine(array_column($districtData, 'id'), $districtData) ;
        if(!isset($districtData[$this->city_id])){
            throw new \Exception('城市信息选择有误！');
        }

        $districtData = $districtData[$this->city_id]['list'];
        $districtData = array_combine(array_column($districtData, 'id'), $districtData) ;
        if($this->district_id && !isset($districtData[$this->district_id])){
            throw new \Exception('区信息选择有误！');
        }

    }

    private function sendMpTpl(){
        //TODO 发送公众号消息
        //...
    }
}