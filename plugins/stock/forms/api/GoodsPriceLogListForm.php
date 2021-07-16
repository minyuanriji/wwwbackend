<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\stock\forms\api;

use app\models\CommonOrder;
use app\models\Goods;
use app\models\Order;
use app\models\User;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\GoodsPriceLog;
use app\models\BaseModel;

class GoodsPriceLogListForm extends BaseModel
{

    public $keyword;
    public $platform;
    public $limit = 10;
    public $page = 1;
    public $sort;
    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['keyword', 'platform'], 'trim'],
            [['keyword', 'platform', 'flag'], 'string'],
            [['limit', 'page'], 'integer'],
            [['fields'], 'safe'],
            [['sort'], 'default', 'value' => ['p.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = GoodsPriceLog::find()
            ->alias('p')
            ->where(['p.user_id' => \Yii::$app->user->id, 'p.is_delete' => 0, 'p.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = p.buy_user_id');

        $list = $query->page($pagination, $this->limit, $this->page)->orderBy('p.id DESC')
            ->select('p.*,u.nickname,u.avatar_url')
            ->asArray()->all();
        $newList = [];


        foreach ($list as $item) {
            /**
             * @var User $user ;
             *
             */
            $newItem['price'] = $item['price'];
            $newItem['id'] = $item['id'];
            $newItem['user_id'] = $item['user_id'];
            $newItem['nickname'] = $item['nickname'];
            $newItem['avatar_url'] = $item['avatar_url'];
            $newItem['order_no'] = $item['order_no'];
            $newItem['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
            $goods = Goods::findOne($item['goods_id']);
            if ($goods) {
                $goodsInfo = $goods->goodsWarehouse;
                $newItem['goods_name'] = $goodsInfo->name;
                $newItem['cover_pic'] = $goodsInfo->cover_pic;
            }
            $newItem['order_price'] =0;
            if ($item['type'] == 0) {
                $newItem['type'] = '商城下单';
                $fillOrder=Order::findOne(['order_no'=>$item['order_no']]);
                if($fillOrder){
                    $newItem['order_price'] = $fillOrder->total_pay_price;
                }
            }
            if ($item['type'] == 1) {
                $newItem['type'] = '拿货下单';

                $fillOrder=FillOrder::findOne(['order_no'=>$item['order_no']]);
                if($fillOrder){
                    $newItem['order_price'] = $fillOrder->pay_price;

                }
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