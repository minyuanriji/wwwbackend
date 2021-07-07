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
use app\plugins\stock\models\FillIncomeLog;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\GoodsPriceLog;
use app\models\BaseModel;

class FillIncomeLogListForm extends BaseModel
{
    public $keyword;
    public $platform;
    public $limit = 10;
    public $page = 1;
    public $sort;
    public $type; //1平级 2越级

    public function rules()
    {
        return [
            [['keyword', 'platform'], 'trim'],
            [['keyword', 'platform'], 'string'],
            [['limit', 'page','type'], 'integer'],
            [['sort'], 'default', 'value' => ['l.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = FillIncomeLog::find()
            ->alias('l')
            ->where(['l.user_id' => \Yii::$app->user->id, 'l.is_delete' => 0, 'l.mall_id' => $mall->id])
            ->leftJoin(['od' => FillOrderDetail::tableName()], 'od.id=l.fill_order_detail_id')
            ->leftJoin(['o' => FillOrder::tableName()], 'o.id=od.order_id')
            ->leftJoin(['u' => User::tableName()], 'u.id = o.user_id');
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }
        if ($this->type) {
            $query->andWhere(['l.type' => $this->type]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)
            ->select('l.*,u.nickname,u.avatar_url,od.goods_id,od.num,o.order_no')
            ->orderBy('l.id desc')->asArray()->all();
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
            if ($item['type'] == 0) {
                $newItem['type'] = '下级拿货货款';
            }
            if ($item['type'] == 1) {
                $newItem['type'] = '平级奖';
            }
            if ($item['type'] == 2) {
                $newItem['type'] = '越级奖';
            }
            $newItem['order_price'] = 0;

            $fillOrder=FillOrder::findOne(['order_no'=>$item['order_no']]);
            if($fillOrder){
                $newItem['order_price'] = $fillOrder->pay_price;
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