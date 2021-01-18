<?php
/**
 * xuyaoxiang
 * 2020/09/08
 * 拼单编辑form
 */
namespace app\plugins\group_buy\forms\common;

use app\models\BaseModel;
use app\plugins\group_buy\models\PluginGroupBuyActiveItem;

class ActiveItemEditForm extends BaseModel
{
    public $id;
    public $active_id;
    public $user_id;
    public $order_id;
    public $is_creator;
    public $attr_id;
    public $group_buy_price;

    public function rules()
    {
        return [
            [['id', 'active_id', 'order_id', 'user_id', 'is_creator', 'attr_id'], 'integer'],
            [['group_buy_price'], 'number'],
        ];
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

        $one = $this->checkRepeatBuyingSql($this->active_id,$this->user_id);

        if ($one) {
            return $this->returnApiResultData(102, "当前用户已经参与过该拼团");
        }

        $model             = new PluginGroupBuyActiveItem();
        $model->created_at = time();
        $model->mall_id    = \Yii::$app->mall->id;

        $model->attributes = $this->attributes;

        if (!$model->save()) {
            return $this->returnApiResultData(101, $this->responseErrorMsg($model));
        }

        return $this->returnApiResultData(0, "保存成功", $model);
    }

    public function checkRepeatBuyingSql($active_id,$user_id)
    {
        return $one = PluginGroupBuyActiveItem::find()
            ->where(['active_id' => $active_id, 'user_id' => $user_id, 'deleted_at' => '0'])
            ->one();
    }
}