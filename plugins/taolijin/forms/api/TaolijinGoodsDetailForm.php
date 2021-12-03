<?php
namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinAli;
use app\plugins\taolijin\models\TaolijinGoods;
use app\plugins\taolijin\models\TaolijinUserAuth;

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

            //用户授权
            $userNeedAuth = false;
            $aliModel = TaolijinAli::findOne($goods->ali_id);
            if(!$aliModel || $aliModel->is_delete){
                throw new \Exception("联盟信息[ID:{$goods->ali_id}]不存在");
            }
            $userAuth = TaolijinUserAuth::findOne([
                "ali_id"  => $aliModel->id,
                "user_id" => \Yii::$app->user->id
            ]);
            if(!$userAuth || $userAuth->access_token_expire_at < time()){
                $userNeedAuth = true;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'auth_req' => $userNeedAuth ? 1 : 0,
                    'detail'   => static::getDetail($goods)
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
        $attrs = ["id", "ali_id", "mall_id", "deduct_integral", "price", "name", "cover_pic", "pic_url",
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