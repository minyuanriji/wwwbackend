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
use app\plugins\stock\models\StockGoods;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\UpgradeBag;

class UpgradeBagListForm extends BaseModel
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

        $list = UpgradeBag::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->with('goods')->page($pagination, $this->limit, $this->page)->all();
        $newList = [];

        foreach ($list as $bag) {
            /**
             * @var UpgradeBag $bag
             */
            $level = StockLevel::findOne(['mall_id' => $bag->mall_id, 'level' => $bag->level, 'is_delete' => 0, 'is_use' => 1]);

            if ($level) {
                $newItem['level_name'] = $level->name;
            }
            $goods = $bag->goods;
            if ($bag->stock_goods_id) {
                $stock_goods = Goods::findOne(['mall_id' => $bag->mall_id, 'is_delete' => 0, 'id' => $bag->stock_goods_id]);
                if($goods){
                    $newItem['stock_goods_name'] = $stock_goods->goodsWarehouse->name;
                    $newItem['stock_cover_pic'] = $stock_goods->goodsWarehouse->cover_pic;
                }
            }
            $newItem['id'] = $bag->id;
            if($goods){
                $newItem['goods_name'] = $goods->goodsWarehouse->name;
                $newItem['cover_pic'] = $goods->goodsWarehouse->cover_pic;
            }
            $newItem['name']=$bag->name;
            $newItem['is_stock'] = $bag->is_stock;
            $newItem['is_enable'] = $bag->is_enable;
            $newItem['stock_num'] = $bag->stock_num;
            $newItem['compute_type'] = $bag->compute_type;
            $newItem['unit_price'] = $bag->unit_price;
            $newItem['created_at'] = date('Y-m-d H:i:s', $bag->created_at);
            $newList[] = $newItem;
        }
        unset($bag);
        unset($newItem);
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }
}