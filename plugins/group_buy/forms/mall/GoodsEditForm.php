<?php
/**
 * 保存普通商品 goods表
 * xuyaoxiang
 * 2020/09/02
 */
namespace app\plugins\group_buy\forms\mall;

use app\models\BaseModel;
use app\models\Goods;

class GoodsEditForm extends BaseModel
{
    public $goods_id;
    public $mall_id;

    //验证规则
    public function rules()
    {
        return [
            [['goods_id', 'mall_id'], 'integer']
        ];
    }

    public function init()
    {
        $this->mall_id = \Yii::$app->mall->id;
    }

    /**
     * 保存
     * @return array
     */
//    public function save()
//    {
//        if (!$this->validate()) {
//            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
//        }
//
//        $model = Goods::findOne(['id' => $this->goods_id, 'mall_id' => $this->mall_id]);
//
//        $model->sign = 'group_buy';
//
//        if (!$model->save()) {
//            return $this->returnApiResultData(1, $this->responseErrorMsg($model));
//        }
//
//        return $this->returnApiResultData(0, "保存成功",$model);
//    }
//
//    /**
//     * 删除数据
//     * 商品表:goods
//     * @param $goods_id
//     * @return array
//     */
    public function del()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
        }

        $model = Goods::findOne(['id'         => $this->goods_id,
                                 'mall_id'    => $this->mall_id,
        ]);

        if (!$model) {
            return $this->returnApiResultData(98, "商品不存在");
        }

        $model->sign = "";

        if (!$model->save()) {

            return $this->returnApiResultData(97, $this->responseErrorMsg($model));
        }

        return $this->returnApiResultData(0, "删除拼团商品成功.", $model);
    }
}