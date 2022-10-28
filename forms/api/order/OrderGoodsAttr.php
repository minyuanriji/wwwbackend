<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-订单商品
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\order;


use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsDistribution;

/**
 * @property int $id
 * @property int $goods_id
 * @property string $sign_id 规格ID标识
 * @property int $stock 库存
 * @property string $original_price 商品原价格
 * @property string $price 商品实际价格
 * @property string $no 货号
 * @property int $weight 重量（克）
 * @property string $pic_url 规格图片
 * @property int $individual_share 是否单独分销设置：0=否，1=是
 * @property int $attr_setting_type 是否详细设置：0=否，1=是
 * @property int $goods_distribution_level 分销等级设置
 * @property int $share_type 佣金配比 0--固定金额 1--百分比
 * @property array $extra
 * @property string $member_price 会员价
 * @property string $score_price 积分抵扣金额
 * @property string $use_score 抵扣的积分
 * @property array $discount 优惠措施
 * @property int $goods_warehouse_id 商品库ID
 * @property string $name 商品库名称
 * @property string $cover_pic 商品库缩略图
 * @property string $detail 商品详情
 * @property string $pic_list 商品轮播图
 * @property int $number 商品数量
 * @property Goods $goods
 * @property GoodsAttr $goodsAttr
 */
class OrderGoodsAttr extends BaseModel
{
    public $id;
    public $goods_id;
    public $sign_id;
    public $stock;
    public $price;
    public $original_price;
    public $no;
    public $weight;
    public $pic_url;

    public $individual_share;
    public $share_type;
    public $member_price;
    public $score_price;
    public $use_score;
    public $discount;
    public $extra;
    public $goods_warehouse_id;
    public $name;
    public $cover_pic;
    public $detail;
    public $pic_list;
    public $number;
    public $goods_distribution_level;
    public $attr_setting_type;

    //独立分销价
    public $is_commisson_price;
    public $user_role_type;

    protected $goods;
    protected $goodsAttr;

    public function rules()
    {
        return [
            [['goods_id', 'stock', 'weight', 'id', 'goods_warehouse_id', 'use_score', 'number'], 'integer'],
            [['price', 'original_price', 'member_price', 'score_price'], 'number', 'min' => 0],
            [['sign_id', 'no', 'pic_url', 'name', 'cover_pic'], 'string', 'max' => 255],
            [['extra', 'discount'], 'safe']
        ];
    }

    /**
     * @param Goods $goods
     */
    public function setGoods($goods)
    {
        $this->goods = $goods;
        $this->goods_warehouse_id = $goods->goods_warehouse_id;
        $this->name = $goods->goodsWarehouse->name;
        $this->cover_pic = $goods->goodsWarehouse->cover_pic;
        $this->detail = $goods->detail;
        $this->pic_list = $goods->goodsWarehouse->pic_url;
    }

    public function getGoods()
    {
        return $this->goods;
    }

    /**
     * @param GoodsAttr $goodsAttr
     * @throws \Exception
     */
    public function setGoodsAttr($goodsAttr)
    {
        if (!$goodsAttr instanceof GoodsAttr) {
            throw new \Exception('参数$goodsAttr必须是app\models\GoodsAttr的一个实例');
        }
        $this->goodsAttr = $goodsAttr;
        $this->attributes = $goodsAttr->attributes;
        $this->original_price = $this->price;
        $this->discount = [];
        $this->extra = $this->getAttrExtra();
    }

    /**
     * 设置商品独立分销价（城市服务商、区域服务商、VIP）
     * @throws \Exception
     */
    public function setCommissionPrice(){
        $user = \Yii::$app->user->getIdentity();
        if($this->goods->enable_commisson_price){
            $this->is_commisson_price = 1;
            $this->user_role_type = $user->role_type;
            if($user->role_type == "branch_office"){
                $this->price = $this->goodsAttr->branch_office_price;
            }elseif($user->role_type == "partner"){
                $this->price = $this->goodsAttr->partner_price;
            }elseif($user->role_type == "store"){
                $this->price = $this->goodsAttr->store_price;
            }
        }
    }

    public function getGoodsAttr()
    {
        return $this->goodsAttr;
    }

    /**
     * @param $goodsAttrId
     * @throws \Exception
     */
    public function setGoodsAttrById($goodsAttrId)
    {
        /* @var GoodsAttr $goodsAttr */
        $goodsAttr = GoodsAttr::find()->where(['id' => $goodsAttrId,"is_delete"=>0])->one();
        if (!$goodsAttr) {
            throw new \Exception('无法查询到规格信息。');
        }

        $this->setGoodsAttr($goodsAttr);
    }

    public function update($runValidation = true, $attributeNames = null)
    {
        $this->goodsAttr->attributes = $this->attributes;
        return $this->goodsAttr->update($runValidation, $attributeNames);
    }

    public function getAttrExtra()
    {
        return [];
    }

}
