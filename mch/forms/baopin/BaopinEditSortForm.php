<?php
namespace app\mch\forms\baopin;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\baopin\models\BaopinMchGoods;

class BaopinEditSortForm extends BaseModel{

    public $id;
    public $mch_id;
    public $sort;

    public function rules(){
        return [
            [['id', 'sort', 'mch_id'], 'required'],
            [['id', 'sort', 'mch_id'], 'integer']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $record = BaopinMchGoods::findOne([
                'id'     => $this->id,
                'mch_id' => $this->mch_id
            ]);
            if(!$record){
                throw new \Exception("无法获取爆品记录");
            }
            $record->sort = max(0, $this->sort);
            if(!$record->save()){
                throw new \Exception($this->responseErrorMsg($record));
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