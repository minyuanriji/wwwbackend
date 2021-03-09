<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-15
 * Time: 19:20
 */


namespace app\forms\mall\goods;

use app\events\GoodsEvent;
use app\forms\common\goods\CommonGoods;
use app\forms\common\mch\MchSettingForm;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCardRelation;
use app\models\GoodsMemberPrice;
use app\models\GoodsServiceRelation;

use app\models\GoodsWarehouse;


use app\plugins\mch\models\Mch;
use yii\db\Exception;

/**
 * @property Goods $goods
 * @property GoodsWarehouse $goodsWarehouse
 */
abstract class BaseGoodsEdit extends BaseModel
{
    public $id;
    public $goods_warehouse_id;
    public $status;
    public $is_on_site_consumption;
    public $price;
    public $use_attr;
    public $attr;
    public $goods_num;
    public $virtual_sales;
    public $cats;
    public $mchCats;
    public $services;
    public $goods_no;
    public $goods_weight;
    public $sort;
    public $is_level;
    public $confine_count;
    public $give_score;
    public $give_score_type;
    public $forehead_score;
    public $forehead_score_type;
    public $accumulative;
    public $is_negotiable;
    public $freight_id;
    public $pieces;
    public $forehead;
    public $app_share_title;
    public $app_share_pic;
    public $pic_url;
    public $is_area_limit;
    public $area_limit;
    public $attr_default_name;
    public $confine_order_count;
    public $fulfil_price = 0;
    public $full_relief_price = 0;
    public $max_deduct_integral = 0;
    public $price_display;
    public $enable_integral;
    public $integral_setting;
    public $enable_score;
    public $score_setting;
    public $is_order_paid;
    public $order_paid;
    public $is_order_sales;
    public $order_sales;
    public $cannotrefund;

    //分销
    public $individual_share;
    public $share_type;

    public $rebate;
    public $attr_setting_type;
    public $cards;
    public $attrGroups;
    public $isNewRecord;
    public $goods;
    public $goodsWarehouse;
    public $member_price;
    public $diffAttrIds = [];
    public $is_level_alone;
    public $is_default_services;
    public $select_attr_groups;
    public $form_id;
    public $mch_id;
    protected $newAttrs;
    protected $sign;


    /** @var  Mch */
    protected $mch;
    public $is_vip_card_goods;
    private $maxNum = 9999999;
    public $is_show_sales;
    public $use_virtual_sales;
    public $labels;

    public function rules()
    {
        return [
            [['status', 'use_attr', 'goods_num', 'price'], 'required'],
            [['use_attr', 'goods_num', 'virtual_sales', 'goods_weight', 'individual_share',
                'share_type', 'attr_setting_type', 'sort', 'is_level',
                'confine_count', 'give_score', 'give_score_type', 'forehead_score_type',
                'accumulative', 'freight_id', 'pieces', 'is_level_alone', 'is_default_services', 'goods_warehouse_id',
                'mch_id', 'form_id', 'is_area_limit', 'confine_order_count',
                'is_vip_card_goods', 'use_virtual_sales', 'is_show_sales'], 'integer'],
            [['goods_no', 'rebate', 'app_share_title', 'app_share_pic', 'attr_default_name'], 'string'],
            [['forehead', 'id','fulfil_price','full_relief_price','max_deduct_integral','enable_integral','enable_score','is_order_paid', 'is_order_sales'], 'number'],
            [['cats', 'mchCats', 'services', 'cards', 'attr', 'attrGroups', 'member_price',
                'select_attr_groups', 'labels','price_display','integral_setting','score_setting','order_paid','order_sales','cannotrefund'], 'safe'],
            [['virtual_sales', 'freight_id', 'is_level', 'is_level_alone', 'forehead', 'forehead_score',
                'give_score', 'individual_share', 'is_level_alone', 'pieces', 'share_type', 'accumulative',
                'attr_setting_type', 'goods_weight', 'is_area_limit', 'form_id'], 'default', 'value' => 0],
            [['app_share_title', 'app_share_pic', 'attr_default_name'], 'default', 'value' => ''],
            [['sort'], 'default', 'value' => 100],
            [['area_limit'], 'trim'],
            [['confine_count', 'confine_order_count'], 'default', 'value' => -1],
            [['forehead_score_type', 'give_score_type', 'is_level',
                'is_default_services'], 'default', 'value' => 1],
            [['price', 'forehead_score'], 'number', 'min' => 0],
            [['price'], 'number', 'max' => 9999999],
            [['is_on_site_consumption'], 'number'],
            [['fulfil_price','full_relief_price'],'default','value'=>0]
        ];
    }

    public function attributeLabels()
    {
        return [
            'status' => '商品上架状态',
            'price' => '商品售价',
            'use_attr' => '是否使用规格',
            'attr' => '商品规格',
            'goods_num' => '商品总库存',
            'goods_weight' => '商品重量',
            'virtual_sales' => '已出售量',
            'sort' => '排序',
            'is_level' => '是否会员价购买',
            'is_level_alone' => '是否单独设置会员价格',
            'app_share_title' => '自定义分享标题',
            'app_share_pic' => '自定义分享图片',
            'pieces' => '单品满件包邮',
            'give_score' => '积分赠送',
            'labels' => '标签',
            'price_display'=>'自定义价格显示',
            'max_deduct_integral'=>'最多购物券抵扣',
            'enable_integral' => '是否启用购物券赠送',
            'integral_setting' => '	购物券赠送设置',
            'enable_score' => '是否启用积分券赠送',
            'score_setting' => ' 积分券赠送设置',
            'is_order_paid' => ' 订单支付后设置',
            'order_paid' => ' 订单支付后参数设置',
            'is_order_sales' => ' 订单完结后设置',
            'order_sales' => ' 订单完结后参数设置',
            'cannotrefund' => '是否支持退换货',
        ];
    }

    /**
     * 规格名称特殊符验证
     */
    protected function attrGroupNameValidator()
    {
        $preg = "/[\'=]|\\\|\"|\|/";

        if ((int)$this->use_attr === 1 && !$this->attrGroups) {
            throw new Exception('请添加规格组信息');
        }

        $arrGroups = [];
        foreach ($this->attrGroups as $item) {
            if (preg_match($preg, $item['attr_group_name'])) {
                throw new Exception('商品规格组、规格名称、规格详情不能包含 \' " \\ = 等特殊符');
            }

            if (!isset($item['attr_list']) || count($item['attr_list']) == 0) {
                throw new Exception('请完善规格组（' . $item['attr_group_name'] . '）的规格值');
            }
            // 规格组名称 不能重复
            if (in_array(trim($item['attr_group_name']), $arrGroups)) {
                throw new \Exception('规格组名称不能重复');
            }
            $arrGroups[] = trim($item['attr_group_name']);

            $arrAttr = [];
            foreach ($item['attr_list'] as $item2) {
                if (preg_match($preg, $item2['attr_name'])) {
                    throw new Exception('商品规格组、规格名称、规格详情不能包含 \' " \\ = 等特殊符');
                }

                if (in_array(trim($item2['attr_name']), $arrAttr)) {
                    throw new \Exception('同一规格组下,规格名称不能重复');
                }
                $arrAttr[] = trim($item2['attr_name']);
            }
        }
    }


    /**
     * 商品规格数据验证
     */
    protected function attrValidator()
    {
        // 多规格检查
        if ((int)$this->use_attr === 1) {
            if (!$this->attr || !is_array($this->attr)) {
                throw new Exception('请完善商品规格信息');
            }

            $goodsNum = 0;
            $num = $this->maxNum;
            foreach ($this->attr as $k => $item) {
                if ($item['stock'] > $num) {
                    throw new \Exception('商品库存不能大于' . $num);
                }
                if ($item['price'] > $num) {
                    throw new \Exception('商品价格不能大于' . $num);
                }
                if ($item['weight'] > $num) {
                    throw new \Exception('商品重量不能大于' . $num);
                }
                // 多规格计算商品库存总数
                $goodsNum += (int)$item['stock'];

                if (!isset($item['stock']) || (int)$item['stock'] < 0) {
                    throw new Exception('规格库存必须大于0');
                }
                if (!isset($item['price']) || (int)$item['price'] < 0) {
                    throw new Exception('规格价格必须大于0');
                }
                if ((int)$item['weight'] < 0) {
                    throw new Exception('规格重量不能小于0');
                }
                if (mb_strlen($item['no']) > 60) {
                    throw new \Exception('货号不能越过60个字符');
                }


                $this->checkExtra($item);

                // 没有会员价时、不需要验证会员价
                if (isset($item['member_price']) && $this->is_level_alone == 1) {
                    foreach ($item['member_price'] as $memberItem) {
                        if ((int)$memberItem < 0) {
                            throw new Exception('多规格会员价不能小于0');
                        }
                        if ((doubleval($memberItem)) > doubleval($item['price'])) {
                            throw new \Exception('会员价不能大于商品售价');
                        }
                    }
                }
            }

            //if ($goodsNum <= 0) {
            //    throw new Exception('请添加多规格商品库存');
            //}
            if ($goodsNum > $num) {
                throw new Exception('商品总库存的值必须不大于9999999。');
            }
        } else {
            // 未开启规格情况下
            //if ($this->goods_num <= 0) {
            //    throw new Exception('请添加商品库存');
            //}
            if ($this->goods_num > $this->maxNum) {
                throw new \Exception('商品总库存不能大于' . $this->maxNum);
            }

            if ($this->goods_weight > $this->maxNum) {
                throw new \Exception('商品重量不能大于' . $this->maxNum);
            }
        }

        // 默认规格下会员价检查
        if ((int)$this->use_attr === 0 && (int)$this->is_level === 1) {
            foreach ($this->member_price as $key => $item) {
                if ($item < 0) {
                    throw new Exception('会员价不能小于0');
                }

                if (doubleval($item) > doubleval($this->price)) {
                    throw new \Exception('会员价不能大于商品售价');
                }
            }
        }
    }

    abstract public function save();

    abstract protected function setGoodsSign();

    /**
     * @return Goods
     */
    protected function getGoods()
    {
        return $this->goods;
    }

    /**
     * @throws \Exception
     * 设置商品
     */
    protected function setGoods()
    {
        $this->handleAttrGroups();
        $this->setMch();

        $common = CommonGoods::getCommon();
        if (!$this->goods_warehouse_id) {
            throw new \Exception('请先选择商品');
        }
        $goodsWarehouse = $common->getGoodsWarehouse($this->goods_warehouse_id);
        if (!$goodsWarehouse) {
            throw new \Exception('商品以删除，请重新选择商品');
        }
        if ($this->id) {
            $this->isNewRecord = false;
            $goods = $common->getGoods($this->id);
            if (!$goods) {
                throw new \Exception('goods商品不存在或以删除');
            }
        } else {
            $goods = new Goods();
            $goods->mall_id = \Yii::$app->mall->id;
            $goods->is_delete = 0;
            $this->isNewRecord = true;
        }
        $this->goodsWarehouse = $goodsWarehouse;
        // 商品
        $goods->goods_warehouse_id = $this->goods_warehouse_id;
        $goods->virtual_sales = $this->virtual_sales;
        $goods->price = $this->price;
        $goods->use_attr = $this->use_attr;
        $goods->attr_groups = \Yii::$app->serializer->encode($this->attrGroups);
        $goods->app_share_title = $this->app_share_title;
        $goods->app_share_pic = $this->app_share_pic;
        $goods->status = $this->status;
        $goods->is_on_site_consumption = $this->is_on_site_consumption;
        $goods->sort = $this->sort;
        $goods->confine_count = $this->confine_count;
        $goods->confine_order_count = $this->confine_order_count;
        $goods->pieces = $this->pieces;
        $goods->forehead = $this->forehead;
        $goods->freight_id = $this->freight_id;
        $goods->give_score = $this->give_score;
        $goods->give_score_type = $this->give_score_type;
        $goods->forehead_score = $this->forehead_score;
        $goods->forehead_score_type = $this->forehead_score_type;
        $goods->accumulative = $this->accumulative;
        $goods->individual_share = $this->individual_share;
        $goods->attr_setting_type = $this->attr_setting_type;
        $goods->form_id = $this->form_id;
        $goods->is_show_sales = $this->is_show_sales;
        $goods->use_virtual_sales = $this->use_virtual_sales;
        $goods->is_area_limit = $this->is_area_limit;
        $goods->area_limit = \Yii::$app->serializer->encode($this->area_limit);
        if($this->labels!=[]){
            $goods->labels = SerializeHelper::encode($this->labels);
        }

        if ($this->mch_id) {
            $goods->is_level = 0;
        } else {
            $goods->is_level = $this->is_level;
        }
        $goods->is_level_alone = $this->is_level_alone;
        $goods->share_type = $this->share_type;
        $goods->sign = $this->setGoodsSign();
        $goods->mch_id = $this->mch_id;
        $goods->is_default_services = $this->is_default_services;

        $goods->fulfil_price = $this->fulfil_price;
        $goods->full_relief_price = $this->full_relief_price;
        $goods->max_deduct_integral = $this->max_deduct_integral;
        $goods->enable_integral = $this->enable_integral;
        $goods->enable_score = $this->enable_score;
        $goods->is_order_paid = $this->is_order_paid;
        $goods->is_order_sales = $this->is_order_sales;

        if(!empty($this->integral_setting)){
            $goods->integral_setting = json_encode($this->integral_setting);
        }
        if(!empty($this->score_setting)){
            $goods->score_setting = json_encode($this->score_setting);
        }
        if(!empty($this->order_paid)){
            $goods->order_paid = json_encode($this->order_paid);
        }
        if(!empty($this->order_sales)){
            $goods->order_sales = json_encode($this->order_sales);
        }

        if($this->cannotrefund!=[]){
            $goods->cannotrefund = SerializeHelper::encode($this->cannotrefund);
        }else{
            $goods->cannotrefund = '';
        }

        //自定义价格显示
        if (!empty($this->price_display)) {
            $goods->price_display = json_encode($this->price_display);
        }

        $res = $goods->save();
        if (!$res) {
            throw new Exception($this->responseErrorMsg($goods));
        }
        $this->goods = $goods;
    }

    /**
     * 商品规格设置
     * @throws Exception
     */
    protected function setAttr()
    {
        if ((int)$this->use_attr === 0) {
            // 未使用规格就添加默认规格
            $this->setDefaultAttr();
            $attrPicList = [];
        } else {
            $this->handleAttr();
            // 多规格数据处理
            $this->newAttrs = $this->attr;
            $attrPicList = array_column($this->attrGroups[0]['attr_list'], 'pic_url', 'attr_id');
        }

        $oldAttr = GoodsAttr::find()->where([
            'is_delete' => 0, 'goods_id' => $this->goods->id
        ])->select('id')->asArray()->all();

        // 是否为新增
        if (!$this->isNewRecord) {
            GoodsAttr::updateAll(['is_delete' => 1,], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
            // GoodsShare::updateAll(['is_delete' => 1], ['is_delete' => 0, 'goods_id' => $this->goods->id]);
            GoodsMemberPrice::updateAll(['is_delete' => 1], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
        }


        // 旧规格ID
        $oldAttrIds = [];
        $newAttrIds = [];
        foreach ($oldAttr as $oldItem) {
            $oldAttrIds[] = $oldItem['id'];
        }

        $goodsStock = 0;
        foreach ($this->newAttrs as $newAttr) {
            $goodsStock += $newAttr['stock'];

            // 记录规格ID数组
            $signIds = '';
            foreach ($newAttr['attr_list'] as $aLItem) {
                $signIds .= $signIds ? ':' . (int)$aLItem['attr_id'] : (int)$aLItem['attr_id'];
            }

            // TODO 待修改
            // 判断规格是需要新增还是更新
            $goodsAttr = null;
            if ($this->goods->id) {
                $goodsAttr = GoodsAttr::findOne([
                    'id' => isset($newAttr['id']) ? $newAttr['id'] : 0,
                    'goods_id' => $this->goods->id
                ]);
            }
            if ($goodsAttr) {
                $goodsAttr->is_delete = 0;
            } else {
                $goodsAttr = new GoodsAttr();
            }
            $goodsAttr->goods_id = $this->goods->id;
            $goodsAttr->sign_id = $signIds;
            $goodsAttr->stock = $newAttr['stock'];
            $goodsAttr->price = $newAttr['price'];
            $goodsAttr->no = $newAttr['no'];
            $goodsAttr->weight = $newAttr['weight'] ?: 0;
            //$goodsAttr->pic_url = $newAttr['pic_url'];
            $key = strstr($signIds, ':', true) ?: $signIds;
            $goodsAttr->pic_url = $attrPicList[$key] ?? '';

            $res = $goodsAttr->save();
            $newAttrIds[] = $goodsAttr->id;
            if (!$res) {
                throw new Exception($this->responseErrorMsg($goodsAttr));
            }

            $diffAttrIds = array_diff($oldAttrIds, $newAttrIds);
            $this->diffAttrIds = count($diffAttrIds) ? $oldAttrIds : $diffAttrIds;
            /**
             * 开放自定义处理规格接口
             */
            $this->setExtraAttr($goodsAttr, $newAttr);


            if (isset($newAttr['member_price'])) {
                foreach ($newAttr['member_price'] as $memberPriceKey => $memberPriceItem) {
                    // 例如键值为 `level1` 去除`level`后就是会员等级
                    $memberLevel = (int)substr($memberPriceKey, 5);
                    // 设置会员价
                    $this->setGoodsMemberPrice($goodsAttr->id, $memberLevel, $memberPriceItem);
                }
            }
        }

        // 商品总库存等于 规格库存总和
        $this->goods->goods_stock = $goodsStock;
        $res = $this->goods->save();
        if (!$res) {
            throw new \Exception($this->responseErrorMsg($this->goods));
        }
    }

    /**
     * 添加默认规格
     * @throws Exception
     *
     */
    private function setDefaultAttr()
    {
        if ($this->select_attr_groups) {
            $attrList = $this->select_attr_groups;
        } else {
            $attrList = [
                [

                    'attr_group_name' => '规格',
                    'attr_group_id' => 1,
                    'attr_id' => 1,
                    'attr_name' => $this->attr_default_name ?: '默认',
                ]
            ];
        }
        $count = 1;
        $attrGroups = [];
        foreach ($attrList as &$item) {
            $item['attr_group_id'] = $count;
            $count++;
            $item['attr_id'] = $count;
            $count++;
            $newItem = [
                'attr_group_id' => $item['attr_group_id'],
                'attr_group_name' => $item['attr_group_name'],
                'attr_list' => [
                    [
                        'attr_id' => $item['attr_id'],
                        'attr_name' => $item['attr_name']
                    ]
                ]
            ];
            $attrGroups[] = $newItem;
        }
        unset($item);
        // 未使用规格 就添加一条默认规格
        $newAttrs = [
            [
                'attr_list' => $attrList,
                'stock' => $this->goods_num,
                'price' => $this->price,
                'no' => $this->goods_no ? $this->goods_no : '',
                'weight' => $this->goods_weight ? $this->goods_weight : 0,
                'pic_url' => '',
            ]
        ];

        // 未使用规格情况下，要把上一次的规格ID 存回去，不然规格记录会重复添加
        if (count($this->attr) === 1 && isset($this->attr[0]['id'])) {
            $newAttrs[0]['id'] = $this->attr[0]['id'];
        }
        $this->goods->attr_groups = \Yii::$app->serializer->encode($attrGroups);
        $res = $this->goods->save();
        if (!$res) {
            throw new Exception($this->responseErrorMsg($this->goods));
        }
        // 将会员价格式调整为 key|value 即 会员等级|会员价
        $memberPrices = $this->member_price;
        $newMemberPrice = [];
        if($memberPrices){
            foreach ($memberPrices as $key => $memberPrice) {
                $newMemberPrice[$key] = $memberPrice;
            }
        }
        $newAttrs[0]['member_price'] = $newMemberPrice;
        $this->newAttrs = $newAttrs;
    }


    /**
     * 设置商品服务
     * @throws Exception
     *
     */
    protected function setGoodsService()
    {
        // 添加服务
        GoodsServiceRelation::updateAll(['is_delete' => 1,], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
        if ($this->services && is_array($this->services)) {
            foreach ($this->services as $item) {
                /* @var GoodsServiceRelation $goodsServiceRelation */
                $goodsServiceRelation = GoodsServiceRelation::findOne([
                    'goods_id' => $this->goods->id,
                    'service_id' => $item['id'],
                ]);
                if ($goodsServiceRelation) {
                    $goodsServiceRelation->is_delete = 0;
                } else {
                    $goodsServiceRelation = new GoodsServiceRelation();
                    $goodsServiceRelation->service_id = $item['id'];
                    $goodsServiceRelation->goods_id = $this->goods->id;
                }
                $res = $goodsServiceRelation->save();

                if (!$res) {
                    throw new Exception($this->responseErrorMsg($goodsServiceRelation));
                }
            }
        }
    }


    /**
     * @param integer $goodsAttrId 商品规格
     * @param integer $level 会员等级
     * @param int $price
     * @throws Exception
     */
    private function setGoodsMemberPrice($goodsAttrId, $level, $price = 0)
    {
        if (!$price) {
            $price = 0;
        }

        $goodsMemberPrice = GoodsMemberPrice::findOne(['goods_attr_id' => $goodsAttrId, 'level' => $level,
            'goods_id' => $this->goods->id, 'is_delete' => 0]);
        // 更新|新增
        if (!$goodsMemberPrice) {
            $goodsMemberPrice = new GoodsMemberPrice();
            $goodsMemberPrice->goods_id = $this->goods->id;
            $goodsMemberPrice->goods_attr_id = $goodsAttrId;
            $goodsMemberPrice->is_delete = 0;
        }

        $goodsMemberPrice->level = $level;
        $goodsMemberPrice->price = floatval($price);
        $res = $goodsMemberPrice->save();
        if (!$res) {
            throw new Exception($this->responseErrorMsg($goodsMemberPrice));
        }
    }



    protected function setExtraAttr($goodsAttr, $newAttr)
    {
        return true;
    }

    protected function setExtraGoods($goods)
    {
        return true;
    }

    protected function checkExtra($goodsAttr)
    {
        return true;
    }

    private function handleAttrGroups()
    {
        $this->attrGroups = $this->addAttrGroupsId($this->attrGroups);
    }

    private function handleAttr()
    {
        foreach ($this->attr as &$item) {
            foreach ($item['attr_list'] as &$alItem) {
                $alItem['attr_group_id'] = $this->newAttrGroupList[$alItem['attr_group_name']];
                $alItem['attr_id'] = $this->newAttrList[$alItem['attr_name']];
            }
            unset($alItem);
        }
        unset($item);
    }

    private $newAttrGroupList = [];
    private $newAttrList = [];
    private $signArr = [];

    private function addAttrGroupsId($list, &$id = 1)
    {
        $newId = 1;
        foreach ($list as $key => $item) {
            if (isset($item['attr_list'])) {
                $this->newAttrGroupList[$item['attr_group_name']] = $newId;
                $list[$key]['attr_group_id'] = $newId++;
                $newItemList = $this->addAttrGroupsId($item['attr_list'], $id);
                $list[$key]['attr_list'] = $newItemList;
            } else {
                if (isset($this->signArr[$item['attr_name']])) {
                    $this->newAttrList[$item['attr_name']] = $this->signArr[$item['attr_name']];
                    $list[$key]['attr_id'] = $this->signArr[$item['attr_name']];
                } else {
                    $this->signArr[$item['attr_name']] = $id;
                    $this->newAttrList[$item['attr_name']] = $id;
                    $list[$key]['attr_id'] = $id++;
                }
            }
        }
        return $list;
    }

    private function setMch()
    {
        if (!$this->mch_id) {
            if (\Yii::$app->mchId) {
                $this->mch_id = \Yii::$app->mchId;
            }
            if (isset(\Yii::$app->admin->identity) && \Yii::$app->admin->identity->mch_id > 0) {
                $this->mch_id = \Yii::$app->admin->identity->mch_id;
            }
        }

        if ($this->mch_id) {
            $this->mch = Mch::findOne($this->mch_id);
        } else {
            $this->mch_id = 0;
        }
    }

    /**
     * 触发商品编辑事件
     * @param bool $isVipCardGoods
     * @param bool $diffAttrIds
     */
    protected function setListener($isVipCardGoods = true, $diffAttrIds = true)
    {
        $event['goods'] = $this->goods;
        $diffAttrIds && $event['diffAttrIds'] = $this->diffAttrIds;
        $isVipCardGoods && $event['isVipCardGoods'] = $this->is_vip_card_goods;
        \Yii::$app->trigger(Goods::EVENT_EDIT, new GoodsEvent($event));
    }

    /**
     * 设置卡券数据
     * @throws Exception
     *
     */
    protected function setCard()
    {
        GoodsCardRelation::updateAll(['is_delete' => 1,], ['goods_id' => $this->goods->id, 'is_delete' => 0]);
        if ($this->cards && is_array($this->cards)) {
            foreach ($this->cards as $k => $item) {
                /* @var GoodsCardRelation $goodsCardRelation */
                $goodsCardRelation = GoodsCardRelation::findOne(['goods_id' => $this->goods->id, 'card_id' => $item['id']]);
                if ($goodsCardRelation) {
                    $goodsCardRelation->is_delete = 0;
                    $goodsCardRelation->num = $item['num'];
                } else {
                    $goodsCardRelation = new GoodsCardRelation();
                    $goodsCardRelation->goods_id = $this->goods->id;
                    $goodsCardRelation->card_id = $item['id'];
                    $goodsCardRelation->num = $item['num'];
                }
                $res = $goodsCardRelation->save();

                if (!$res) {
                    throw new Exception($this->responseErrorMsg($goodsCardRelation));
                }
            }
        }
    }
}
