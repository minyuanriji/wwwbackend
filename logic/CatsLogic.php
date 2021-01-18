<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 公共分类
 * Author: zal
 * Date: 2020-04-18
 * Time: 10:16
 */

namespace app\logic;


use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;

class CatsLogic
{
    public $page;
    public $sign;

    /**
     * 获取所有分类(包括二级、三级)
     */
    public static function getAllCats()
    {
        $list = GoodsCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0,
            'parent_id' => 0
        ])->with('child.child')->asArray()->all();

        return $list;
    }

    public function getCatGoods($id)
    {
        if ($id) {
            $catIds = [$id];
            $goodsCats_2 = GoodsCats::find()->where([
                'parent_id' => $id,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'is_delete' => 0
            ])->asArray()->select('id')->all();

            if ($goodsCats_2) {
                foreach ($goodsCats_2 as $item) {
                    $catIds[] = $item['id'];
                    $goodsCats_3 = GoodsCats::find()->where([
                        'parent_id' => $item['id'],
                        'mall_id' => \Yii::$app->mall->id,
                        'mch_id' => \Yii::$app->admin->identity->mch_id,
                        'is_delete' => 0
                    ])->asArray()->all();

                    foreach ($goodsCats_3 as $item2) {
                        $catIds[] = $item2['id'];
                    }
                }
            }
        } else {
            $goodsCats = GoodsCats::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
            ])->select('id')->asArray()->all();

            $catIds = [];
            foreach ($goodsCats as $goodsCat) {
                $catIds[] = $goodsCat['id'];
            }
        }
        $goodsIds = GoodsCatRelation::find()->alias('gc')->where([
            'gc.cat_id' => $catIds,
            'gc.is_delete' => 0
        ])->select('goods_id');

        $list = Goods::find()->alias('g')->where([
            'g.sign' => $this->sign,
            'g.is_delete' => 0,
            'g.mall_id' => \Yii::$app->mall->id,
            'g.status' => 1,
            'g.mch_id' => \Yii::$app->admin->identity->mch_id,
        ])->andWhere(['id' => $goodsIds])
            ->with('attr')
            ->groupBy('id')
            ->page($pagination, 10, $this->page)->asArray()->all();

        return [
            'list' => $list,
            'pagination' => $pagination,
        ];
    }
}
