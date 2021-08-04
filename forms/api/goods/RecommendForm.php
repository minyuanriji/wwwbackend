<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-28
 * Time: 11:57
 */

namespace app\forms\api\goods;


use app\core\ApiCode;
use app\core\BasePagination;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\forms\common\CommonAppConfig;
use app\forms\common\goods\GoodsList;
use app\forms\common\goods\RecommendSettingForm;
use app\logic\AppConfigLogic;
use app\models\BaseModel;
use app\models\Goods;
use app\models\Mall;

/**
 *
 * @var BasePagination $pagination
 * @property Mall $mall
 */
class RecommendForm extends BaseModel implements ICacheForm
{
    public $mall;
    public $goods_id;
    public $type;
    public $pagination;
    public $page;

    public function rules()
    {
        return [
            [['goods_id', 'page'], 'integer'],
            [['type'], 'string'],
            [['type'], 'default', 'value' => 'goods']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $this->mall = \Yii::$app->mall;
        $setting = $this->mall->getMallSetting(['is_recommend']);
        if (!$setting['is_recommend'] || $setting['is_recommend'] == 0) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '未开启推荐商品',
                'data' => [
                    'list' => []
                ]
            ];
        }
        $option = AppConfigLogic::getAppCatStyle();
        $recommendCount = $option['recommend_count'];

        /** @var Goods $goods */
        $goods = Goods::find()->with('goodsWarehouse.cats')->where([
            'id' => $this->goods_id,
            'mall_id' => \Yii::$app->mall->id
        ])->one();

        $form = new GoodsList();
        $form->cat_id = array_column($goods->goodsWarehouse->goodsCatRelation, 'cat_id');
        $form->status = 1;
        $form->sign = ['mch', ''];
        $form->limit = $recommendCount;
        $form->exceptSelf = $this->goods_id;
        $form->sort = 4;
        $goodsList = $form->getList();
        $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', ['list' => $goodsList]);
    }

    public function getSourceDataForm(){
        $res = $this->getNewList();
        if($res['code'] == ApiCode::CODE_SUCCESS){
            return new APICacheDataForm([
                "sourceData" => $res
            ]);
        }
        return $res;
    }

    public function getCacheKey(){
        $keys[] = (int)$this->page;
        $keys[] = (int)\Yii::$app->mall->id;
        $keys[] = (int)$this->goods_id;
        $keys[] = (int)$this->login_uid;
        $keys[] = !empty($this->type) ? $this->type : "";
        return $keys;
    }

    public function getNewList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        try {
            $form = new RecommendSettingForm();
            $setting = $form->getSetting();
            $list = [];
            foreach ($setting as $key => $item) {
                if ($key == $this->type) {
                    $list = $this->getGoodsList($item, $key);
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '请求成功', ['list' => $list,'page_count'=>$this->pagination->page_count,'total_count'=>$this->pagination->total_count]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

    private function getGoodsList($item, $key)
    {
        if ($item['is_recommend_status'] == 0) {
            return [];
        }

        $goodsIds = [];
        $form = new GoodsList();
        $form->is_login = $this->is_login;
        $form->login_uid = $this->login_uid;
        if ($key == 'goods') {
            /** @var Goods $goods */
            $goods = Goods::find()->with('goodsWarehouse.cats')->where([
                'id' => $this->goods_id,
                'mall_id' => \Yii::$app->mall->id
            ])->one();
            if ($goods) {
                $form->cat_id = array_column($goods->goodsWarehouse->goodsCatRelation, 'cat_id');
            }
            $form->limit = $item['goods_num'];
            $form->exceptSelf = $this->goods_id;
        } else {
            if ($item['is_custom'] == 1) {
                // 推荐商品自定义
                foreach ($item['goods_list'] as $gItem) {
                    if (!in_array($gItem['id'], $goodsIds)) {
                        $goodsIds[] = $gItem['id'];
                    }
                }
                $form->goods_id = $goodsIds;
                $form->limit = count($goodsIds);
            } else {
                // 获取商品列表排序前10件商品
                $form->limit = 10;
                $form->sort = 1;
            }
        }


        $form->status = 1;
        $form->sign = ['mch', ''];
        $list = $form->getList();
        $this->pagination=$form->pagination;
        // 商品重新排序
        $newList = [];
        if (isset($item['is_custom']) && $item['is_custom']) {
            foreach ($goodsIds as $id) {
                foreach ($list as $item) {
                    if ($item['id'] == $id) {
                        $newList[] = $item;
                    }
                }
            }
        } else {
            $newList = $list;
        }
        return $newList;
    }
}
