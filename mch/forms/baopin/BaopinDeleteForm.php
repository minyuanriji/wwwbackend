<?php
namespace app\mch\forms\baopin;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\baopin\models\BaopinMchGoods;

class BaopinDeleteForm extends BaseModel{

    public $mch_id;
    public $goods_id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['goods_id', 'mch_id'], 'required'],
            [['goods_id', 'mch_id'], 'integer']
        ]);
    }

    public function delete(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $model = BaopinMchGoods::findOne([
                "goods_id" => $this->goods_id,
                "mch_id"   => $this->mch_id
            ]);
            if(!$model){
                throw new \Exception("爆品记录不存在");
            }

            $model->is_delete = 1;
            if(!$model->save()){
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