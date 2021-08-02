<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金公共处理类
 * Author: zal
 * Date: 2020-05-26
 * Time: 10:30
 */

namespace app\plugins\distribution\forms\common;

use app\models\BaseModel;
use app\plugins\distribution\models\DistributionCommissionLog;
use app\plugins\distribution\models\DistributionGoods;
use app\plugins\distribution\models\DistributionGoodsDetail;

class DistributionCommissionCommon extends BaseModel
{
    /**
     * 新增佣金记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public static function addCommissonLog($data){
        $distribution_order_id = $data["distribution_order_id"];
        $user_id = $data["user_id"];
        $mall_id = $data["mall_id"];
        $model = new DistributionCommissionLog();
        $model->distribution_order_id = $distribution_order_id;
        $model->mall_id = $mall_id;
        $model->user_id = $user_id;
        $model->order_id = $data["order_id"];
        $model->type = $data["type"];
        $model->money = $data["money"];
        $model->commission = $data["commission"];
        $model->desc = $data["desc"];
        $model->level = $data["level"];
        $result = $model->save();
        if(!$result){
            throw new \Exception((new BaseModel())->responseErrorMsg($model));
        }
        return true;
    }
}