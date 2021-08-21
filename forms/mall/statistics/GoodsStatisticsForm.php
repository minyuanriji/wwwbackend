<?php

namespace app\forms\mall\statistics;

use app\core\ApiCode;
use app\forms\api\admin\CashForm;
use app\forms\api\admin\ReviewForm;
use app\forms\mall\export\DataStatisticsExport;
use app\forms\mall\order\OrderForm;
use app\forms\mall\order\OrderRefundListForm;
use app\models\GoodsWarehouse;
use app\plugins\mch\models\Mch;
use app\models\BaseModel;
use app\models\Goods;
use app\models\MallSetting;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\Store;
use app\models\User;

class GoodsStatisticsForm extends BaseModel
{
    public $date_start;
    public $date_end;
    public $page;
    public $limit;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules()
    {
        return [
            [['keyword','sort_prop','sort_type'], 'string'],
            [['page', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $goods_query = $this->goods_where();

        $new_query = clone $goods_query;
        $goods_query->select("g.`id`, gw.name, COALESCE(SUM(od.`total_price`),0) AS `total_price`,COALESCE(SUM(od.`num`),0) AS `num`")->groupBy('g.goods_warehouse_id');

        $goods_list = $goods_query
            ->page($pagination, $this->limit, $this->page)
            ->orderBy('num DESC')
            ->asArray()
            ->all();

        if ($goods_list) {
            foreach ($goods_list as &$value) {
                $value['refund_num'] = $new_query->andWhere(['od.is_refund' => 1, 'od.goods_id' => $value['id']])->sum('num') ?: 0;
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $goods_list,
                'pagination' => $pagination
            ]
        ];
    }
    protected function goods_where()
    {
        $query = Order::find()->alias('o')
            ->where(['g.mall_id' => \Yii::$app->mall->id, 'o.is_recycle' => 0, 'o.is_delete' => 0, 'o.is_pay' => 1])
            ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id = o.id')
            ->leftJoin(['g' => Goods::tableName()], 'g.id = od.goods_id')
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'g.goods_warehouse_id = gw.id');

        if ($this->keyword) {
            $query->andWhere(['or', ['like', 'gw.name', $this->keyword], ['like', 'g.id', $this->keyword]]);
        }

        //时间查询
        if ($this->date_start) {
            $query->andWhere(['>=', 'od.created_at', strtotime($this->date_start . ' 00:00:00')]);
        }

        if ($this->date_end) {
            $query->andWhere(['<=', 'od.created_at', strtotime($this->date_end . ' 23:59:59')]);
        }

        $query->andWhere(['!=', 'o.cancel_status', 1]);

        return $query;
    }
}
