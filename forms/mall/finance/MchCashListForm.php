<?php
namespace app\forms\mall\finance;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsTransferOrder;
use app\models\Store;
use app\models\User;
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
        $currentApply = 0;
        $currentActual = 0;
        $query = MchCash::find()->alias("mc");
        $query->leftJoin(["eto" => EfpsTransferOrder::tableName()], "mc.order_no=eto.outTradeNo");
        $query->leftJoin(["s" => Store::tableName()], "s.mch_id=mc.mch_id");
        $query->leftJoin(["m" => Mch::tableName()], "m.id=s.mch_id");
        $query->select(["mc.id", "s.mch_id", "s.name", "s.cover_url", "mc.money", "mc.fact_price", "mc.type", "mc.created_at",
            "mc.status", "mc.transfer_status", "m.account_money", "mc.order_no", "mc.service_fee_rate", "mc.updated_at",
            "eto.remark", "mc.content", "mc.type_data"]);

        if ($this->keyword ) {
           $query = $query->andWhere(["IN", "s.name", $this->keyword]);
        }
        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'mc.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'mc.created_at', strtotime($this->start_date)]);
        }

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

        $applyQuery = clone $query;
        $applyMoney = $applyQuery->sum('mc.money');
        $actualQuery = clone $query;
        $actualMoney = $actualQuery->andWhere(['mc.transfer_status' => 1])->sum('mc.fact_price');
        $list = $query->asArray()->page($pagination, $this->limit, $this->page)->all();
        if($list){
            foreach($list as &$item){
                $typeData = @json_decode($item['type_data'], true);
                $item = array_merge($item, is_array($typeData) ? $typeData : []);
                $currentApply += $item['money'];
                if ($item['transfer_status'] == 1) {
                    $currentActual += $item['fact_price'];
                }
            }
        }

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
            'list'       => $list ?: [],
            'Statistics' => [
                'applyMoney' => $applyMoney ?: 0,
                'actualMoney' => $actualMoney ?: 0,
                'currentApply' => $currentApply,
                'currentActual' => $currentActual,
            ],
            'pagination' => $pagination,
        ]);
    }
}