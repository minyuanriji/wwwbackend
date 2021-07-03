<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商城拼单查询form
 * Author: xuyaoxiang
 * Date: 2020/9/4
 * Time: 19:35
 */

namespace app\plugins\group_buy\forms\mall;

use app\plugins\group_buy\filters\User;
use app\plugins\group_buy\filters\Order;
use app\plugins\group_buy\forms\common\ActiveItemQueryCommonForm;

class ActiveItemQueryForm extends ActiveItemQueryCommonForm
{
    /**普通列表
     * @return array
     */
    public function queryList()
    {
        $this->scenario = 'list';

        if (!$this->validate()) {
            return $this->returnApiResultData(99, $this->responseErrorMsg($this));
        }

        $all = $this->returnAll();

        $all = $this->transAll($all);

        return $this->returnData($all);
    }

    /**
     * 拼团商品详情拼团列表
     * @return array
     */
    public function queryListForGoodsDetail()
    {
        $this->scenario = 'list';

        if (!$this->validate()) {
            return $this->returnApiResultData(99, $this->responseErrorMsg($this));
        }

        $all = $this->returnAll();

        $all = $this->transAllForGoodsDetail($all);

        return $this->returnData($all);

    }

    /**
     * 数据转换
     * @param $all
     * @return mixed
     */
    private function transAll($all)
    {
        foreach ($all as $key => $value) {
            $all[$key]['created_at_format'] = date("Y-m-d H:i:s", $value['created_at']);
            $all[$key]['user']              = User::filterItem($value['user']);
            $all[$key]['order']             = Order::filterItem($value['order']);
        }

        return $all;
    }

    /**
     * 数据转换,拼团商品详情拼团列表
     * @param $all
     * @return array
     */
    private function transAllForGoodsDetail($all)
    {
        $temp=[];
        foreach ($all as $key => $value) {
            $temp[$key]['user'] = User::filterItem($value['user']);
        }

        return $temp;
    }
}