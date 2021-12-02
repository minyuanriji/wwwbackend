<?php

namespace app\forms\mall\finance;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchPriceLog;

class MchPriceLogApplyForm extends BaseModel{

    public $id;
    public $act;
    public $content;

    public function rules(){
        return [
            [['id', 'act', 'content'], 'required', 'message'=>'{attribute}不能为空']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'act' => '操作方法',
            'content' => '备注信息',
        ];
    }

    public function doApply(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $priceLog = MchPriceLog::findOne($this->id);
            if(!$priceLog){
                throw new \Exception("结算记录[ID:{$this->id}]不存在");
            }
            if($priceLog->status != "unconfirmed"){
                throw new \Exception("状态{$priceLog->status}异常");
            }

            if($this->act == "confirmed"){
                $priceLog->status = "confirmed";
            }elseif($this->act == "canceled"){
                $priceLog->status = "canceled";
            }

            $priceLog->updated_at = time();
            $priceLog->remark     = $this->content;
            if(!$priceLog->save()){
                throw new \Exception($this->responseErrorMsg($priceLog));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '操作成功');
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}