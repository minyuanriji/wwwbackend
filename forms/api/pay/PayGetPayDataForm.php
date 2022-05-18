<?php

namespace app\forms\api\pay;

use app\core\ApiCode;
use app\forms\api\payCenter\paymentOrderPrepare\BasePrepareForm;
use app\models\BaseModel;
use app\models\PaymentPrepare;

class PayGetPayDataForm extends BaseModel{

    public $source_table;
    public $token;
    public $queue_id;
    public $id;

    public function rules(){
        return [
            [['source_table'], 'required'],
            [['token'], 'trim'],
            [['id'], 'safe']
        ];
    }

    public function getData(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            if(empty($this->token) && !$this->id){
                throw new \Exception("参数id或token不能同时为空");
            }

            $where['mall_id'] = \Yii::$app->mall->id;
            $where['source_table'] = $this->source_table;
            if($this->id){
                $where['order_id'] = $this->id;
            }else{
                $where['token'] = $this->token;
            }

            $paymentPrepare = PaymentPrepare::findOne($where);
            if(!$paymentPrepare){
                throw new \Exception("数据异常");
            }

            $className = $paymentPrepare->prepare_class;
            if(!class_exists($className)){
                throw new \Exception("类“{$className}”不存在");
            }

            $class = new $className([
                "token"    => $this->token,
                "order_id" => $this->id
            ]);
            if(!($class instanceof BasePrepareForm)){
                throw new \Exception("类“{$className}”必须继承父类“BasePrepareForm”");
            }

            return $class->prepare();
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}