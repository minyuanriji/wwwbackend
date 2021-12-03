<?php


namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinGoods;

class TaoLiJinGoodsEditForm extends BaseModel {

    public $id;
    public $deduct_integral;
    public $price;
    public $status;
    public $name;
    public $detail;
    public $cover_pic;
    public $pic_url;
    public $video_url;
    public $virtual_sales;
    public $unit;
    public $gift_price;
    public $ali_type;
    public $ali_unique_id;
    public $ali_rate;
    public $ali_other_data;
    public $ali_url;

    public function rules(){
        return [
            [['deduct_integral', 'price', 'name', 'cover_pic', 'unit', 'gift_price', 'ali_type',
              'ali_unique_id', 'ali_rate', 'ali_other_data'], 'required'],
            [['id'], 'integer'],
            [['status', 'pic_url', 'video_url', 'detail', 'ali_url', 'virtual_sales'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $goods = TaolijinGoods::findOne($this->id);
            if(!$goods){
                $goods = new TaolijinGoods([
                    "mall_id"    => \Yii::$app->mall->id,
                    "updated_at" => time(),
                    "created_at" => time()
                ]);
            }

            $goods->deduct_integral = $this->deduct_integral;
            $goods->price           = $this->price;
            $goods->status          = (int)$this->status;
            $goods->name            = $this->name;
            $goods->detail          = $this->detail;
            $goods->cover_pic       = $this->cover_pic;
            $goods->pic_url         = json_encode($this->pic_url);
            $goods->video_url       = $this->video_url;
            $goods->virtual_sales   = (int)$this->virtual_sales;
            $goods->unit            = $this->unit;
            $goods->gift_price      = $this->gift_price;
            $goods->ali_type        = $this->ali_type;
            $goods->ali_unique_id   = $this->ali_unique_id;
            $goods->ali_rate        = $this->ali_rate;
            $goods->ali_other_data  = json_encode($this->ali_other_data);
            $goods->ali_url         = $this->ali_url;

            if(!$goods->save()){
                throw new \Exception($this->responseErrorMsg($goods));
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