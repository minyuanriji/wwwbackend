<?php
/**
 * xuyaoxiang
 * 2020/09/02
 * 保存拼团商品多个表
 */
namespace app\plugins\group_buy\forms\mall;

use app\plugins\sign_in\forms\BaseModel;
use app\plugins\group_buy\services\GroupBuyGoodsServices;

class MultiGroupBuyGoodsEditForm extends BaseModel
{
    public $goods_id; //商品id
    public $mall_id;
    public $group_buy_goods = []; //拼团商品
    public $attrGroups = []; //商品规格
    public $form = []; //普通商品

    public function rules()
    {
        return [
            [['goods_id','mall_id'], 'integer'],
            [['group_buy_goods', 'attrGroups','form'], 'safe']
        ];
    }

    public function init()
    {
        $this->mall_id = \Yii::$app->mall->id;
    }

    /**
     * 保存数据
     * 商品表:goods
     * 拼团商品表:plugin_group_buy_goods
     * 商品规格价格表:goods_attr
     * 拼团商品规格价格表:plugin_group_buy_goods_attr
     *
     * @return array
     */
    public function save()
    {
        //数据检验
        if (!isset($this->group_buy_goods['goods_id'])) {
            return $this->returnApiResultData(98, "goods_id不存在");
        }

        $this->goods_id = $this->group_buy_goods['goods_id'];

        //事务开始
        $t = \Yii::$app->db->beginTransaction();

        try {
            //保存goods表
//            $goodsEditForm           = new GoodsEditForm();
//            $goodsEditForm->goods_id = $this->goods_id;
//            $goodsEditForm->mall_id  = $this->mall_id;
//            $returnSaveGoods         = $goodsEditForm->save();
//            if ($returnSaveGoods['code'] > 0) {
//                throw new \Exception($returnSaveGoods['msg'], $returnSaveGoods['code']);
//            }

            //保存拼团商品表
            $groupBuyGoodsEditForm           = new GroupBuyGoodsEditForm();
            $groupBuyGoodsEditForm->attributes = $this->group_buy_goods;
            $return_group_buy_goods = $groupBuyGoodsEditForm->save();
            if ($return_group_buy_goods['code'] > 0) {
                throw new \Exception($return_group_buy_goods['msg'], $return_group_buy_goods['code']);
            }

            //保存商品规格
            if (!isset($this->form['attr'])) {
                throw new \Exception("form attr不存在", 91);
            }
            $return = $this->saveGoodsAttr($this->form['attr']);
            if ($return['code'] > 0) {
                throw new \Exception($return['msg'], $return['code']);
            }

            $t->commit();

        } catch (\Exception $e) {

            $t->rollBack();

            return $this->returnApiResultData(100, $e->getMessage());
        }

        $GroupBuyGoodsServices = new GroupBuyGoodsServices();
        $GroupBuyGoodsServices->groupBuyGoodsQueue($return_group_buy_goods['data']);

        return $this->returnApiResultData(0, "保存成功");
    }

    /**
     * 保存数据
     * 商品规格表 goods_attr
     * 拼团商品规格价格表 plugin_group_buy_goods_attr
     * @param $attrGroups
     * @param $attr
     * @return array
     */
    public function saveGoodsAttr($attr)
    {
        $form             = new GroupBuyGoodsAttrEditForm();
        $form->attr       = $attr;

        return $form->save();
    }

    /**
     * 删除数据
     * 商品表:goods
     * 拼团商品表:plugin_group_buy_goods
     * @param $goods_id
     * @return array
     */
    public function del()
    {
        $t = \Yii::$app->db->beginTransaction();

        try {
            $goodsEditForm           = new GoodsEditForm();
            $goodsEditForm->goods_id = $this->goods_id;
            $goodsEditForm->mall_id  = $this->mall_id;
            $returnDelGoods          = $goodsEditForm->del();
            if ($returnDelGoods['code'] > 0) {
                throw new \Exception($returnDelGoods['msg'], $returnDelGoods['code']);
            }

            $groupBuyGoodsEditForm           = new GroupBuyGoodsEditForm();
            $groupBuyGoodsEditForm->goods_id = $this->goods_id;
            $groupBuyGoodsEditForm->mall_id  = $this->mall_id;
            $returnDelGroupBuyGoods          = $groupBuyGoodsEditForm->del();
            if ($returnDelGroupBuyGoods['code'] > 0) {
                throw new \Exception($returnDelGroupBuyGoods['msg'], $returnDelGroupBuyGoods['code']);
            }

            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();

            return $this->returnApiResultData($e->getCode(), $e->getMessage());
        }

        return $this->returnApiResultData(0, "删除成功");
    }
}