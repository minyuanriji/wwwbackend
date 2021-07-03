<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-27
 * Time: 16:05
 */

namespace app\forms\api\index\home;


use app\forms\common\CommonAppConfig;
use app\forms\common\coupon\CouponCommon;
use app\forms\common\coupon\CouponListCommon;
use app\forms\common\goods\GoodsList;
use app\models\Banner;
use app\models\BannerRelation;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\ImgMagic;
use app\models\NavIcon;

class HomePageDefaultData
{

    public static function getHomeNavs()
    {
        $navs = NavIcon::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'status' => 1,
            'is_delete' => 0,
        ])->orderBy(['sort' => SORT_ASC])->asArray()->all();
        $newList = [];
        foreach ($navs as $nav) {
            $arr = [
                'id' => $nav['id'],
                'icon_url' => $nav['icon_url'],
                'link_url' => $nav['url'],
                'name' => $nav['name'],
                'open_type' => $nav['open_type'],
                'params' => $nav['params'] ? json_decode($nav['params'], true) : [],
            ];
            $newList[] = $arr;
        }
        return $newList;
    }

    public static function getHomeBanners()
    {
        $bannerIds = Banner::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->select('id');
        $query = BannerRelation::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        $banners = $query->andWhere(['banner_id' => $bannerIds])
            ->with('banner')
            ->orderBy('id ASC')
            ->asArray()
            ->all();
        $banners = array_map(function ($item) {
            return $item['banner'];
        }, $banners);

        $newList = [];
        foreach ($banners as $banner) {
            $arr = [
                'id' => $banner['id'],
                'title' => $banner['title'],
                'params' => $banner['params'] ? json_decode($banner['params'], true) : '',
                'open_type' => $banner['open_type'],
                'pic_url' => $banner['pic_url'],
                'page_url' => $banner['page_url'],
            ];
            $newList[] = $arr;
        }
        return $newList;
    }

    public static function getHomeSearch()
    {


    }


    public static function getHomeCouponList()
    {

        $common = new CouponListCommon();
        $common->user = \Yii::$app->user->identity;
        $list = $common->getList();
        return $list;
    }

    public static function getHomeCatGoodsList($catIds = [], $isAllCat = false)
    {
        $query = GoodsCats::find()->where([
            'is_delete' => 0,
            'status' => 1,
            'is_show' => 1,
            'mch_id' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'parent_id' => 0,
        ])->with(['child' => function ($query) {
            $query->andWhere(['status' => 1, 'is_show' => 1])
                ->with(['child' => function ($query) {
                    $query->andWhere(['status' => 1, 'is_show' => 1])->orderBy('sort ASC');
                }])->orderBy('sort ASC');
        }]);
        if ($isAllCat) {
            $list = $query->orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC])->all();
        } else {
            $list = $query->andWhere(['id' => $catIds])->all();
        }
        $catList = self::getCatList($list);
        $form = new GoodsList();
        $newList = [];
        /** @var GoodsCats[] $list */
        foreach ($list as $item) {
            $goodsWarehouseId = GoodsCatRelation::find()->where(['cat_id' => $catList[$item->id], 'is_delete' => 0])
                ->select('goods_warehouse_id');
            /* @var Goods[] $goodsList */
            $goodsList = Goods::find()->with(['goodsWarehouse.goodsCatRelation', 'mallGoods', 'attr'])
                ->where([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'sign' => ['mch', ''], 'status' => 1,
                    'goods_warehouse_id' => $goodsWarehouseId
                ])->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
                ->all();
            $arr = [];
            $arr['key'] = 'cat';
            $arr['name'] = $item->name;
            $arr['relation_id'] = $item->id;
            $arr['cat_pic_url'] = $item->pic_url;
            $newGoods = [];
            foreach ($goodsList as $gItem) {
                $newGoods[] = $form->getGoodsData($gItem);
            }
            $arr['goods'] = $newGoods;
            $newList[] = $arr;
        }
        return $newList;
    }

    static function getCatList($list, $index = 1)
    {
        $res = [];
        if (is_array($list)) {
            foreach ($list as $item) {
                $result = [];
                $result[] = intval($item->id);
                if (isset($item->child)) {
                    $index++;
                    $result = array_merge($result, self::getCatList($item->child, $index));
                    $index--;
                }
                if ($index == 1) {
                    $res[$item->id] = $result;
                } else {
                    $res = array_merge($res, $result);
                }
            }
        }
        return $res;
    }


    //$catIds, $isAllCat

    public static function getNewCatGoods($data, $catGoods)
    {
        if ($data['relation_id'] == 0) {
            return $catGoods;
        } else {
            foreach ($catGoods as $catGood) {
                if ($catGood['relation_id'] == $data['relation_id']) {
                    return [$catGood];
                }
            }
        }

        return [];
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-28
     * @Time: 19:48
     * @Note:获取魔方板块
     * @param $blockIds
     * @return array
     */
    public static function getHomeBlocks($blockIds)
    {
        $blocks = ImgMagic::find()->where([
            'id' => $blockIds,
            'is_delete' => 0
        ])->asArray()->all();
        $newList = [];
        foreach ($blocks as $block) {
            $block['value'] = $other = \Yii::$app->serializer->decode($block['value']);
            // 样式一
            if (count($block['value']) == 1 && $block['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 0;
                $block['value'] = [
                    [
                        'width' => '100%',
                        'height' => 'auto',
                        'left' => 0,
                        'top' => 0,
                    ]
                ];
            }

            if (count($block['value']) == 2 && $block['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 1;
                $block['value'] = [
                    [
                        'width' => (300 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => 0
                    ],
                ];
            }
            if (count($block['value']) == 3 && $block['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 2;
                $block['value'] = [
                    [
                        'width' => (300 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => 0
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => '50%'
                    ],
                ];
            }
            if (count($block['value']) == 4 && $block['type'] == 1) {
                $block['style'] = 360;
                $block['status'] = 3;
                $block['value'] = [
                    [
                        'width' => (300 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (450 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => 0
                    ],
                    [
                        'width' => (225 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (300 * 100 / 750) . '%',
                        'top' => '50%'
                    ],
                    [
                        'width' => (225 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (525 * 100 / 750) . '%',
                        'top' => '50%'
                    ],
                ];
            }

            // 样式二
            if (count($block['value']) == 2 && $block['type'] == 2) {
                $block['style'] = 240;
                $block['status'] = 4;
                $block['value'] = [
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (375 * 100 / 750) . '%',
                        'top' => 0
                    ],
                ];
            }
            if (count($block['value']) == 3 && $block['type'] == 2) {
                $block['style'] = 240;
                $block['status'] = 5;
                $block['value'] = [
                    [
                        'width' => (250 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (250 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (250 * 100 / 750) . '%',
                        'top' => 0
                    ],
                    [
                        'width' => (250 * 100 / 750) . '%',
                        'height' => '100%',
                        'left' => (500 * 100 / 750) . '%',
                        'top' => 0
                    ],
                ];
            }
            if (count($block['value']) == 4 && $block['type'] == 2) {
                $block['style'] = 187.5;
                $block['status'] = 6;
                $block['value'] = [
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => '25%',
                        'top' => 0
                    ],
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => '50%',
                        'top' => 0
                    ],
                    [
                        'width' => '25%',
                        'height' => '100%',
                        'left' => '75%',
                        'top' => 0
                    ],
                ];
            }

            // 样式三
            if (count($block['value']) == 4 && $block['type'] == 3) {
                $block['style'] = 372;
                $block['status'] = 7;
                $block['value'] = [
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => 0,
                        'top' => 0
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (375 * 100 / 750) . '%',
                        'top' => 0
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => 0,
                        'top' => '50%',
                    ],
                    [
                        'width' => (375 * 100 / 750) . '%',
                        'height' => '50%',
                        'left' => (375 * 100 / 750) . '%',
                        'top' => '50%',
                    ],
                ];
            }
            foreach ($block['value'] as $key => $item) {
                if (isset($other[$key])) {
                    $block['value'][$key] = array_merge($block['value'][$key], $other[$key]);
                }
            }

            $newList[] = $block;
        }

        return $newList;
    }


}