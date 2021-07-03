<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 开团查询form
 * Author: xuyaoxiang
 * Date: 2020/9/4
 * Time: 14:19
 */

namespace app\plugins\group_buy\forms\mall;

use app\plugins\group_buy\event\ActiveEvent;
use app\plugins\group_buy\forms\common\ActiveQueryCommonForm;
use app\plugins\group_buy\filters\User;
use app\plugins\group_buy\models\PluginGroupBuyActive;
use app\plugins\group_buy\services\TimeServices;

class ActiveQueryForm extends ActiveQueryCommonForm
{
    /**
     * 列表
     * @return array
     */
    public function queryList()
    {
        $all = $this->returnAll();

        $all = $this->transAll($all);

        return $this->returnData($all);
    }

    /**
     * 详情
     * @return array
     */
    public function show()
    {
        $item = $this->queryShow();
        $item = $this->transOne($item);

        return $this->returnApiResultData(0, "", $item);
    }

    /**
     * @param $item
     * @return mixed
     */
    private function transOne($item){
        $item['goods'] = GroupBuyGoodsQueryForm::filterGoods($item['goods_id']);
        $item['creator'] = User::filterItem($item['creator']);

        return $item;
    }

    /**
     * @param $all
     * @return mixed
     */
    public function transAll($all)
    {
        foreach ($all as $key => $value) {
            //获取剩余时间
            $ts                          = new TimeServices();

            $all[$key]['remaining_time'] = $ts->getReaminingTimeMin($value['end_at']);

            //处理普通商品字段
            $all[$key]['goods']   = GroupBuyGoodsQueryForm::filterGoods($value['goods_id']);
            //处理团长用户字段
            $all[$key]['creator'] = User::filterItem($value['creator']);
        }

        return $all;
    }

    public function manualEnd()
    {
        $this->scenario = 'show';

        if (!$this->validate()) {
            return $this->returnApiResultData(97, $this->responseErrorMsg($this));
        }

        $this->as_array = false;
        $item           = $this->queryShow();

        if (!$item) {
            return $this->returnApiResultData(98, "拼团不存在");
        }

        if ($item->is_virtual != 1) {
            return $this->returnApiResultData(101, "不是虚拟成团不能手动结束");
        }

        $item->status    = 2;
        $item->end_at    = date('Y-m-d H:i:s', time());
        $item->is_manual = 1;

        if (!$item->save()) {
            return $this->returnApiResultData(99, "拼团保存失败");
        }

        $event                          = new ActiveEvent();
        $event->plugin_group_buy_active = $item;
        \Yii::$app->trigger(PluginGroupBuyActive::EVENT_GROUP_BUY_ACTIVE_SUCCESS, $event);

        return $this->returnApiResultData(0, "结束拼团成功", $item);
    }
}