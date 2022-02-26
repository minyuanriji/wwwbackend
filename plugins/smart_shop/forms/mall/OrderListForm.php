<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\models\IncomeLog;
use app\models\Integral;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\shopping_voucher\models\ShoppingVoucherLog;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\Order;

class OrderListForm extends BaseModel{

    public $page;
    public $mch_id;

    public function rules(){
        return [
            [['page', 'mch_id'], 'integer']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $query = Order::find()->alias("o")
                ->innerJoin(["m" => Mch::tableName()], "m.id=o.bsh_mch_id")
                ->innerJoin(["s" => Store::tableName()], "s.mch_id=o.bsh_mch_id");

            $query->andWhere(["o.is_delete" => 0]);

            $query->orderBy("o.id DESC");

            $selects = ["o.*", "m.mobile", "s.name", "s.cover_url", "m.transfer_rate"];

            $list = $query->select($selects)->asArray()->page($pagination, 10, $this->page)->all();
            $shop = new SmartShop();
            if($list){
                foreach($list as $key1 => $item){
                    $item['detail']     = $shop->getOrderDetail($item['from_table_name'], $item['from_table_record_id']);
                    $item['split_data'] = !empty($item['split_data']) ? (array)@json_decode($item['split_data'], true) : [];
                    if(!isset($item['split_data']['receivers']) || empty($item['split_data']['receivers'])){
                        $item['split_data']['receivers'] = [];
                    }
                    foreach($item['split_data']['receivers'] as $key => $receiver){
                        $item['split_data']['receivers'][$key]['amount'] = round($receiver['amount']/100, 6);
                    }
                    $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);

                    //获取支付用户信息
                    $user = !empty($item['pay_user_mobile']) ? User::findOne(["mobile" => $item['pay_user_mobile']]) : null;
                    $item['user'] = $user ? $user->getAttributes() : '';

                    //获取支付用户上级信息
                    if($user){
                        $parent = User::findOne($user->parent_id);
                        $item['parent'] = $parent ? $parent->getAttributes() : '';
                    }

                    //统计赠送的购物券
                    $item['shopping_voucher'] = round((float)ShoppingVoucherLog::find()->where([
                        "type"        => 1,
                        "source_id"   => $item['id'],
                        "source_type" => "from_smart_shop_order"
                    ])->sum("money"), 2);

                    //统计赠送的积分
                    $item['send_score'] = (int)Integral::find()->where([
                        "source_id"   => $item['id'],
                        "source_type" => "from_smart_shop_order"
                    ])->sum("integral_num");

                    //统计分佣
                    $item['commision_amount'] = round(floatval(IncomeLog::find()->andWhere([
                        "AND",
                        ["source_id" => $item['id']],
                        "source_type LIKE 'smart_shop_order%'",
                        ["flag" => 1],
                        ["is_delete" => 0],
                        ["type" => 1]
                    ])->sum("income")), 2);

                    $list[$key1] = $item;
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
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}