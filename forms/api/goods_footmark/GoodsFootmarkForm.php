<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-13
 * Time: 9:32
 */

namespace app\forms\api\goods_footmark;


use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\GoodsFootmark;
use app\models\GoodsWarehouse;

class GoodsFootmarkForm extends BaseModel
{
    public $id;
    public $page = 1;
    public $limit = 10;

    public function rules()
    {
        return [
            [['page', 'limit', 'id'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
        ]; // TODO: Change the autogenerated stub
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 19:38
     * @Note:获取列表
     * @return array
     */
    public function getList()
    {

        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        /**
         * @var BasePagination $pagination
         *
         */
        $list = GoodsFootmark::find()
            ->with('goods')
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id])
            ->page($pagination, $this->limit, $this->page)
            ->all();

        $newList = [];
        /**
         * @var GoodsFootmark $list [];
         * @var GoodsWarehouse $goodsInfo
         */
        foreach ($list as $item) {
            $goodsInfo = $item->goods->goodsWarehouse;
            $newItem['footmark_id'] = $item->id;
            $newItem['goods_id'] = $item->goods_id;
            $newItem['goods_name'] = $goodsInfo->name;
            $newItem['created_at'] = date('Y-m-d', $item->created_at);
            $newItem['cover_pic'] = $goodsInfo->cover_pic;
            $newItem['price'] = $item->goods->price;
            $newList[] = $newItem;
        }
        if (count($newList)) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', ['list' => $newList,
                'page_count' => $pagination->page_count, 'total_count' => $pagination->total_count
            ]);
        }
        return $this->returnApiResultData(ApiCode::CODE_FAIL, '暂无足迹');
    }


    public function delete()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mark = GoodsFootmark::findOne(['id' => $this->id, 'user_id' => \Yii::$app->user->id, 'is_delete' => 0]);

        if (!$mark) {


            return $this->returnApiResultData(ApiCode::CODE_FAIL, '足迹不存在或已被删除！');
        }

        $mark->is_delete = 1;
        if ($mark->save()) {

            return  $this->returnApiResultData(ApiCode::CODE_SUCCESS, '删除成功');
        }

        return  $this->returnApiResultData(ApiCode::CODE_FAIL, '删除失败');
    }
}