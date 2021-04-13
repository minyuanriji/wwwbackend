<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 自定义商品显示价格
 * Author: xuyaoxiang
 * Date: 2020/10/28
 * Time: 11:23
 */

namespace app\services\Goods;


use app\services\MallSetting\MallSettingService;

class PriceDisplayService
{
    private $mall_id;

    const SETTING_KEY = "price_display"; //商品配置,自定义商品价格显示key
    const INTEGRAL_PRICE_SETTING_KEY = "integral_price_display"; //商品配置,红包券会员价显示key

    private $is_price_display = 1; //自定义商品价格显示前端是否显示
    private $is_integral_price_display = 0; //红包券会员价前端是否显示

    public function __construct($mall_id)
    {
        $this->mall_id = $mall_id;

        //获取配置
        $this->getIntegralPriceSetting();
        //若不显示会员价,自定义商品价格字样也不显示
        if (0 == $this->is_integral_price_display) {
            $this->is_price_display = 0;
        }
    }

    private function getPriceSetting()
    {
        $MallSettingService = new MallSettingService($this->mall_id);
        $data_price_display = $MallSettingService->getValueByKey(self::SETTING_KEY);

        if (isset($data_price_display['is_display'])) {
            $this->is_price_display = $data_price_display['is_display'];
        }
    }

    private function getIntegralPriceSetting()
    {
        $MallSettingService          = new MallSettingService($this->mall_id);
        $data_integral_price_display = $MallSettingService->getValueByKey(self::INTEGRAL_PRICE_SETTING_KEY);
        if (isset($data_integral_price_display['is_display'])) {
            $this->is_integral_price_display = $data_integral_price_display['is_display'];
        }

    }

    /**
     * @return array
     */
    function getPriceDisplay()
    {
        $GoodsPriceDisplayServices = new GoodsPriceDisplayServices();
        $params['page']            = 1;
        $params['limit']           = 10000;
        $params['mall_id']         = $this->mall_id;
        $data                      = $GoodsPriceDisplayServices->getList($params);

        if (empty($data['list'])) {
            return [];
        }

        $list         = $data['list'];
        $rebuild_list = array();
        foreach ($list as $value) {
            $rebuild_list[$value['id']] = $value['name'];
        }

        return $rebuild_list;
    }

    /**
     * @param  $goods_price_display
     * @return array
     */
    function getGoodsPriceDisplay($goods_price_display, $is_json = true)
    {
        if (empty($goods_price_display)) {
            return [];
        }

        $price_display_list = $this->getPriceDisplay();

        if ($is_json) {
            $goods_price_display = json_decode($goods_price_display, true);
        }

        foreach ($goods_price_display as $key => &$value) {
            //如果商品display_id跟$price_display_list匹配上，添加display_name。否则该商品display_id已经被删除。不输出给前端显示
            if (isset($price_display_list[$value['display_id']])) {
                $value['display_name'] = $price_display_list[$value['display_id']];
            } else {
                unset($goods_price_display[$key]);
            }
        }

        return $goods_price_display;
    }
}