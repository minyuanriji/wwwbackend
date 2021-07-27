<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\baopin\models\BaopinGoods;

class DeleteForm extends BaseModel{

    public $goods_id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['goods_id'], 'required']
        ]);
    }

    public function delete(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $model = BaopinGoods::findOne(["goods_id" => $this->goods_id]);
            if(!$model){
                throw new \Exception("爆品记录不存在");
            }

            if(!$model->delete()){
                throw new \Exception("爆品记录：".$this->goods_id."删除失败了");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '删除成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}