<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * diy插件-商城后台model-商品
 * Author: zal
 * Date: 2020-04-23
 * Time: 10:10
 */

namespace app\forms\mall\home_page;

use app\core\ApiCode;
use app\forms\common\goods\GoodsList;
use app\models\BaseModel;
use app\models\Goods;

class GoodsForm extends BaseModel
{
    public $page;
    public $limit;
    public $keyword;
    public $cat_id;
    public $mch_id;
    public $ids = [];

    public function rules()
    {
        return [
            [['page', 'mch_id', 'cat_id','limit'], 'integer'],
            [['keyword'], 'string'],
            [['keyword'], 'trim'],
            [['ids'], 'safe'],
            [['cat_id', 'mch_id'], 'default', 'value' => 0]
        ];
    }

    /**
     * 搜索
     * @return array
     */
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        if (!($this->sign == '' || $this->sign == 'mch')) {
            try {
                $plugin = \Yii::$app->plugin->getPlugin($this->sign);
                if (!method_exists($plugin, 'getGoodsData')) {
                    throw new \Exception('没有这个getGoods这个函数');
                }
                $res = $plugin->getGoodsData($this->attributes);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '',
                    'data' => $res
                ];
            } catch (\Exception $exception) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $exception->getMessage()
                ];
            }
        } else {
            $common = new GoodsList();
            $common->attributes = $this->attributes;
            $common->relations = ['goodsWarehouse', 'mallGoods'];
            $common->status = 1;
            $common->sign = $this->sign ?: ['mch', ''];
            $common->mch_id = $this->mch_id;
            /* @var Goods[] $goodsList */
            $goodsList = $common->search();

            $newList = [];
            foreach ($goodsList as $goods) {
                $newItem = $common->getDiyBack($goods);
                if ($goods->mallGoods) {
                    $newItem = array_merge($newItem, [
                        'is_negotiable' => $goods->mallGoods->is_negotiable
                    ]);
                }
                $newList[] = $newItem;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $newList,
                    'pagination' => $common->pagination
                ]
            ];
        }
    }
}
