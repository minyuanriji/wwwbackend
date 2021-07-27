<?php
/**
 *
 */

namespace app\plugins\group_buy\forms\mall;

use app\models\BaseModel;
use app\models\GoodsAttr;
use app\plugins\group_buy\models\PluginGroupBuyGoodsAttr as GroupBuyGoodsAttr;

class GroupBuyGoodsAttrEditForm extends BaseModel
{
    public $attr;

    public function rules()
    {
        return [
            ['attr', 'safe'],
        ];
    }

    /**
     * 保存(商品规格主表)和(拼团商品规格价格表)
     * 商品规格主表:goods_attr
     * 拼团商品规格价格表:plugin_group_buy_goods_attr
     * @return array
     */
    public function save()
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            foreach ($this->attr as $key => $value) {
                //$model             = GoodsAttr::findOne(['id' => $value['id']]);
                //$model->attributes = $value;

                //if (!$model->save()) {
                //    throw new \Exception($this->responseErrorMsg($model), 92);
                //}

                //拼团价格
                if (!isset($value['group_buy_price'])) {
                    throw new \Exception("拼团价为必填项", 91);
                }

                $return = $this->saveGroupBuyGoodsAttr($value);
                if ($return['code'] > 0) {
                    throw new \Exception($return['msg'], $return['code']);
                }
            }

            $t->commit();

        } catch (\Exception $e) {

            $t->rollBack();

            return $this->returnApiResultData(91, $e->getMessage());
        }

        return $this->returnApiResultData(0, "保存成功");
    }

    /**
     * 保存数据
     * 拼团商品规格价格表:plugin_group_buy_goods_attr
     * @param $attr_item
     * @return array
     */
    public function saveGroupBuyGoodsAttr($attr_item)
    {
        $model = GroupBuyGoodsAttr::findOne(['attr_id' => $attr_item['id']]);

        if (!$model) {
            $model = new GroupBuyGoodsAttr();
        }

        $model->attr_id         = $attr_item['id'];
        $model->group_buy_price = $attr_item['group_buy_price'];
        $model->stock           = $attr_item['stock'];

        if (!$model->save()) {
            return $this->returnApiResultData(99, $this->responseErrorMsg($model));
        }

        return $this->returnApiResultData(0, "保存成功");
    }
}