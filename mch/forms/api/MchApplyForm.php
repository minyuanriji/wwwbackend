<?php
namespace app\mch\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;

class MchApplyForm extends BaseModel {

    public $user_id;                //绑定用户ID

    public $mobile;                 //手机号
    public $realname;               //申请人真实姓名
    public $pic_id_card_front;      //身份证正面图片
    public $pic_id_card_back;       //身份证背面图片
    public $pic_business_license;   //营业执照图片

    public $cat_id;                 //店铺类型
    public $name;                   //店铺名称
    public $province_id;            //省
    public $city_id;                //市
    public $district_id;            //区
    public $longitude;              //经度
    public $latitude;               //纬度

    public function rules(){
        return array_merge(parent::rules(), [
            [['user_id', 'mobile', 'realname', 'pic_id_card_front', 'pic_id_card_back',
              'pic_business_license', 'cat_id', 'name', 'province_id', 'address',
              'city_id', 'district_id', 'longitude', 'latitude'], 'required'],
            [['user_id', 'cat_id', 'province_id', 'city_id', 'district_id'], 'integer'],
            [['mobile', 'pic_id_card_front', 'address', 'pic_id_card_back', 'pic_business_license'], 'string', 'max' => 255],
            [['realname', 'name', 'longitude', 'latitude'], 'string', 'max' => 65],
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
                $mch = new Mch();
                $mch->mall_id         = \Yii::$app->mall->id;
                $mchModel->created_at = time();
            }

            $mchModel->review_status      = Mch::REVIEW_STATUS_UNCHECKED;
            $mchModel->user_id            = $this->user_id;
            $mchModel->realname           = $this->realname;
            $mchModel->mobile             = $this->mobile;
            $mchModel->mch_common_cat_id  = $this->cat_id;
            $mchModel->updated_at         = time();

            if (!$mchModel->save()) {
                throw new \Exception($this->responseErrorMsg($mchModel));
            }

            //设置店铺
            $this->setStore($mchModel);

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

        try {
            // 小程序端和后台管理端数据不统一，所以要区分
            if (is_array($this->bg_pic_url)) {
                $store->pic_url = \Yii::$app->serializer->encode($this->bg_pic_url);
            } else if (is_string($this->bg_pic_url)) {
                $picUrl = [\Yii::$app->serializer->decode($this->bg_pic_url)];
                $store->pic_url = \Yii::$app->serializer->encode($picUrl);
            } else {
                $store->pic_url = \Yii::$app->serializer->encode([]);
            }
        } catch (\Exception $exception) {
            $store->pic_url = \Yii::$app->serializer->encode([]);
        }

        $store->mobile = $this->service_mobile;
        $store->province_id = $this->province_id;
        $store->city_id = $this->city_id;
        $store->district_id = $this->district_id;
        $res = $store->save();
        if (!$res) {
            throw new \Exception($this->responseErrorMsg($store));
        }
    }

}