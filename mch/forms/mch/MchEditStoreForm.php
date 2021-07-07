<?php
namespace app\mch\forms\mch;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\DistrictArr;
use app\models\Model;
use app\models\Store;
use app\plugins\mch\models\Mch;

class MchEditStoreForm extends BaseModel {

    public $address;
    public $name;
    public $logo;
    public $bg_pic_url;
    public $service_mobile;
    public $province_id;
    public $city_id;
    public $district_id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['address', 'service_mobile', 'name'], 'required'],
            [['province_id', 'city_id', 'district_id'], 'integer'],
            [['logo', 'service_mobile'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 65],
            [['bg_pic_url'], 'safe']
        ]);
    }

    public function attributeLabels(){
        return [
            'service_mobile' => '店铺服务电话',
            'name' => '店铺名称'
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        try {

            $this->checkData();
            $this->setStore();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    protected function setStore(){

        $store = Store::findOne([
            'mch_id' => \Yii::$app->mchAdmin->identity->mch_id
        ]);

        if (!$store) {
            $store = new Store();
            $store->mall_id     = \Yii::$app->mall->id;
            $store->mch_id      = $this->mch->id;
            $store->description = '欢迎来到' . $this->name;
            $store->scope       = $this->name;
            $store->is_default  = 1;
        }
        $store->name        = $this->name;
        $store->address     = $this->address;
        $store->cover_url   = $this->logo ?: '/';

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

        $store->province_id     = $this->province_id;
        $store->city_id         = $this->city_id;
        $store->district_id     = $this->district_id;
        $res = $store->save();
        if (!$res) {
            throw new \Exception($this->responseErrorMsg($store));
        }
    }

    protected function checkData(){
        if ($this->bg_pic_url && !is_array($this->bg_pic_url)) {
            throw new \Exception('店铺背景图参数错误');
        }
    }
}
