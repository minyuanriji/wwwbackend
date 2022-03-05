<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 10:34
 */


namespace app\forms\mall\goods;

use app\core\ApiCode;
use app\events\GoodsEvent;
use app\forms\common\goods\CommonGoods;
use app\forms\common\mch\MchSettingForm;
use app\logic\AppConfigLogic;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsWarehouse;
use app\models\MallGoods;
use app\plugins\mch\models\MchGoods;
use app\controllers\business\PostageRules;


/**
 * Class GoodsEditForm
 * @package app\forms\mall\goods
 * @Notes
 * @property MallGoods $goods
 */
class GoodsEditForm extends BaseGoodsEdit
{
    // 商品库商品字段
    public $name;
    public $product;
    public $original_price;
    public $cost_price;
    public $detail;
    public $video_url;
    public $unit;
    public $pic_url;
    // 商城商品特有字段
    public $is_negotiable;
    public $is_sell_well;
    protected $mallGoods;
    public $use_virtual_sales;
    public $is_show_sales;
    public $labels;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'original_price', 'cost_price', 'detail', 'unit',], 'required'],
            [['is_sell_well', 'is_negotiable', 'is_show_sales', 'use_virtual_sales'], 'integer'],
            [['video_url','product'], 'string'],
            [['original_price', 'cost_price'], 'number', 'min' => 0],
            [['pic_url','labels'], 'safe'],
            [['is_sell_well', 'is_negotiable',], 'default', 'value' => 0],
            [['cost_price', 'original_price'], 'number', 'max' => 9999999]
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name' => '商品名称',
            'original_price' => '商品原价',
            'cost_price' => '商品成本价',
            'detail' => '商品详情',
            'cover_pic' => '商品缩略图',
            'video_url' => '商品视频',
            'unit' => '商品单位',
            'is_sell_well' => '是否热销',
            'use_virtual_sales' => '启用虚拟销量',
            'is_show_sales' => '是否显示销量',
            'labels'=>'标签'
        ]);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 10:35
     * @Note:保存商品
     * @return array
     */

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        if (count($this->pic_url) <= 0) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请上传商品轮播图'
            ];
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->attrValidator();
            $this->attrGroupNameValidator();
            if (!$this->id) {
                $this->add();
            } else {
                $this->update();
            }
            $this->setAttr();
            $this->setGoodsCat();
            $this->setGoodsService();
            $this->setListener();
            $transaction->commit();
//            echo '<pre>';
//            (new PostageRules()) -> CustomPostage($this -> expressName);
//            exit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'goods_id' => $this->goods->id
                ],
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ];
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 10:35
     * @Note:商品标志
     * @return string
     */

    protected function setGoodsSign()
    {
        return $this->mch_id > 0 ? 'mch' : '';
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 10:35
     * @Note:往添加商品仓库
     * @throws \Exception
     */

    private function add()
    {
        $goodsWarehouse = $this->editGoodsWarehouse();
        $this->goods_warehouse_id = $goodsWarehouse->id;
        $this->setGoods();
        $this->editMallGoods();
        $this->editMchGoods();
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 10:36
     * @Note:更新仓库
     * @throws \Exception
     */
    private function update()
    {
        $common = CommonGoods::getCommon();
        $this->setGoods();
        if (!$this->goods->goodsWarehouse) {
            throw new \Exception('商品库错误：查找不到id为' . $this->goods->goods_warehouse_id . '的商品');
        }
        $this->editGoodsWarehouse($this->goods->goodsWarehouse);

        $mallGoods = $common->getMallGoods($this->goods->id);
        if (!$mallGoods) {
            throw new \Exception('mall_goods商品不存在或者已删除');
        }
        $this->editMallGoods($mallGoods);
        $this->editMchGoods($this->goods->id);
        //更新首页装修中添加过的商品
        $goodsData = [];
        $goodsData["id"] = $this->id;
        $goodsData["name"] = $this->name;
        $goodsData["price"] = $this->price;
        $goodsData["cover_pic"] = $this->pic_url[0]['pic_url'];
        $goodsData["max_deduct_integral"] = $this->max_deduct_integral;
        $goodsData["price_display"] = $this->price_display;
        AppConfigLogic::findHomePageGoods($goodsData);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 10:36
     * @Note:编辑商品仓库
     * @param null $goodsWarehouse
     * @return GoodsWarehouse|null
     * @throws \Exception
     */

    private function editGoodsWarehouse($goodsWarehouse = null)
    {
        if (!$goodsWarehouse) {
            $goodsWarehouse = new GoodsWarehouse();
            $goodsWarehouse->mall_id = \Yii::$app->mall->id;
            $goodsWarehouse->is_delete = 0;
        }
        $goodsWarehouse->name = $this->name;
        $goodsWarehouse->product = $this->product;
        $goodsWarehouse->original_price = $this->original_price;
        $goodsWarehouse->cost_price = $this->cost_price;
        $goodsWarehouse->detail = $this->detail;
        $goodsWarehouse->cover_pic = $this->pic_url[0]['pic_url'];
        $goodsWarehouse->pic_url = \Yii::$app->serializer->encode($this->pic_url);
        $goodsWarehouse->video_url = $this->video_url;
        $goodsWarehouse->unit = $this->unit;
        if (!$goodsWarehouse->save()) {
            throw new \Exception('商品保存失败：' . $this->responseErrorMsg($goodsWarehouse));
        }
        $this->goodsWarehouse = $goodsWarehouse;
        return $goodsWarehouse;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 10:37
     * @Note:编辑商城商品
     * @param null $mallGoods
     * @return MallGoods|null
     * @throws \Exception
     */
    private function editMallGoods($mallGoods = null)
    {
        if (!$mallGoods) {
            $mallGoods = new MallGoods();
            $mallGoods->is_delete = 0;
            $mallGoods->mall_id = \Yii::$app->mall->id;
            $mallGoods->goods_id = $this->goods->id;
        }
        $mallGoods->is_sell_well = $this->is_sell_well;
        $mallGoods->is_negotiable = $this->is_negotiable;
        if (!$mallGoods->save()) {
            throw new \Exception('商品保存失败：' . $this->responseErrorMsg($mallGoods));
        }
        return $mallGoods;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 10:37
     * @Note:多商户商品编辑
     * @param null $goodsId
     * @return MchGoods|bool|null
     * @throws \Exception
     */
    private function editMchGoods($goodsId = null)
    {
        if ($this->mch_id <= 0) {
            return false;
        }
        $mchGoods = null;
        if ($goodsId) {
            $mchGoods = MchGoods::findOne(['goods_id' => $goodsId]);
            if (!$mchGoods) {
                throw new \Exception('商户商品不存在');
            }
        }

        if (!$mchGoods) {
            $mchGoods = new MchGoods();
            $mchGoods->is_delete = 0;
            $mchGoods->mall_id = \Yii::$app->mall->id;
            $mchGoods->mch_id = $this->mch_id;
            $mchGoods->goods_id = $this->goods->id;
        }

        // 多商户开启商品上架审核,每次编辑都需下架
        $form = new MchSettingForm();
        $setting = $form->search();
        if ($setting['is_goods_audit'] == 1) {
            $this->goods->status = 0;
            $res = $this->goods->save();
            if (!$res) {
                throw new \Exception($this->goods);
            }
            $mchGoods->status = 0;
            $mchGoods->remark = '';
        }

        $mchGoods->sort = $this->sort;
        if (!$mchGoods->save()) {
            throw new \Exception('商品保存失败：' . $this->responseErrorMsg($mchGoods));
        }

        return $mchGoods;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-16
     * @Time: 10:38
     * @Note:设置商品分类
     * @throws \Exception
     */
    protected function setGoodsCat()
    {
        if (!is_array($this->cats) || !is_array($this->mchCats)) {
            throw new \Exception('分类必须为数组');
        }
        $goodsCatRelationList = $this->goodsWarehouse->goodsCatRelation;

        $catIdList = array_column($goodsCatRelationList, 'cat_id');
        $cats = array_merge($this->cats, $this->mchCats);
        $catIdListDiff = array_diff($catIdList, $cats);
        if (!$catIdList) {
            $catsDiff = $cats;
        } else {
            $catsDiff = array_diff($cats, $catIdList);
        }
        if (count($catIdListDiff) > 0) {
            foreach ($catIdListDiff as $key => $value) {
                $goodsCatRelation = $goodsCatRelationList[$key];
                $goodsCatRelation->is_delete = 1;
                $goodsCatRelation->save();
            }
        }
        if (count($catsDiff) > 0) {
            foreach ($catsDiff as $item) {
                $goodsCatRelation = new GoodsCatRelation();
                $goodsCatRelation->cat_id = isset($item['value']) ? $item['value'] : $item;
                $goodsCatRelation->goods_warehouse_id = $this->goodsWarehouse->id;
                $goodsCatRelation->save();
            }
        }
    }
}
