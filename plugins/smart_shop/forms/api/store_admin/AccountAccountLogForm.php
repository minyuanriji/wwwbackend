<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\smart_shop\models\StoreAccountLog;
use app\plugins\smart_shop\models\StorePayOrder;

class AccountAccountLogForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $page;

    public function rules()
    {
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

            $query = StoreAccountLog::find()->alias("l");

            $query->where([
                "l.mall_id" => \Yii::$app->mall->id,
                "l.ss_mch_id" => $this->merchant_id,
                "l.ss_store_id" => $this->store_id
            ]);

            $query->select(["l.*"]);

            $list = $query->orderBy("l.id DESC")->asArray()
                ->page($pagination, 10, $this->page)->all();

            if($list){
                $payTypes = ['alipay' => '支付宝', 'wechat' => '微信', 'balance' => '余额'];
                foreach($list as $key => $row){
                    $row['created_at'] = date("Y-m-d H:i:s", $row['created_at']);
                    if($row['source_type'] == "store_pay_order"){
                        $info = StorePayOrder::find()->alias("o")
                            ->select(["o.order_no", "o.order_price", "o.pay_type", "o.pay_time", "u.nickname", "u.mobile"])
                            ->where(["o.id" => $row['source_id']])
                            ->leftJoin(["u" => User::tableName()], "u.id=o.pay_uid")
                            ->asArray()
                            ->one();
                        if($info){
                            $info['pay_type'] = isset($payTypes[$info['pay_type']]) ? $payTypes[$info['pay_type']] : "";
                        }
                        $row['source_info'] = $info;
                    }else{
                        $row['source_type'] = '';
                    }
                    $list[$key] = $row;
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