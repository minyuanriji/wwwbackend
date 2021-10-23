<?php

namespace app\plugins\alibaba\helpers;

use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsWarn;

class AliGoodsHelper{

    /**
     * 设置商品异常记录
     * @param AlibabaDistributionGoodsList $goods
     * @param string $remark
     * @throws \Exception
     */
    public static function setWarn(AlibabaDistributionGoodsList $goods, $remark = ""){

        $goodsWarn = AlibabaDistributionGoodsWarn::findOne([
            "mall_id"  => $goods->mall_id,
            "goods_id" => $goods->id
        ]);
        if(!$goodsWarn){
            $goodsWarn = new AlibabaDistributionGoodsWarn([
                "mall_id"    => $goods->mall_id,
                "goods_id"   => $goods->id,
                "created_at" => time()
            ]);
        }
        $goodsWarn->updated_at = time();
        $goodsWarn->flag       = 0;
        $goodsWarn->remark     = $remark;
        if(!$goodsWarn->save()){
            throw new \Exception(json_encode($goodsWarn->getErrors()));
        }

    }

}