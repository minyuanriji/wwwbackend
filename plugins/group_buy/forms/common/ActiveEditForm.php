<?php
/**
 * xuyaoxiang
 * 开团记录编辑form
 * 2020/08/31
 */

namespace app\plugins\group_buy\forms\common;

use app\models\BaseModel;
use app\plugins\group_buy\models\PluginGroupBuyActive;

/**
 * Class ActiveEditForm
 * @package app\plugins\group_buy\forms\common
 */
class ActiveEditForm extends BaseModel
{
    public $id;
    public $goods_id;
    public $people;
    public $virtual_people;
    public $actual_people;
    public $creator_id;
    public $start_at;
    public $end_at;
    public $status;
    public $is_virtual;
    public $mall_id;
    public $group_buy_id;
    public $is_send;

    public function rules()
    {
        return [
            [['goods_id', 'people', 'virtual_people', 'actual_people', 'creator_id', 'status', 'is_virtual', 'mall_id', 'group_buy_id','is_send'], 'integer'],
            [['start_at', 'end_at'], 'safe'],
            [['status', 'virtual_people', 'actual_people', 'is_virtual'], 'default', 'value' => 0]
        ];
    }

    /**
     * @param null $active_id
     * @return array
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
        }

        if ($this->id) {
            $model = PluginGroupBuyActive::findOne(['id'  => $this->id,
                                                    'mall_id'    => $this->mall_id,
                                                    'deleted_at' => 0]);
            if (!$model) {
                return $this->returnApiResultData(98, $this->responseErrorMsg("找不到开团记录"));
            }

            $model->updated_at = time();
        } else {
            $model             = new PluginGroupBuyActive();
            $model->created_at = time();
        }

        $model->attributes = $this->attributes;

        if (!$model->save()) {
            return $this->returnApiResultData(1, $this->responseErrorMsg($model));
        }

        return $this->returnApiResultData(0, "保存成功", $model);
    }

    /**
     * @param $active_id
     * @return array
     */
    public function del()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
        }

        $model = PluginGroupBuyActive::findOne(['id'         => $this->id,
                                                'mall_id'    => $this->mall_id,
                                                'deleted_at' => 0]);

        $model->deleted_at = time();

        if (!$model->save()) {
            return $this->returnApiResultData(1, $this->responseErrorMsg($model));
        }

        return $this->returnApiResultData(0, "删除成功");
    }
}