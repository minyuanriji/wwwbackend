<?php
namespace app\mch\forms\order;

use app\models\BaseModel;
use app\models\Integral;
use app\models\IntegralDeduct;
use app\models\IntegralRecord;
use app\models\User;

class CheckoutOrderDeductIntegralForm extends BaseModel{

    public $user_id;  //用户ID
    public $deduction_price; //抵扣金额
    public $desc;

    public $source_table;//来源表
    public $source_id;   //来源ID

    public static $errorMsg;

    public function rules(){
        return [
            [['user_id', 'deduction_price', 'source_table', 'source_id'], 'required'],
            [['user_id', 'source_id'], 'integer'],
            [["deduction_price"], "number", "min" => 0],
            [['source_table', 'desc'], 'string']
        ];
    }

    /**
     * 购物券抵扣
     * @return boolean
     */
    public function save(){

        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->validate()) {
                throw new \Exception($this->responseErrorMsg());
            }

            if((float)$this->deduction_price <= 0){
                throw new \Exception("抵扣金额必须大于0");
            }

            //需要抵扣的购物券数
            $integralDeductionPrice = (float)$this->deduction_price ;

            //用户对象
            $user = User::findOne($this->user_id);

            //有动态购物券优先扣减
            if($user->dynamic_integral > 0){

                //获取用户所有可用动态购物券记录，并且按过期时间升序排列
                $dynamicIntegrals = static::getDynamicIntegrals($this->user_id);

                $deduct = array(
                    'controller_type'   => 1,
                    'mall_id'           => $user->mall_id,
                    'user_id'           => $user->id,
                    'source_id'         => $this->source_id,
                    'source_table'      => $this->source_table
                );
                $beforeMoney = $user->dynamic_integral;

                foreach($dynamicIntegrals as $integral){

                    $deduct['record_id']    = $integral['id'];
                    $deduct['before_money'] =  $beforeMoney;

                    //统计出可以抵扣的数额
                    $canDeductMoney = !empty($integral['deduct']) ? $integral['money'] + array_sum(array_column($integral['deduct'], 'money')) : $integral['money'];

                    if(intval(bcmul($canDeductMoney,100)
                            >= intval(bcmul($integralDeductionPrice,100)))){

                        //当前券的面值足够抵扣掉订单，则从此券中扣除
                        $deduct['money'] = $integralDeductionPrice * -1;
                        $deduct['desc']  = $this->desc . '扣除动态购物券('.$integral['id'].')抵扣：'.$integralDeductionPrice;

                        $this->deduct($deduct, $user);

                        if(intval(bcmul($canDeductMoney,100) == intval(bcmul($integralDeductionPrice,100)))){
                            $integral->status = 3;
                            if(!$integral->save()){
                                throw new \Exception($integral->getErrorMessage());
                            }
                        }

                        $beforeMoney -= $integralDeductionPrice;
                        $integralDeductionPrice = 0;
                        break;
                    }else{ //当前券的面值不足够抵扣掉订单使用的券，则扣除当前全部面值

                        $beforeMoney -= $canDeductMoney;
                        $integralDeductionPrice -= $canDeductMoney;

                        $deduct['money'] = $canDeductMoney * -1;
                        $deduct['desc']  = $this->desc . '扣除动态购物券('.$integral['id'].')抵扣：'.$canDeductMoney;

                        static::deduct($deduct, $user);

                        $integral->status = 3;
                        if(!$integral->save()){
                            throw new Exception($integral->getErrorMessage());
                        }
                    }
                }
            }

            //使用永久购物券补足不够的
            if($integralDeductionPrice > 0){
                $diffIntegral = $user->static_integral - $integralDeductionPrice;
                if($diffIntegral < 0){
                    throw new \Exception('永久购物券不足');
                }
                $record = array(
                    'controller_type' => 1,
                    'mall_id'         => $user->mall_id,
                    'user_id'         => $user->id,
                    'money'           => $integralDeductionPrice * -1,
                    'desc'            => $this->desc . '静态购物券抵扣：'.$integralDeductionPrice,
                    'before_money'    => $user['static_integral'],
                    'type'            => Integral::TYPE_ALWAYS,
                    'source_id'       => $this->source_id,
                    'source_table'    => $this->source_table,
                );
                // 写入日志
                if(!IntegralRecord::record($record)){
                    throw new \Exception(IntegralRecord::getError());
                }
            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            static::$errorMsg = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * 新增购物券抵扣记录
     * @param array $log
     * @return void
     */
    private function deduct(array $log, $wallet){
        try{
            $model = new IntegralDeduct();
            $model->loadDefaultValues();
            $model->attributes = $log;
            if(!$model->save()){
                throw new \Exception($model->getErrorMessage());
            }

            //更新用户动态抵扣券数
            $wallet->dynamic_integral -= $log['money'];

            if(!$wallet->save(false)){
                throw new \Exception($wallet->getErrorMessage());
            }

        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $userId 获取用户所有可用动态购物券记录
     * @return array
     */
    private function getDynamicIntegrals($userId){
        $dynamicIntegrals = IntegralRecord::find()->where(array(
            'controller_type' => 1,
            'type'            => Integral::TYPE_DYNAMIC,
            'status'          => 1,
            'user_id'         => $userId
        ))->with(['deduct'])->orderBy('expire_time ASC')->all();
        return $dynamicIntegrals ? $dynamicIntegrals : [];
    }
}