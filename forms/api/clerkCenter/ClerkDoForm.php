<?php

namespace app\forms\api\clerkCenter;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\clerk\ClerkData;
use app\models\clerk\ClerkLog;

class ClerkDoForm extends BaseModel{

    public $id;
    public $remark;

    public function rules(){
        return [
            [['id'], 'required'],
            [['remark'], 'safe']
        ];
    }

    public function doClerk(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $clerkData = ClerkData::findOne($this->id);
            if(!$clerkData){
                throw new \Exception("核销数据不存在");
            }

            if($clerkData->status != 0){
                throw new \Exception("当前状态无法核销");
            }

            if(empty($clerkData->process_class) || !class_exists($clerkData->process_class)){
                throw new \Exception("核销处理类”".$clerkData->process_class."“不存在");
            }

            //处理核销
            $className = $clerkData->process_class;
            $class = new $className(["clerk_user_id" => \Yii::$app->user->id]);

            $class->process($clerkData);

            //更新核销数据
            $clerkData->status = 1;
            $clerkData->updated_at = time();
            if(!$clerkData->save()){
                throw new \Exception($this->responseErrorMsg($clerkData));
            }

            //生成核销记录
            $clerkLog = new ClerkLog([
                'mall_id'       => $clerkData->mall_id,
                'clerk_data_id' => $clerkData->id,
                'user_id'       => \Yii::$app->user->id,
                'created_at'    => time(),
                'remark'        => $this->remark
            ]);
            if(!$clerkLog->save()){
                throw new \Exception($this->responseErrorMsg($clerkLog));
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '核销成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }

    }

}