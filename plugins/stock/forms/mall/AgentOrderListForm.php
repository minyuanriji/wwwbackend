<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\stock\forms\mall;


use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\User;
use app\plugins\stock\models\AgentOrder;
use app\plugins\stock\models\StockGoods;

class AgentOrderListForm extends BaseModel
{
    public $limit = 10;
    public $page = 1;
    public $type = 0;
    public $keyword;

    public function rules()
    {
        return [
            [['limit', 'page', 'type'], 'integer'],
            [['keyword'],'string']
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
            ->leftJoin(['u'=>User::tableName()],'u.id=ao.user_id')
            ->where(['ao.is_delete' => 0, 'ao.mall_id' => $mall->id]);
        if($this->keyword){
            $query->andWhere(['like','u.nickname',$this->keyword]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)
            ->select('ao.*,u.nickname,u.avatar_url')
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
            $item['created_at']=date('Y-m-d H:i:s',$item['created_at']);


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