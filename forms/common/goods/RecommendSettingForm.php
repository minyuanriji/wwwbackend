<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-25
 * Time: 10:13
 */


namespace app\forms\common\goods;

use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class RecommendSettingForm extends BaseModel
{
    public function getSetting()
    {
        $setting = OptionLogic::get(
            Option::NAME_RECOMMEND_SETTING,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            $this->getDefault()
        );

        foreach ($setting as $key => &$item) {
            if (isset($item['is_recommend_status'])) {
                $item['is_recommend_status'] = (int)$item['is_recommend_status'];
            }
            if (isset($item['is_custom'])) {
                $item['is_custom'] = (int)$item['is_custom'];
            }
        }

        return $setting;
    }

    public function getDefault()
    {
        return [
            'goods' => [
                'is_recommend_status' => 1,
                'goods_num' => 6
            ],
            'order_pay' => [
                'is_recommend_status' => 1,
                'is_custom' => 0,
                'goods_list' => []
            ],
            'order_comment' => [
                'is_recommend_status' => 1,
                'is_custom' => 0,
                'goods_list' => []
            ],
        ];
    }
}