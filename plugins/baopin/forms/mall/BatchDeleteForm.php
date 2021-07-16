<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;

class BatchDeleteForm extends BaseModel{

    public $goods_id_str;

    public function rules(){
        return array_merge(parent::rules(), [
            [['goods_id_str'], 'required'],
            [['goods_id_str'], 'string']
        ]);
    }

    public function deleteMuti(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $goodsIdArray = explode(",", $this->goods_id_str);
            foreach($goodsIdArray as $goodsId){
                $form = new DeleteForm();
                $form->goods_id = (int)$goodsId;
                $res = $form->delete();
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '批量删除成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}