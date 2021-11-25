<?php

namespace app\forms\mall\commission;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
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

        try {
            $query = CommissionGoodsPriceLog::find()->alias('cg')/*->where([
                'cg.mall_id' => \Yii::$app->mall->id,
            ])*/;
            $query->innerJoin(["o" => Order::tableName()], "o.id=cg.order_id");
            $query->innerJoin(["g" => Goods::tableName()], "g.id=cg.goods_id");
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->innerJoin(["u" => User::tableName()], "u.id=cg.user_id")
                ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

            $query->keyword($this->keyword_1 == 1 && $this->keyword, ["LIKE", "u.nickname", $this->keyword]);
            $query->keyword($this->keyword_1 == 2 && $this->keyword, ["u.mobile" => $this->keyword]);
            $query->keyword($this->keyword_1 == 3 && $this->keyword, ["o.order_no" => $this->keyword]);
            $query->keyword($this->keyword_1 == 4 && $this->keyword, ["LIKE", "gw.name", $this->keyword]);

            if ($this->start_date && $this->end_date) {
                $query->andWhere(['<', 'cg.created_at', strtotime($this->end_date)])
                    ->andWhere(['>', 'cg.created_at', strtotime($this->start_date)]);
            }

            if ($this->status === '' || $this->status == -2) {
            } else {
                $query->andWhere(['cg.status' => $this->status]);
            }

            $select = ['cg.*', 'u.nickname', 'u.avatar_url', 'u.role_type', 'u.mobile', 'gw.cover_pic'];
            $list = $query->select($select)->page($pagination, $this->limit)->orderBy('cg.id desc')->asArray()->all();

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
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list' => $list,
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }


}