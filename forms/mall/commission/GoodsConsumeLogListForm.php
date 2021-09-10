<?php

namespace app\forms\mall\commission;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\commission\models\CommissionGoodsPriceLog;

class GoodsConsumeLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $status;

    public function rules()
    {
        return [
            [['page', 'limit', 'status'], 'integer'],
            [['keyword', 'start_date', 'end_date'], 'trim'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) return $this->responseErrorInfo();

        $query = CommissionGoodsPriceLog::find()->alias('cg')->where([
            'cg.mall_id' => \Yii::$app->mall->id,
        ])->joinwith(['user' => function ($query) {
            if ($this->keyword) {
                //$query->where(['like', 'nickname', $this->keyword]);
            }
        }])->orderBy('id desc');
        $query->innerJoin(["g" => Goods::tableName()], "g.id=cg.goods_id");
        $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
        $query->innerJoin(["u" => User::tableName()], "u.id=cg.user_id");
        if($this->keyword){
            $query->andWhere([
                "OR",
                ["LIKE", "u.nickname", $this->keyword],
                ["LIKE", "gw.name", $this->keyword]
            ]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'cg.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'cg.created_at', strtotime($this->start_date)]);
        }

        if ($this->status === '' || $this->status == -2) {
        } else {
            $query->andWhere(['cg.status' => $this->status]);
        }

        $list = $query->page($pagination, $this->limit)->asArray()->all();

        if ($list) {
            foreach ($list as &$item) {
                $item['goods_name'] = '';
                $goods = Goods::findOne($item['goods_id']);
                if ($goods) {
                    $goods_ware = GoodsWarehouse::findOne($goods->goods_warehouse_id);
                    $item['goods_name'] = $goods_ware ? $goods_ware->name : '';
                }
                $order_detail = OrderDetail::findOne($item['order_detail_id']);
                if ($order_detail) {
                    $item['num'] = $order_detail->num;
                    $item['total_original_price'] = $order_detail->total_original_price;
                    $item['total_price'] = $order_detail->total_price;
                    $item['use_score_price'] = $order_detail->use_score_price;
                    $item['integral_price'] = $order_detail->integral_price;
                } else {
                    $item['num'] = '';
                    $item['total_original_price'] = '';
                    $item['total_price'] = '';
                    $item['use_score_price'] = '';
                    $item['integral_price'] = '';
                }
                $order = \app\models\Order::findOne($item['order_id']);
                if ($order) {
                    $user = User::findOne($order->user_id);
                    $item['buy_user_name'] = $user ? $user->nickname : '';
                    $item['order_no'] = $order->order_no;
                    $item['total_pay_price'] = $order->total_pay_price;
                } else {
                    $item['order_no'] = '';
                    $item['buy_user_name'] = '';
                    $item['total_pay_price'] = '';
                }
                $item['identity'] = '';
                if (isset($item['user']['role_type'])) {
                    if ($item['user']['role_type'] == 'store') {
                        $item['identity'] = 'VIP会员';
                    } elseif ($item['user']['role_type'] == 'partner') {
                        $item['identity'] = '合伙人';
                    } elseif ($item['user']['role_type'] == 'branch_office') {
                        $item['identity'] = '分公司';
                    } elseif ($item['user']['role_type'] == 'user') {
                        $item['identity'] = '普通用户';
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