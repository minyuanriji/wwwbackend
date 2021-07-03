<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-21
 * Time: 19:30
 */

namespace app\plugins\stock\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\User;
use app\plugins\stock\models\Stock;
use app\plugins\stock\models\UpgradeBag;

class UpgradeBagForm extends BaseModel
{
    public $id;
    public $level;
    public $goods_id;
    public $stock_num;
    public $is_stock;
    public $unit_price;
    public $stock_goods_id;
    public $name;
    public $is_enable;
    public $compute_type;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'],'string'],
            [['level', 'goods_id', 'stock_num', 'is_stock', 'stock_goods_id','is_enable','compute_type'], 'integer'],
            [['unit_price'], 'number'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $existBag=UpgradeBag::findOne(['level'=>$this->level,'mall_id'=>\Yii::$app->mall->id,'is_delete'=>0]);
        if($existBag&&$existBag->id!=$this->id){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '配置已存在');
        }

        $existBag=UpgradeBag::findOne(['goods_id'=>$this->goods_id,'mall_id'=>\Yii::$app->mall->id,'is_delete'=>0]);
        if($existBag&&$existBag->id!=$this->id){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '该商品已经加入其他配置方案');
        }
        if ($this->id) {
            $bag = UpgradeBag::findOne(['is_delete' => 0, 'id' => $this->id]);
            if (!$bag) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL, '配置不存在');
            }
        } else {
            $bag = new UpgradeBag();
            $bag->mall_id = \Yii::$app->mall->id;
        }
        if ($this->is_stock) {
            if (!$this->goods_id) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL, '请填写商品ID');
            } else {
                $goods = Goods::findOne(['id' => $this->goods_id, 'mall_id' => \Yii::$app->mall->id]);
                if (!$goods) {
                    return $this->returnApiResultData(ApiCode::CODE_FAIL, '商品不存在');
                }
            }
        }
        $bag->attributes = $this->attributes;
        if (!$bag->save()) {
            return $this->responseErrorMsg($bag);
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '保存成功');
    }

}