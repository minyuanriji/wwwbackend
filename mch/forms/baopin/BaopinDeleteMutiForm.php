<?php
namespace app\mch\forms\baopin;

use app\core\ApiCode;
use app\models\BaseModel;

class BaopinDeleteMutiForm extends BaseModel{

    public $mch_id;
    public $goods_id_str;

    public function rules(){
        return array_merge(parent::rules(), [
            [['goods_id_str', 'mch_id'], 'required'],
            [['mch_id'], 'integer'],
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
                $form = new BaopinDeleteForm();
                $form->mch_id   = $this->mch_id;
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