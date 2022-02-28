<?php


namespace app\forms\api\payCenter;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\IntegralLog;
use app\models\User;

abstract class IntegralBasePayForm extends BaseModel {

    public $trade_pwd;

    public function rules(){
        return [
            [['trade_pwd'], 'required']
        ];
    }

    public function pay(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            //用户信息判断
            $user = User::findOne(\Yii::$app->user->id);
            if(!$user || $user->is_delete){
                throw new \Exception("无法获取到用户信息");
            }

            //验证交易密码
            if (empty($user->transaction_password) || !\Yii::$app->getSecurity()->validatePassword($this->trade_pwd, $user->transaction_password)) {
                throw new \Exception('交易密码错误');
            }

            //待支付金豆数
            $payIntegral = $this->payIntegray($user);
            if($user->static_integral < $payIntegral){
                throw new \Exception('金豆不足');
            }

            //金豆扣取
            $currentIntegral = floatval($user->static_integral);
            $user->static_integral = max(0, $currentIntegral - floatval($payIntegral));
            if(!$user->save()){
                throw new \Exception(json_encode($user->getErrors()));
            }

            //支付后操作
            $logData = ['source_id' => '', 'source_type' => '', 'desc' => ''];
            $result = $this->paidAction($user, function($id, $type, $desc) use(&$logData){
                $logData['source_id']   = $id;
                $logData['source_type'] = $type;
                $logData['desc']        = $desc;
            });

            //生成金豆记录
            $integralLog = new IntegralLog(array_merge($logData, [
                "mall_id"          => $user->mall_id,
                "user_id"          => $user->id,
                "type"             => 2,
                "current_integral" => $currentIntegral,
                "integral"         => $payIntegral,
                "created_at"       => time(),
                "is_manual"        => 0
            ]));
            if(!$integralLog->save()){
                throw new \Exception(json_encode($integralLog->getErrors()));
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $result
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * 获取要支付的金豆数
     * @param User $user
     * @return float
     */
    abstract protected function payIntegray(User $user);


    /**
     * 支付后操作
     * @param User $user
     * @param Closure $callback 回掉方法传入金豆记录来源ID、类型、描述
     * @return array
     */
    abstract protected function paidAction(User $user, \Closure $callback);
}