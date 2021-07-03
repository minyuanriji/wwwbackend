<?php
/**
 * 公共商品form
 * xuyaoxiang
 * 2020/08/27
 */
namespace app\plugins\group_buy\forms\common;

use app\forms\common\goods\CommonGoods as ParentCommonGoods;
use app\plugins\group_buy\forms\mall\GroupBuyGoodsAttrQueryForm;

class CommonGoodsForm extends ParentCommonGoods
{
    public function getGoodsDetail($id)
    {
        $detail = parent::getGoodsDetail($id);

        //获取拼团价格
        foreach ($detail['attr'] as $key => $value) {
            $detail['attr'][$key]['group_buy_price'] = GroupBuyGoodsAttrQueryForm::getGroupBuyPriceByAttrId($value['id']);
        }

        return $detail;
    }
}