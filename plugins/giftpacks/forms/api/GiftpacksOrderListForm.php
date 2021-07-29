<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;

class GiftpacksOrderListForm extends BaseModel{

    public $page;
    public $status;

    public function rules(){
        return [
            [['page'], 'integer'],
            [['status'], 'safe']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = GiftpacksOrder::find()->alias("go")
                        ->innerJoin(["gf" => Giftpacks::tableName()], "gf.id=go.pack_id");
            $query->where([
                "go.mall_id"   => \Yii::$app->mall->id,
                "go.user_id"   => \Yii::$app->user->id,
                "go.is_delete" => 0
            ]);
            if($this->status == "paid") {
                $query->andWhere(["go.pay_status" => "paid"]);
            }elseif($this->status == "refund"){
                $query->andWhere(["IN", "go.pay_status", ["refund", "refunding"]]);
            }else{
                $query->andWhere(["go.pay_status" => "unpaid"]);
            }

            $selects = ["go.id as order_id", "go.order_sn", "go.order_price",
                "go.created_at", "go.pay_status", "go.pay_price", "go.pay_type",
                "go.integral_deduction_price",
                "go.pack_id", "gf.title", "gf.cover_pic"
            ];

            $query->orderBy("go.id DESC");

            $list = $query->select($selects)->page($pagination, 10, max(1, (int)$this->page))
                        ->asArray()->all();
            if($list){
                foreach($list as &$item){
                    $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}