<?php
/**
 * xuyaoxiang
 * 2020/09/08
 */
namespace app\plugins\group_buy\forms\mall;

use app\models\BaseModel;
use app\plugins\group_buy\models\PluginGroupBuyGoodsAttr;

class GroupBuyGoodsAttrQueryForm extends BaseModel
{
    /**
     * 根据商品规格id获取对应拼团价格
     * @param $attr_id
     * @return int|mixed|null
     */
    static public function getGroupBuyPriceByAttrId($attr_id)
    {
        $result = PluginGroupBuyGoodsAttr::findOne(['attr_id' => $attr_id]);
        if ($result) {
            return $result->group_buy_price;
        }
        return 0;
    }
}