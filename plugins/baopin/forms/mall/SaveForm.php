<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\plugins\baopin\models\BaopinGoods;

class SaveForm extends BaseModel{

    public $goods_id_str;

    public function rules(){
        return array_merge(parent::rules(), [
            [['goods_id_str'], 'required'],
            [['goods_id_str'], 'string']
        ]);
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $goodsIdArray = explode(",", $this->goods_id_str);
            foreach($goodsIdArray as $goodsId){
                $goodsId = (int)$goodsId;

                //只能添加一个
                $exists = BaopinGoods::find()->where(["goods_id" => $goodsId])->exists();
                if($exists) continue;

                //获取商品信息
                $goods = Goods::findOne($goodsId);
                if(!$goods) continue;

                $model = new BaopinGoods();
                $model->mall_id    = $goods->mall_id;
                $model->goods_id   = $goods->id;
                $model->created_at = time();
                $model->updated_at = time();
                if(!$model->save()){
                    throw new \Exception($this->responseErrorMsg($model));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}