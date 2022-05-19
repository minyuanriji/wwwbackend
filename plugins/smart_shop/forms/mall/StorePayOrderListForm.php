<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\models\User;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\models\StorePayOrder;

class StorePayOrderListForm extends BaseModel{

    public $page;
    public $keyword;
    public $kw_type;
    public $status;

    public function rules(){
        return [
            [['page'], 'integer'],
            [['keyword', 'kw_type', 'status'], 'trim']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {

            $query = StorePayOrder::find()->alias("o")
                ->leftJoin(["u" => User::tableName()], "u.id=o.pay_uid");

            $query->where([
                "o.mall_id" => \Yii::$app->mall->id,
            ]);

            if($this->status){
                if($this->status == "paid"){
                    $query->andWhere(["o.pay_status" => "paid"]);
                }elseif($this->status == "unpaid"){
                    $query->andWhere(["o.pay_status" => "unpaid"]);
                }
            }

            if($this->kw_type && !empty($this->keyword)){
                if($this->kw_type == "order_no"){
                    $query->andWhere(["o.order_no" => $this->keyword]);
                }elseif($this->kw_type == "store_name"){
                    $query->andWhere(["LIKE", "o.store_name", $this->keyword]);
                }elseif($this->kw_type == "pay_user_id"){
                    $query->andWhere(["o.pay_uid" => $this->keyword]);
                }elseif($this->kw_type == "pay_user_kw"){
                    $query->andWhere([
                        "OR",
                        ["LIKE", "u.nickname", $this->keyword],
                        ["LIKE", "u.mobile", $this->keyword]
                    ]);
                }
            }

            $query->select(["o.*", "u.nickname", "u.mobile"]);

            $list = $query->orderBy("o.id DESC")->asArray()
                ->page($pagination, 10, $this->page)->all();

            if($list){
                $payTypes = ['alipay' => '支付宝', 'wechat' => '微信', 'balance' => '余额'];
                foreach($list as $key => $row){
                    $list[$key]['created_at'] = date("Y-m-d H:i:s", $row['created_at']);
                    $list[$key]['pay_time'] = !empty($row['pay_time']) ? date("Y-m-d H:i:s", $row['pay_time']) : "";
                    $list[$key]['pay_type'] = isset($payTypes[$row['pay_type']]) ? $payTypes[$row['pay_type']] : "";
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination,
                ]
            ];
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}