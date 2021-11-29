<?php
namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinGoods;

class TaolijinGoodsDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function detail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $goods = TaolijinGoods::findOne($this->id);
            if(!$goods || $goods->is_delete){
                throw new \Exception("商品不存在");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => static::getDetail($goods)
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    public static function getDetail(TaolijinGoods $goods){
        $attrs = ["id", "mall_id", "deduct_integral", "price", "name", "cover_pic", "pic_url",
            "video_url", "unit", "gift_price", "ali_type", "detail"];
        $details = [];
        foreach($attrs as $attr){
            if(isset($goods->$attr)){
                $details[$attr] = $goods->$attr;
            }
        }

        $details['pic_url'] = json_decode($details['pic_url']);
        $details['extra_data'] = json_decode($goods->ali_other_data, true);

        return $details;
    }

}