<?php

namespace app\plugins\giftpacks\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;

class GiftpacksEditForm extends BaseModel{

    public $id;
    public $title;
    public $cover_pic;
    public $price;

    public function rules(){
        return [
            [['title', 'cover_pic'], 'required'],
            [['price'], 'number', 'min' => 0],
            [['id', 'price'], 'safe']
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

            $model->title      = $this->title;
            $model->cover_pic  = $this->cover_pic;
            $model->updated_at = time();
            $model->price      = $this->price;
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