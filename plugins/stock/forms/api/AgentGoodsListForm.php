<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\stock\forms\api;

use app\helpers\SerializeHelper;
use app\models\Goods;
use app\models\BaseModel;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockAgentGoods;
use app\plugins\stock\models\StockGoods;

class AgentGoodsListForm extends BaseModel
{
    public $limit = 10;
    public $page = 1;

    public function rules()
    {
        return [
            [['limit', 'page'], 'integer'],

        ];
    }
    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $agent = StockAgent::findOne(['user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0]);
        $query = StockAgentGoods::find()
            ->alias('ag')
            ->leftJoin(['sg' => StockGoods::tableName()], 'sg.goods_id=ag.goods_id')
            ->where(['ag.is_delete' => 0,'sg.is_delete' => 0, 'ag.mall_id' => $mall->id, 'ag.user_id' => \Yii::$app->user->identity->id]);
        $list = $query->page($pagination, $this->limit, $this->page)
            ->select('ag.*,sg.agent_price')
            ->orderBy('ag.id desc')->asArray()->all();

        foreach ($list as &$item) {
            $newItem['id'] = $item['id'];
            $goods = Goods::findOne($item['goods_id']);
            if ($goods) {
                $goodsInfo = $goods->goodsWarehouse;
                $item['cover_pic'] = $goodsInfo->cover_pic;
                $item['goods_name'] = $goodsInfo->name;
            }
            $item['stock_price'] = 0;
            if ($agent) {
                if (!empty($item["agent_price"]) && $item['agent_price'] != "null") {
                    $agent_price = SerializeHelper::decode($item['agent_price']);
                    foreach ($agent_price as $price) {
                        if ($agent->level == $price['level']) {
                            $item['stock_price'] = isset($price['stock_price']) ? $price['stock_price'] : 0;
                        }
                    }
                    unset($price);
                }
            }
        }
        unset($item);
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