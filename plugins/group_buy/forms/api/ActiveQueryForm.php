<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/4
 * Time: 14:19
 */

namespace app\plugins\group_buy\forms\api;

use app\plugins\group_buy\filters\User;
use app\plugins\group_buy\forms\common\ActiveQueryCommonForm;
use app\plugins\group_buy\services\TimeServices;
use app\plugins\group_buy\forms\mall\ActiveItemQueryForm;
use app\plugins\group_buy\filters\Goods;
use app\plugins\group_buy\services\GroupBuyGoodsServices;
use app\plugins\group_buy\forms\api\GoodsForm;

class ActiveQueryForm extends ActiveQueryCommonForm
{
    //当前商品总拼团多人
    private $total_actual_people = 0;

    /**
     * 开团列表
     * @return array
     */
    public function queryList()
    {
        $this->status = 1;

        $list = $this->returnAll();

        $list = $this->transAll($list);

        $this->pagination = $this->getPaginationInfo($this->pagination);

        return $this->selfReturnData($list, $this->total_actual_people);
    }

    /**
     * 开团详情
     * @return array
     */
    public function show()
    {
        //获取详情场景,id必须
        $this->scenario = 'show';

        //参数验证
        if (!$this->validate()) {
            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
        }

        //获取原始model数据
        $data = $this->queryShow();

        //加工数据
        $data = $this->transShow($data);

        return $this->returnApiResultData(0, "", $data);
    }

    public function transShow($data)
    {
        $goods = new Goods();
        //获取和过滤goods数据
        $data['goods'] = $goods->filterItemForGroupBuy($data['goods_id']);

        //剩余时间
        $ts                      = new TimeServices();
        $data['remaining_time']  = $ts->getReaminingTime($data['end_at']);

        //显示最低规格拼团价为开团得显示拼团价
        $GoodsForm   = new GoodsForm();
        $data['group_buy_price'] = $GoodsForm->getMinGroupBuyPrice($data['goods_id']);

        $data['activeItems'] = array_map(array($this, "transActiveItem"), $data['activeItems']);

        return $data;
    }

    /**
     * 只返回拼单用户信息
     * @param $item
     * @return array
     */
    public function transActiveItem($item)
    {
        $user = new User();

        return [
            'is_creator' => $item['is_creator'],
            'user' => $user->getItemByUserId($item['user_id']),
        ];
    }

    /**
     * 自定义返回格式
     * @param $all
     * @param $total_actual_people
     * @return array
     */
    protected function selfReturnData($all, $total_actual_people)
    {
        return $this->returnApiResultData(0, "", [
            'list'                => $all,
            'total_actual_people' => $total_actual_people,
            'pagination'          => $this->pagination
        ]);
    }

    /**
     * 循环获取
     * @param $all
     * @return mixed
     */
    public function transAll($all)
    {
        foreach ($all as $key => $value) {
            $all[$key] = $this->transOne($value);
        }

        return $all;
    }

    /**
     * @param $item
     * @return mixed
     */
    public function transOne($item)
    {
        $this->total_actual_people += $item['actual_people'];
        //获取剩余时间
        $ts                     = new TimeServices();
        $item['remaining_time'] = $ts->getReaminingTime($item['end_at']);
        //删除商品数据
        unset($item['goods']);
        //删除团长数据
        unset($item['creator']);
        //删除拼团商品数据
        unset($item['groupBuyGoods']);

        //获取拼团用户数据
        $form                     = new ActiveItemQueryForm();
        $form->limit              = 3;
        $form->active_id          = $item['id'];
        $return                   = $form->queryListForGoodsDetail();
        $item['active_item_list'] = $return['data']['list'];

        return $item;
    }
}