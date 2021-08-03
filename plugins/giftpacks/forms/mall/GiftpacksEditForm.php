<?php

namespace app\plugins\giftpacks\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;

class GiftpacksEditForm extends BaseModel{

    public $id;
    public $title;
    public $cover_pic;
    public $max_stock;
    public $price;
    public $profit_price;
    public $descript;

    public $group_enable;
    public $group_price;
    public $group_need_num;
    public $group_expire_time;

    public function rules(){
        return [
            [['title', 'cover_pic'], 'required'],
            [['price', 'profit_price', 'group_price'], 'number', 'min' => 0],
            [['group_enable', 'max_stock', 'group_need_num', 'group_expire_time'], 'integer'],
            [['id', 'descript'], 'safe']
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

            $model->title             = $this->title;
            $model->cover_pic         = $this->cover_pic;
            $model->updated_at        = time();
            $model->max_stock         = $this->max_stock;
            $model->price             = $this->price;
            $model->profit_price      = $this->profit_price;
            $model->descript          = $this->descript;
            $model->group_enable      = $this->group_enable;
            $model->group_price       = $this->group_price;
            $model->group_need_num    = $this->group_need_num;
            $model->group_expire_time = $this->group_expire_time;
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