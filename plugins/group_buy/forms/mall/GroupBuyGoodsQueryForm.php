<?php
/**
 * 拼团商品查询
 * xuyaoxiang
 * 2020/09/02
 */

namespace app\plugins\group_buy\forms\mall;

use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsAttr;
use app\plugins\group_buy\models\PluginGroupBuyGoods;
use app\plugins\group_buy\forms\common\CommonGoodsForm;
use app\models\GoodsWarehouse;
use app\plugins\group_buy\models\PluginGroupBuyGoodsAttr;
use app\plugins\group_buy\services\GroupBuyGoodsServices;
use app\plugins\group_buy\forms\api\GoodsForm;
use app\plugins\group_buy\services\TimeServices;

class GroupBuyGoodsQueryForm extends BaseModel
{
    public $page;
    public $limit;
    public $status;
    public $goods_id;
    public $source = 'backend';
    public $group_buy_id;
    public $goods_name;

    //验证规则
    public function rules()
    {
        return [
            [['page', 'limit', 'status', 'goods_id'], 'integer'],
            [['source', 'goods_name'], 'string']
        ];
    }

    //获取单条数据
    public function show()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
        }

        $CommonGoods     = new CommonGoodsForm();
        $detail          = $CommonGoods->getGoodsDetail($this->goods_id);
        $group_buy_goods = $this->getOneByGoodsId($this->goods_id);

        return $this->returnApiResultData(0, "", ['detail' => $detail, 'group_buy_goods' => $group_buy_goods]);
    }

    /**
     * 根据goods_id获取拼团商品
     * @param $goods_id
     * @return array
     */
    public function getOneByGoodsId($goods_id)
    {
        $model = PluginGroupBuyGoods::find()->where(['goods_id' => $goods_id, 'deleted_at' => 0])->one();

        if (!$model) {
            return null;
        } else {
            $group_buy_goods = $model->toArray();
        }

        $group_buy_goods['group_buy_goods_attr'] = $this->getGourpBuyGoodsAttr($goods_id);
        $TimeServices                            = new TimeServices();
        $group_buy_goods['vaild_time_format']           = $TimeServices->vaildTimeFormat($group_buy_goods['vaild_time']);

        return $group_buy_goods;
    }

    public function getGourpBuyGoodsAttr($goods_id, $as_array = true)
    {
        $all = PluginGroupBuyGoodsAttr::find()
            ->alias('gbga')
            ->leftJoin(['ga' => GoodsAttr::tableName()], 'ga.id=gbga.attr_id')
            ->select(['gbga.*', 'ga.stock as original_stock', 'ga.price as original_price'])
            ->where(['ga.is_delete' => 0, 'ga.goods_id' => $goods_id])
            ->asArray($as_array)
            ->all();

        return $all;
    }

    //按条件查询数据
    private function queryData($params, $is_array = true)
    {
        $params['limit']   = isset($params['limit']) ? $params['limit'] : 1;
        $params['page']    = isset($params['page']) ? $params['page'] : 1;
        $params['mall_id'] = \Yii::$app->mall->id;

        $query = PluginGroupBuyGoods::find()
            ->alias('bg')
            ->leftJoin(Goods::tableName() . " g", 'g.id=bg.goods_id')
            ->leftJoin(GoodsWarehouse::tableName() . " gw", "gw.id=g.goods_warehouse_id")
            ->with('goods')
            ->where(["bg.deleted_at" => 0,
                     "g.deleted_at"  => 0,
            ]);

        //拼团商品状态
        if (isset($params['status'])) {
            $query->andWhere(['bg.status' => $params['status']]);
        }

        if (isset($params['goods_name'])) {
            $query->andWhere(['like', 'gw.name', $params['goods_name']]);
        }

        $query->orderBy('bg.start_at asc');

        $query->asArray($is_array);

        return $query;
    }

    //查询数据列表
    public function queryList()
    {
        $params = $this->attributes;
        $query  = $this->queryData($params);

        $pagination = null;
        $query->page($pagination, $params['limit'], $params['page']);
        $all = $query->all();

        if ($this->source != 'backend') {
            $all        = $this->transAll($all);
            $pagination = $this->getPaginationInfo($pagination);
        } else {
            $all = $this->transAllBackend($all);
        }

        return $this->returnApiResultData(0, "", [
            'list'       => $all,
            'pagination' => $pagination
        ]);
    }

    //单条查询数据
    public function queryOne()
    {
        $params = $this->attributes;
        $query  = $this->queryData($params);

        $data = $query->one();

        $data = $this->transOne($data);

        return $data;
    }

    /**
     * 转换多条数据
     * @param $data
     * @return mixed
     */
    private function transAll($data)
    {
        $temp = [];
        foreach ($data as $key => $value) {
            $temp[$key]['group_buy_goods'] = $this->filterGroupBugGoods($value);
            $temp[$key]['goods']           = $this->filterGoods($value['goods_id']);
            $temp[$key]['goods_warehouse'] = $this->filterGoodsWareHouse($this->getOneGoodsWarehouse($value['goods']));
            $TimeServices = new TimeServices();
            $temp[$key]['group_buy_goods']['start_at_format'] = $TimeServices->getReaminingTimeStartAt($value['start_at']);
        }

        return $temp;
    }

    private function transAllBackend($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->transOneBackend($value);
        }

        return $data;
    }

    private function transOneBackend($item)
    {
        $item['goods']                        = $this->filterGoods($item['goods_id']);
        $total                                = $this->getTotalGoodsBuyOrder($item['id']);
        $item['total_goods_buy_order']        = $total['total_goods_buy_order'];
        $item['total_goods_buy_order_amount'] = $total['total_goods_buy_order_amount'];
        return $item;
    }

    public function getTotalGoodsBuyOrder($group_buy_id)
    {
        $data = PluginGroupBuyGoods::find()->where(['id' => $group_buy_id])->with('activeItem')->asArray(true)->one();

        if (!empty($data['activeItem'])) {
            $data['total_goods_buy_order_amount'] = price_format(array_sum(array_column($data['activeItem'], 'group_buy_price')));
            $data['total_goods_buy_order']        = count($data['activeItem']);
        } else {
            $data['total_goods_buy_order_amount'] = 0;
            $data['total_goods_buy_order']        = 0;
        }

        return $data;
    }

    /**
     * 累计已拼团件数(拼单数)active_item
     * @param $goods_id
     * @return int
     */
    public function getGoodsBuyTotalSales($goods_id)
    {
        $GroupBuyGoodsServices = new GroupBuyGoodsServices();
        $params['goods_id']    = $goods_id;
        $data                  = $GroupBuyGoodsServices->getAllTotalGroupBuyGoods($params);
        $sales=0;
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $sales+=count($value['activeItem']);
            }
        }
        return $sales;
    }

    /**
     * 转换一条数据
     * @param $data
     * @return array|\yii\db\ActiveRecord|null
     */
    private function transOne($data)
    {
        $temp['group_buy_goods'] = self::filterGroupBugGoods($data);
        $temp['goods']           = self::filterGoods($data['goods_id']);
        $temp['goods_warehouse'] = self::filterGoodsWareHouse($this->getOneGoodsWarehouse($data['goods']));
        return $temp;
    }

    /**
     * 获取商品详情
     * @param $data
     * @return array|\yii\db\ActiveRecord|null
     */
    private function getOneGoodsWarehouse($data)
    {
        if (isset($data['goods_warehouse_id'])) {
            return GoodsWarehouse::find()->where(['id' => $data['goods_warehouse_id']])->asArray(true)->one();
        }

        return [];
    }

    /**
     * goods表字段过滤
     * @param $item
     * @return array
     */
    static public function filterGoods($goods_id)
    {
        if (!$goods_id) {
            return [];
        }

        $goods = Goods::findOne($goods_id);

        if (!$goods_id) {
            return [];
        }

        return [
            'id'          => $goods->id,
            'goods_stock' => $goods->goods_stock,
            'sign'        => $goods->sign,
            'name'        => $goods->name,
            'detail'      => $goods->detail,
            'cover_pic'   => $goods->coverPic,
            'pic_url'     => $goods->picUrl,
        ];
    }

    /**
     * goods_ware_house表字段过滤
     * @param $item
     * @return array
     */
    static public function filterGoodsWareHouse($item)
    {
        if (empty($item)) {
            return [];
        }

        return [
            'name'      => $item['name'],
            'detail'    => $item['detail'],
            'cover_pic' => $item['cover_pic'],
            'pic_url'   => $item['pic_url'],
            'unit'      => $item['unit']
        ];
    }

    /**
     * 字段过滤
     * @param $item
     * @return array
     */
    static public function filterGroupBugGoods($item)
    {
        if (empty($item)) {
            return [];
        }

        $GoodsForm = new GoodsForm();
        $sales     = $GoodsForm->getCumulativeSales($item['goods_id']);
        $price     = $GoodsForm->getMinGroupBuyPrice($item['goods_id']);

        return [
            'id'             => $item['id'],
            'mall_id'        => $item['mall_id'],
            'goods_id'       => $item['goods_id'],
            'start_at'       => $item['start_at'],
            'people'         => $item['people'],
            'virtual_people' => $item['virtual_people'],
            'is_virtual'     => $item['is_virtual'],
            'status'         => $item['status'],
            'price'          => $price,
            'sales'          => $sales,
        ];
    }
}