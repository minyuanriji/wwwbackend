<?php

namespace app\forms\mall\commission;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\OrderDetail;
use app\models\Store;
use app\models\User;
use app\plugins\commission\models\CommissionCheckoutPriceLog;
use app\plugins\commission\models\CommissionGoodsPriceLog;
use app\plugins\commission\models\CommissionStorePriceLog;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;

class StoreScanCodeLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $keyword_1;
    public $status;

    public function rules()
    {
        return [
            [['page', 'limit', 'status', 'keyword_1'], 'integer'],
            [['keyword', 'start_date', 'end_date'], 'trim'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) return $this->responseErrorInfo();

        $query = CommissionCheckoutPriceLog::find()->alias('cc')->where([
            'cc.mall_id' => \Yii::$app->mall->id,
        ]);
        $query->innerJoin(["co" => MchCheckoutOrder::tableName()], "co.id=cc.checkout_order_id");
        $query->innerJoin(["s" => Store::tableName()], "s.mch_id=co.mch_id");
        $query->innerJoin(["u" => User::tableName()], "u.id=cc.user_id")
            ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

        $query->keyword($this->keyword_1 == 1 && $this->keyword, ["LIKE", "s.name", $this->keyword]);
        $query->keyword($this->keyword_1 == 2 && $this->keyword, ["u.mobile" => $this->keyword]);
        $query->keyword($this->keyword_1 == 3 && $this->keyword, ["co.order_no" => $this->keyword]);
        $query->keyword($this->keyword_1 == 4 && $this->keyword, ["s.mch_id" => (int)$this->keyword]);

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'cc.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'cc.created_at', strtotime($this->start_date)]);
        }

        if ($this->status === '' || $this->status == -2) {
        } else {
            $query->andWhere(['cc.status' => $this->status]);
        }

        $select = ['cc.*', 'u.nickname', 'u.avatar_url', 'u.role_type', 'u.mobile'];
        $list = $query->select($select)->orderBy('cc.id desc')->page($pagination, $this->limit)->asArray()->all();
        if ($list) {
            foreach ($list as &$item) {
                $item['order_no']       = '';
                $item['pay_user_name']  = '';
                $item['order_price']    = '';
                $item['pay_price']      = '';
                $item['store_name']     = '';
                $item['store_url']      = '';
                $item['score_deduction_price']      = '';
                $item['integral_deduction_price']      = '';
                $mch_checkout_order = MchCheckoutOrder::findOne($item['checkout_order_id']);
                if ($mch_checkout_order) {
                    $item['order_no'] = $mch_checkout_order->order_no;
                    $item['order_price'] = $mch_checkout_order->order_price;
                    $item['pay_price'] = $mch_checkout_order->pay_price;
                    $item['score_deduction_price']      = $mch_checkout_order->score_deduction_price;
                    $item['integral_deduction_price']   = $mch_checkout_order->integral_deduction_price;

                    $user = User::findOne($mch_checkout_order->pay_user_id);
                    $item['pay_user_name'] = $user ? $user->nickname : '';

                    $store = Store::findOne(["mch_id" => $mch_checkout_order->mch_id]);
                    if ($store) {
                        $item['store_name'] = $store->name;
                        $item['store_url'] = $store->cover_url ?: 'https://www.mingyuanriji.cn/web/static/header-logo.png';
                    }
                }
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}