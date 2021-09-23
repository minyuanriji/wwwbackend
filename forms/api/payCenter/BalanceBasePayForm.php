<?php


namespace app\forms\api\payCenter;


use app\core\ApiCode;
use app\forms\common\UserBalanceModifyForm;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\User;

abstract class BalanceBasePayForm extends BaseModel {

    public $trade_pwd;

    public function rules(){
        return [
            [['trade_pwd'], 'required']
        ];
    }

    public function pay()
    {
        if (!$this->validate()) {
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

            //待支付余额
            $payBalance = $this->payBalance($user);
            if($user->balance < $payBalance){
                throw new \Exception('余额不足');
            }

            //支付后操作
            $logData = ['source_id' => '', 'source_type' => '', 'desc' => ''];
            $result = $this->paidAction($user, function($id, $type, $desc) use(&$logData){
                $logData['source_id']   = $id;
                $logData['source_type'] = $type;
                $logData['desc']        = $desc;
            });

            //余额扣取
            $balanceModifyForm = new UserBalanceModifyForm([
                "type"        => BalanceLog::TYPE_SUB,
                "money"       => $payBalance,
                "source_id"   => $logData['source_id'],
                "source_type" => $logData['source_type'],
                "desc"        => $logData['desc'],
                "custom_desc" => ""
            ]);
            $balanceModifyForm->modify($user);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $result
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * 获取要支付的余额
     * @param User $user
     * @return float
     */
    abstract protected function payBalance(User $user);


    /**
     * 支付后操作
     * @param User $user
     * @param Closure $callback 回掉方法传入红包记录来源ID、类型、描述
     * @return array
     */
    abstract protected function paidAction(User $user, \Closure $callback);
}