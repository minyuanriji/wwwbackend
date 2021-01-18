<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\stock\forms\api;

use app\models\Goods;
use app\models\User;
use app\models\UserChildren;
use app\plugins\stock\models\AgentOrder;
use app\plugins\stock\models\FillIncomeLog;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\GoodsPriceLog;
use app\models\BaseModel;
use app\plugins\stock\models\StockAgent;

class AgentOrderListForm extends BaseModel
{
    public $limit = 10;
    public $page = 1;
    public $type = 0;

    public function rules()
    {
        return [
            [['limit', 'page', 'type'], 'integer'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = AgentOrder::find()
            ->alias('ao')
            ->where(['ao.is_delete' => 0, 'ao.mall_id' => $mall->id, 'ao.user_id' => \Yii::$app->user->identity->id]);
        $query->andWhere(['ao.status' => $this->type]);
        $list = $query->page($pagination, $this->limit, $this->page)
            ->select('ao.*')
            ->orderBy('ao.id desc')
            ->asArray()
            ->all();
        foreach ($list as &$item) {
            $goods = Goods::findOne($item['goods_id']);
            $item['cover_pic'] = '';
            $item['goods_name'] = '未知';
            if ($goods) {
                $goodsInfo = $goods->goodsWarehouse;
                $item['cover_pic'] = $goodsInfo->cover_pic;
                $item['goods_name'] = $goodsInfo->name;
            }
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }
}