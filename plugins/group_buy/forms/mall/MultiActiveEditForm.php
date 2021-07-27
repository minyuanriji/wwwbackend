<?php
/**
 * xuyaoxiang
 * 保存开团记录 多个表
 * 2020/09/01
 */

namespace app\plugins\group_buy\forms\mall;

use app\plugins\group_buy\event\ActiveEvent;
use Yii;
use app\plugins\group_buy\forms\common\ActiveEditForm;
use app\plugins\group_buy\forms\common\ActiveItemEditForm;
use app\plugins\group_buy\models\PluginGroupBuyGoods;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\group_buy\models\PluginGroupBuyActive;
use app\plugins\group_buy\services\GroupBuyGoodsAttrServices;
use app\plugins\group_buy\jobs\ActiveEndJob;

class MultiActiveEditForm extends BaseModel
{
    public $active_item = [];
    public $active = [];
    public $mall_id;
    private $groupBuyGoods;

    public function rules()
    {
        return [
            [['active_item', 'active'], 'safe']
        ];
    }

    public function init()
    {
        $this->mall_id = Yii::$app->mall->id;
    }

    /**
     * 保存开团记录，拼团订单
     * @return array
     */
    public function save()
    {
        //事务开始
        $t = \Yii::$app->db->beginTransaction();
        try {
            /**
             * 如果没有传入开团id,视为发起开团.
             */
            if (!isset($this->active['id'])) {
                $return = $this->addActive();

                if ($return['code'] > 0) {
                    throw new \Exception($return['msg'], $return['code']);
                }

                $this->active_item['is_creator'] = 1; //团长
                $this->active_item['active_id']  = $return['data']['id']; //开团id

                //延时队列，开团到时自动结束
                $seconds = $this->groupBuyGoods['vaild_time'] * 60;

                //调试的
                if (YII_ENV == 'dev' or YII_ENV == 'test') {
                    $seconds = 300;
                }

                Yii::$app->queue->delay($seconds)->push(new ActiveEndJob(
                    ['active_id' => $this->active_item['active_id'], 'mall' => \Yii::$app->mall]
                ));
            } else {
                $return = $this->editActive();
                if ($return['code'] > 0) {
                    throw new \Exception($return['msg'], $return['code']);
                }
            }

            $return = $this->saveActiveItem();

            if ($return['code'] > 0) {
                throw new \Exception($return['msg'], $return['code']);
            }

            //减库存
            $GroupBuyGoodsAttrServices = new GroupBuyGoodsAttrServices();

            $return                    = $GroupBuyGoodsAttrServices->updateStock(1, 'sub', $this->active_item['attr_id']);

            if ($return['code'] > 0) {
                throw new \Exception($return['msg'], $return['code']);
            }
            $t->commit();

        } catch (\Exception $e) {

            $t->rollBack();

            return $this->returnApiResultData(100, $e->getMessage());
        }

        return $this->returnApiResultData(0, "保存成功");
    }

    public function editActive()
    {
        $model = PluginGroupBuyActive::find()->where(['id' => $this->active['id'], 'status' => 1])->one();

        if (!$model) {
            return $this->returnApiResultData(95, "没有找到开团记录或者拼团已满,id:" . $this->active['id']);
        }

        $this->active_item['active_id']  = $model->id; //开团id
        $this->active_item['is_creator'] = 0; //团长
        $model->actual_people            += 1;

        //正常成团
        if ($model->actual_people == $model->people) {
            $model->status = 2;
        }

        //虚拟成团
        if ($model->is_virtual == 1 and $model->virtual_people == $model->actual_people) {
            $model->status = 2;
        }

        if (!$model->save()) {
            return $this->returnApiResultData(99, $this->responseErrorMsg($model));
        }

        if(2 == $model->status){
            $active = PluginGroupBuyActive::findOne($model->id);
            $event                          = new ActiveEvent();
            $event->plugin_group_buy_active = $active;
            \Yii::$app->trigger(PluginGroupBuyActive::EVENT_GROUP_BUY_ACTIVE_SUCCESS, $event);
        }

        return $this->returnApiResultData(0, "保存成功");
    }

    /**
     * 获取拼团商品
     * @param false $toArray
     * @return PluginGroupBuyGoods|array
     */
    public function getGroupBuyGoods($toArray = true)
    {
        $return = PluginGroupBuyGoods::findOne(['goods_id' => $this->active['goods_id'], 'mall_id' => $this->mall_id, 'deleted_at' => 0]);

        if (!$return) {
            return [];
        }

        if ($toArray) {
            return $return->toArray();
        } else {
            return $return;
        }
    }

    /**
     * 新增开团记录
     * @return array
     */
    public function addActive()
    {
        $groupBuyGoods = $this->getGroupBuyGoods();

        if (empty($groupBuyGoods)) {
            return $this->returnApiResultData(99, "找不到拼团商品");
        }

        $this->groupBuyGoods = $groupBuyGoods;

        $activeEditForm                = new ActiveEditForm();
        $activeEditForm->attributes    = $groupBuyGoods;
        $activeEditForm->start_at      = date('Y-m-d H:i:s', time());
        $activeEditForm->actual_people = 1;
        $activeEditForm->group_buy_id  = $groupBuyGoods['id'];
        $activeEditForm->creator_id    = $this->active_item['user_id'];
        $activeEditForm->end_at        = $this->getEndTime($groupBuyGoods['vaild_time']);
        $activeEditForm->status        = 1;
        return $activeEditForm->save();
    }

    /**
     * 保存拼团订单表
     * @return array
     */
    public function saveActiveItem()
    {
        $activeItemEditForm = new ActiveItemEditForm();

        $activeItemEditForm->attributes = $this->active_item;

        return $activeItemEditForm->save();
    }

    /**
     * 根据有效时间(分钟数)获取结束时间
     * @param $start_at
     * @param $vaild_time
     * @return false|string
     */
    public function getEndTime($vaild_time)
    {
        $vaild_time_unix = $vaild_time * 60;
        $end_at          = date('Y-m-d H:i:s', time() + $vaild_time_unix);

        return $end_at;
    }
}