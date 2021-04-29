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
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\GoodsPriceLog;
use app\models\BaseModel;

class BuyGoodsPriceListForm extends BaseModel
{

    public $keyword;
    public $platform;
    public $limit = 10;
    public $page = 1;
    public $sort;
    public $flag;

    public function rules()
    {
        return [
            [['keyword', 'platform'], 'trim'],
            [['keyword', 'platform', 'flag'], 'string'],
            [['limit', 'page'], 'integer'],
            [['sort'], 'default', 'value' => ['o.created_at' => SORT_DESC]],
        ];
    }
    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = FillOrder::find()
            ->alias('o')
            ->where(['o.user_id' => \Yii::$app->user->id, 'o.is_delete' => 0, 'o.mall_id' => $mall->id,'o.is_pay'=>1])
            ->leftJoin(['u' => User::tableName()], 'u.id = o.user_id')
            ->leftJoin(['od' => FillOrderDetail::tableName()], 'od.order_id=o.id');
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)
            ->select('o.*,u.nickname,u.avatar_url,od.fill_price as price,od.goods_id,od.num')
            ->orderBy('o.id desc')->asArray()->all();
        $newList = [];
        foreach ($list as $item) {
            $newItem['price'] = $item['price'];
            $newItem['id'] = $item['id'];
            $newItem['user_id'] = $item['user_id'];
            $newItem['nickname'] = $item['nickname'];
            $newItem['avatar_url'] = $item['avatar_url'];
            $newItem['order_no'] = $item['order_no'];
            $newItem['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
            $goods = Goods::findOne($item['goods_id']);
            $newItem['goods_num'] = $item['num'];
            if ($goods) {
                $goodsInfo = $goods->goodsWarehouse;
                $newItem['goods_name'] = $goodsInfo->name;
                $newItem['cover_pic'] = $goodsInfo->cover_pic;
            }
            $newItem['order_price'] = 0;

            $fillOrder = FillOrder::findOne(['order_no' => $item['order_no']]);
            if($fillOrder){
                $newItem['order_price'] = $fillOrder->pay_price;
            }

            $totalFillPrice = FillOrderDetail::find()->where(['order_id' => $item['id'],"is_delete" => FillOrderDetail::IS_DELETE_NO])->sum("fill_price");
            if(!empty($totalFillPrice)){
                $newItem['price'] = $totalFillPrice;
            }
            $newList[] = $newItem;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ]
        ];
    }
}