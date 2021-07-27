<?php
namespace app\forms\mall\order;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\OrderClerkExpress;
use app\models\OrderClerkExpressDetail;

class OrderClerkExpressForm extends BaseModel{

   public $order_id;
   public $order_detail_id;
   public $goods_id;
   public $send_type;
   public $express_no;
   public $express_content;
   public $express;
   public $express_code;
   public $store_id;

    public function rules(){
        return [
            [['order_id', 'order_detail_id', 'goods_id', 'send_type'], 'required'],
            [['express_no', 'express_content', 'express', 'express_code', 'store_id'], 'safe']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $orderClerkExpress = OrderClerkExpress::findOne(["order_detail_id" => $this->order_detail_id]);
            $expressDetail = null;
            if($orderClerkExpress) { //已经有一条记录了
                $count = OrderClerkExpress::find()->andWhere([
                    "AND",
                    ["express_detail_id" => $orderClerkExpress->express_detail_id],
                    "id <> '" . $orderClerkExpress->id . "'"
                ])->count();
                if ($count <= 0) {
                    $expressDetail = OrderClerkExpressDetail::findOne($orderClerkExpress->express_detail_id);
                }
            }

            if(!$expressDetail){ //生成发货详情记录
                $expressDetail = new OrderClerkExpressDetail([
                    'mall_id'    => \Yii::$app->mall->id,
                    'created_at' => time()
                ]);
            }

            $expressDetail->send_type       = $this->send_type;
            $expressDetail->express_no      = $this->express_no;
            $expressDetail->is_delete       = 0;
            $expressDetail->deleted_at      = 0;
            $expressDetail->updated_at      = time();
            $expressDetail->express_content = $this->express_content;
            $expressDetail->express         = $this->express;
            $expressDetail->express_code    = $this->express_code;

            if(!$expressDetail->save()){
                throw new \Exception($this->responseErrorMsg($expressDetail));
            }

            if(!$orderClerkExpress){
                $orderClerkExpress = new OrderClerkExpress([
                    "mall_id"         => \Yii::$app->mall->id,
                    "order_id"        => $this->order_id,
                    "order_detail_id" => $this->order_detail_id,
                    "goods_id"        => $this->goods_id,
                    "store_id"        => $this->store_id,
                    "created_at"      => time()
                ]);
            }

            $orderClerkExpress->updated_at        = time();
            $orderClerkExpress->express_detail_id = $expressDetail->id;
            if(!$orderClerkExpress->save()){
                throw new \Exception($this->responseErrorMsg($orderClerkExpress));
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