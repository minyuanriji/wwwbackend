<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商品api类
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\goods;

use app\forms\admin\permission\role\AdminRole;
use app\forms\admin\permission\role\SuperAdminRole;
use app\forms\common\goods\GoodsMember;
use app\forms\common\video\Video;
use app\models\Admin;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\Mall;
use app\models\User;
use app\services\Goods\PriceDisplayService;
use yii\helpers\ArrayHelper;

/**
 * Class BaseApiGoods
 * @package app\forms\api\goods
 * @property Goods $goods
 * @property Mall $mall
 */
class ApiGoods extends BaseModel
{
    private static $instance;
    public $goods;
    public $mall;
    public $isSales = 1;

    public static function getCommon($mall = null)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        self::$instance->mall = $mall;
        return self::$instance;
    }

    private function defaultData()
    {
        return [
            'id' => '商品id',
            'goods_warehouse_id' => '商品库id',
            'name' => '商品名称',
            'cover_pic' => '商品缩略图',
            'original_price' => '商品原价',
            'unit' => '商品单位',
            'page_url' => '商品跳转路径',
            'is_negotiable' => '是否价格面议',
            'is_level' => '是否享受会员',
            'level_price' => '会员价',
            'price' => '售价',
            'price_content' => '售价文字版',
            'is_sales' => '是否显示销量',
            'sales' => '销量',
        ];
    }

    public function getDetail()
    {
       // $isNegotiable = $this->getNegotiable();
        $isSales = $this->getIsSales();
        try {
            $attrGroups = \Yii::$app->serializer->decode($this->goods->attr_groups);
        } catch (\Exception $exception) {
            $attrGroups = [];
        }

        $PriceDisplayService=new PriceDisplayService(\Yii::$app->mall->id);
        //可抵购物券大于0才显示购物券会员价  2
        if ($this->goods->max_deduct_integral > 0) {
            $price_display = $PriceDisplayService->getGoodsPriceDisplay($this->goods->price_display);
        } else {
            $price_display = [];
        }

        $goodsStock = array_sum(array_column($this->goods->attr, 'stock')) ?? 0;
        $data = [
            'id' => $this->goods->id,
            'goods_warehouse_id' => $this->goods->goods_warehouse_id,
            'mch_id' => $this->goods->mch_id,
            'sign' => $this->goods->sign,
            'name' => $this->goods->name,
            'cover_pic' => $this->goods->coverPic,
            'video_url' => Video::getUrl($this->goods->videoUrl),
            'original_price' => $this->goods->originalPrice,
            'unit' => $this->goods->unit,
            'page_url' => $this->goods->pageUrl,
          //  'is_negotiable' => $isNegotiable,
            'is_level' => $this->goods->is_level,
            'level_price' => $this->getGoodsMember(),
            'price' => $this->goods->price,
           // 'price_content' => $this->getPriceContent($isNegotiable),
            'is_sales' => $isSales,
            'is_show_sales'=>$this->goods->is_show_sales??0,
            'use_attr'=>$this->goods->use_attr,
            'sales' => $this->getSales($isSales, $this->goods->unit),
            'is_delete' => $this->goods -> is_delete,
            'attr_groups' => $attrGroups,
            'attr' => $this->setAttr($this->goods->attr),
            'goods_stock' => $goodsStock,
            'goods_num' => $goodsStock,
            'max_deduct_integral'=>$this->goods->max_deduct_integral,
            'price_display' => $price_display
        ];
        $data = array_merge($data, $this->getPlugin());
        return $data;
    }

    /**
     * @return int
     * 获取是否价格面议
     */
    protected function getNegotiable()
    {
        $data = 0;
        if ($this->goods->sign == '') {
            $mallGoods = $this->goods->mallGoods;
            $data = $mallGoods->is_negotiable;
        }
        return $data;
    }

    /**
     * @return string
     * 获取会员价
     */
    protected function getGoodsMember()
    {
        return GoodsMember::getCommon()->getGoodsMemberPrice($this->goods);
    }

    /**
     * 获取售价文字版
     * @param int $isNegotiable
     * @return string
     */
    protected function getPriceContent($isNegotiable)
    {
        if ($isNegotiable == 1) {
            $priceContent = '价格面议';
        } elseif ($this->goods->price > 0) {
            $priceContent = '￥' . $this->goods->price;
        } else {
            $priceContent = '免费';
        }
        return $priceContent;
    }

    /**
     * 获取是否显示销量
     * @return int|mixed
     */
    protected function getIsSales()
    {
        try {
            $setting = \Yii::$app->mall->getMallSetting(['is_show_sales_num']);
            $isSales = $setting['is_show_sales_num'];
        } catch (\Exception $exception) {
            $isSales = 1;
        }
        return $isSales;
    }

    /**
     * 获取销量
     * @param int $isSales
     * @param string $unit
     * @return string
     */
    protected function getSales($isSales, $unit = '件')
    {
        $sales = '';
        if ($this->isSales == 1 && $isSales == 1) {
            $sales = $this->goods->virtual_sales + $this->goods->getSales();
            $length = strlen($sales);

            if ($length > 8) { //亿单位
                $sales = substr_replace(substr($sales, 0, -7), '.', -1, 0) . "亿";
            } elseif ($length > 4) { //万单位
                $sales = substr_replace(substr($sales, 0, -3), '.', -1, 0) . "w";
            }
            $sales = sprintf("已售%s%s", $sales, $unit);
        }else{
            $sales = sprintf("已售%s%s", $this->goods->getSales(), $unit);
        }
        return $sales;
    }

    /**
     * 获取插件中额外的信息
     * @return array
     */
    protected function getPlugin()
    {
        $list = [];
        try {
            try {
                $pluginList = \Yii::$app->role->getMallRole()->getPluginList();
            } catch (\Exception $exception) {
                /* @var Admin $admin */
                $admin = $this->mall->admin;
                $config = [
                    'userIdentity' => $admin,
                    'user' => $admin,
                    'mall' => $this->mall
                ];
                if ($admin->admin_type == Admin::ADMIN_TYPE_SUPER) {
                    $parent = new SuperAdminRole($config);
                } elseif ($admin->admin_type == Admin::ADMIN_TYPE_ADMIN) {
                    $parent = new AdminRole($config);
                } else {
                    throw new \Exception('错误的账户');
                }
                $pluginList = $parent->getMallRole()->getPluginList();
            }
            foreach ($pluginList as $plugin) {
                $list = array_merge($list, $plugin->getGoodsExtra($this->goods));
            }
        } catch (\Exception $exception) {
        }
        return $list;
    }

    /**
     * 处理规格数据
     * @param null $attr
     * @return array
     * @throws \Exception
     */
    public function setAttr($attr = null)
    {
        if (!$this->goods) {
            throw new \Exception('请先设置商品对象');
        }
        if (!$attr) {
            $attr = $this->goods->attr;
        }
        $newAttr = [];
        $attrGroup = \Yii::$app->serializer->decode($this->goods->attr_groups);
        $attrList = $this->goods->resetAttr($attrGroup);
        /* @var GoodsAttr[] $attr */
        foreach ($attr as $key => $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['attr_list'] = $attrList[$item['sign_id']];
            $newAttr[] = $newItem;
        }
        return $newAttr;
    }
}
