<?php

namespace app\plugins\giftpacks\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;

class GiftpacksEditForm extends BaseModel{

    public $id;
    public $title;
    public $cover_pic;
    public $pic_url;
    public $max_stock;
    public $expired_at;
    public $price;
    public $profit_price;
    public $purchase_limits_num;
    public $descript;

    public $group_enable;
    public $group_price;
    public $group_need_num;
    public $group_expire_time;

    public $allow_currency;
    public $integral_enable;
    public $integral_give_num;

    public $score_enable;
    public $score_give_settings;
    public $detail;

    public $virtual_sales;

    public function rules(){
        return [
            [['title', 'cover_pic', 'pic_url', 'expired_at', 'allow_currency'], 'required'],
            [['integral_give_num', 'purchase_limits_num', 'price', 'profit_price', 'group_price', 'virtual_sales'], 'number', 'min' => 0],
            [['integral_enable', 'score_enable', 'group_enable', 'max_stock', 'group_need_num', 'group_expire_time'], 'integer'],
            [['id', 'descript', 'score_give_settings', 'detail'], 'safe']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            if(!empty($this->id)){
                $model = Giftpacks::findOne((int)$this->id);
                if(!$model){
                    throw new \Exception("大礼包不存在");
                }
            }else{
                $model = new Giftpacks([
                    "mall_id" => \Yii::$app->mall->id,
                    "created_at" => time()
                ]);
            }

            $model->detail              = $this->detail;
            $model->title               = $this->title;
            $model->cover_pic           = $this->cover_pic;
            $model->pic_url             = json_encode($this->pic_url);
            $model->updated_at          = time();
            $model->expired_at          = strtotime($this->expired_at);
            $model->max_stock           = $this->max_stock;
            $model->price               = $this->price;
            $model->profit_price        = $this->profit_price;
            $model->purchase_limits_num = (int)$this->purchase_limits_num;
            $model->descript            = $this->descript;
            $model->group_enable        = $this->group_enable;
            $model->group_price         = $this->group_price;
            $model->group_need_num      = $this->group_need_num;
            $model->group_expire_time   = $this->group_expire_time;

            $model->integral_enable     = $this->integral_enable;
            $model->integral_give_num   = $this->integral_give_num;
            $model->score_enable        = $this->score_enable;
            $model->score_give_settings = is_array($this->score_give_settings) ? json_encode($this->score_give_settings) : '';
            $model->allow_currency      = $this->allow_currency;
            $model->virtual_sales       = $this->virtual_sales;

            if($this->allow_currency != "money"){ //非现金支付
                $model->integral_enable     = 0;
                $model->integral_give_num   = 0;
                $model->score_enable        = 0;
                $model->score_give_settings = '';
            }

            if(!$model->save()){
                throw new \Exception($this->responseErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}