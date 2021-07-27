<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商品分类
 * Author: zal
 * Date: 2020-04-13
 * Time: 16:16
 */

namespace app\forms\common\goods;


use app\models\BaseModel;
use app\models\GoodsCats;

class GoodsCatsCommon extends BaseModel
{
    /**
     * 搜索商品分类
     * @param string $keyword
     * @param int $limit
     * @return array
     */
    public static function searchCat($keyword = '', $limit = 20)
    {
        $keyword = trim($keyword);

        $query = GoodsCats::find()->where([
            'AND',
            ['LIKE', 'name', $keyword],
            ['mall_id' => \Yii::$app->mall->id],
        ]);

        $list = $query->select('id,pic_url,name')->orderBy('name')->limit($limit)->asArray()->all();
        return [
            'list' => $list,
        ];
    }

    /**
     * 获取所有一级分类
     * @return array
     */
    public static function allParentCat()
    {
        $list = GoodsCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'parent_id' => 0,
            'is_delete' => 0,
            'mch_id' => 0
        ])->asArray()->all();

        return [
            'list' => $list
        ];
    }
}
