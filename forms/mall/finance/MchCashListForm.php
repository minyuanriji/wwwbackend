<?php
namespace app\forms\mall\finance;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsTransferOrder;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCash;

class MchCashListForm extends BaseModel{

    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $status;

    public function rules(){
        return [
            [['page', 'limit'], 'integer'],
            [['keyword', 'start_date', 'end_date', 'status'], 'trim'],
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = MchCash::find()->alias("mc");
        $query->leftJoin(["eto" => EfpsTransferOrder::tableName()], "mc.order_no=eto.outTradeNo");
        $query->leftJoin(["s" => Store::tableName()], "s.mch_id=mc.mch_id");
        $query->leftJoin(["m" => Mch::tableName()], "m.id=s.mch_id");
        $query->select(["mc.id", "s.mch_id", "s.name", "s.cover_url", "mc.money", "mc.fact_price", "mc.type", "mc.created_at",
            "mc.status", "mc.transfer_status", "m.account_money", "mc.order_no", "mc.service_fee_rate", "mc.updated_at",
            "eto.remark", "mc.content", "mc.type_data"]);

        $query->orderBy(['mc.created_at' => SORT_DESC]);

        if($this->status == "no_confirm"){
            $query->andWhere(["mc.status" => 0]);
        }elseif($this->status == "no_paid"){
            $query->andWhere(["mc.status" => 1]);
            $query->andWhere(["mc.transfer_status" => 0]);
        }elseif($this->status == "paid"){
            $query->andWhere(["mc.status" => 1]);
            $query->andWhere(["mc.transfer_status" => 1]);
        }elseif($this->status == "refuse"){
            $query->andWhere(["mc.status" => 2]);
            $query->andWhere(["mc.transfer_status" => 0]);
        }elseif($this->status == "return"){
            $query->andWhere(["mc.status" => 2]);
            $query->andWhere(["mc.transfer_status" => 2]);
        }

        $list = $query->asArray()->page($pagination, $this->limit, $this->page)->all();
        if($list){
            foreach($list as &$item){
                $typeData = @json_decode($item['type_data'], true);
                $item = array_merge($item, is_array($typeData) ? $typeData : []);
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list'       => $list ? $list : [],
                'pagination' => $pagination,
            ]
        ];
    }
}