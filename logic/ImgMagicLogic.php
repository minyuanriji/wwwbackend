<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 图片魔方处理
 * Author: zal
 * Date: 2020-04-13
 * Time: 14:36
 */

namespace app\logic;

use app\models\ImgMagic;

class ImgMagicLogic
{
    /**
     * 获取所有图片魔方
     * @return array
     */
    public static function getAll()
    {
        $list = ImgMagic::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->asArray()->all();
        return [
            'list' => $list,
        ];
    }
}
