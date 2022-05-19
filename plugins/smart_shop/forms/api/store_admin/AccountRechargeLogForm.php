<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\smart_shop\models\StorePayOrder;

class AccountRechargeLogForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $page;

    public function rules(){
        return [
            [['merchant_id', 'store_id'], 'required'],
            [['page'], 'integer']
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
                "o.ss_mch_id" => $this->merchant_id,
                "o.ss_store_id" => $this->store_id
            ]);

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