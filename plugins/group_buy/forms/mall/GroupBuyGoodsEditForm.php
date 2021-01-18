<?php
/**
 * xuyaoxiang
 * 2020/09/08
 * 拼团商品编辑
 */

namespace app\plugins\group_buy\forms\mall;

use app\models\BaseModel;
use app\plugins\group_buy\models\PluginGroupBuyGoods;

class GroupBuyGoodsEditForm extends BaseModel
{
    public $goods_id;
    public $start_at;
    public $vaild_time;
    public $people;
    public $virtual_people;
    public $is_virtual;
    public $status;
    public $mall_id;
    public $send_balance;
    public $send_score;
    public $goods_stock;

    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'people', 'vaild_time', 'status', 'mall_id', 'is_virtual', 'virtual_people','send_score','send_balance','goods_stock'], 'integer'],
            ['start_at', 'safe']
        ];
    }

    public function init()
    {
        $this->mall_id = \Yii::$app->mall->id;
    }

    /**
     * 保存数据
     * @return array
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
        }

      //  $model = PluginGroupBuyGoods::findOne(['goods_id' => $this->goods_id, 'deleted_at' => 0]);

        //if (!$model) {
            $model = new PluginGroupBuyGoods();
        //}

        $model->attributes = $this->attributes;
        $model->status     = 0;

        if (!$model->save()) {
            return $this->returnApiResultData(99, $this->responseErrorMsg($model));
        }

        return $this->returnApiResultData(0, "保存成功",$model->toArray());
    }

    /**
     * 删除数据
     * 拼团商品表:plugin_group_buy_goods
     * @param $goods_id
     * @return array
     */
    public function del()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
        }

        $PluginGroupBuyGoods = new PluginGroupBuyGoods();
        $model               = $PluginGroupBuyGoods->getGroupBuyGoodsOne($this->goods_id, $this->mall_id);

        if (!$model) {
            return $this->returnApiResultData(97, "拼团商品不存在");
        }

        $model->deleted_at = time();

        if (!$model->save()) {

            return $this->returnApiResultData(2, $this->responseErrorMsg($model));
        }

        return $this->returnApiResultData(0, "删除拼团商品成功");
    }
}