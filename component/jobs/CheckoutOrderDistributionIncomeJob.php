<?php
namespace app\component\jobs;


use app\models\IncomeLog;
use app\models\Mall;
use app\models\User;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionSetting;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\mch\models\MchDistributionDetail;
use yii\base\Component;
use yii\queue\JobInterface;

class CheckoutOrderDistributionIncomeJob extends Component implements JobInterface{

    public function execute($queue){

        $checkoutOrder = MchCheckoutOrder::find()->where([
            "is_delete"       => 0,
            "is_distribution" => 0,
            "is_pay"          => 1
        ])->orderBy("updated_at ASC")->limit(1)->one();
        if(!$checkoutOrder) return;

        $checkoutOrder->updated_at = time();
        $checkoutOrder->save();

        $t = \Yii::$app->getDb()->beginTransaction();
        try {

            $mall = Mall::findOne($checkoutOrder->mall_id);
            if($mall){
                \Yii::$app->setMall($mall);
                $level = DistributionSetting::getValueByKey(DistributionSetting::LEVEL);
            }

            $mch  = Mch::findOne($checkoutOrder->mch_id);
            $user = User::findOne($checkoutOrder->pay_user_id);

            if(!$mall || empty($level) || $level < 1 || !$user || !$mch || !$mch->distribution_detail_set){
                $checkoutOrder->is_distribution = 1;
                $checkoutOrder->save();
                $t->commit();
                return;
            }



            $distribution_detail_list = MchDistributionDetail::find()->andWhere([
                'mch_id' => $mch->id
            ])->all();

            $distribution_list = []; //先找出分销商
            $distribution1 = Distribution::findOne(['user_id' => $user->parent_id, 'is_delete' => 0]);//一级
            if ($distribution1) {
                $distribution_list[0] = $distribution1;
                $parent1 = User::findOne($distribution1->user_id);
                if ($parent1) {
                    $distribution2 = Distribution::findOne(['user_id' => $parent1->parent_id, 'is_delete' => 0]);//二级
                    if ($distribution2) {
                        $distribution_list[1] = $distribution2;
                        $parent2 = User::findOne($distribution2->user_id);
                        if ($parent2) {
                            $distribution3 = Distribution::findOne(['user_id' => $parent2->parent_id, 'is_delete' => 0]);//三级
                            if ($distribution3) {
                                $distribution_list[2] = $distribution3;
                            }
                        }
                    }
                }
            }

            $price_type = $mch->distribution_share_type;

            for ($i = 0; $i < $level; $i++) {
                $user_level = $i + 1; //用户层级

                if(count($distribution_list) <= $i)  break;

                $distribution = $distribution_list[$i];

                if(!$distribution) continue;

                $first_price = $second_price = $third_price = 0;
                $price = 0;
                foreach ($distribution_detail_list as $detail) {
                    if ($detail->level == $distribution->level) {
                        $first_price  = $detail->commission_first;
                        $second_price = $detail->commission_second;
                        $third_price  = $detail->commission_third;
                        break;
                    }
                }

                if ($price_type == 2) { //按固定金额
                    if ($user_level == 1) { //一级
                        $price = $first_price;
                    }
                    if ($user_level == 2) { //二级
                        $price = $second_price;
                    }
                    if ($user_level == 3) { //三级
                        $price = $third_price;
                    }
                }

                if ($price_type == 1) { //按百分比
                    if ($user_level == 1) { //一级
                        $price = $first_price * $checkoutOrder->order_price / 100;
                    }
                    if ($user_level == 2) { //二级
                        $price = $second_price * $checkoutOrder->order_price / 100;;
                    }
                    if ($user_level == 3) { //三级
                        $price = $third_price * $checkoutOrder->order_price / 100;;
                    }
                }

                if($price > 0){
                    $distributionUser = $distribution->user;
                    if($distributionUser){
                        $this->incomeChange($distributionUser, $mch, $checkoutOrder, $price);
                    }
                }
            }

            $checkoutOrder->is_distribution = 1;
            $checkoutOrder->updated_at = time();
            if(!$checkoutOrder->save()){
                throw new \Exception(json_encode($checkoutOrder->getErrors()));
            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
        }

    }

    private function incomeChange(User $user, Mch $mch, MchCheckoutOrder $checkoutOrder, $price){

        $data = [
            "user_id"         => $user->id,
            "order_detail_id" => $checkoutOrder->id,
            "type"            => 1,
            "flag"            => 1,
            "from"            => 2
        ];
        $incomeLog = IncomeLog::findOne($data);
        if($incomeLog) return;

        $user->income += floatval($price);
        $user->total_income += floatval($price);
        $desc = "来自商家“".$mch->store->name."”结账订单：" . $checkoutOrder->id . "的佣金收入";

        if(!$user->save()){
            throw new \Exception("用户收入信息更新失败");
        }

        $incomeLog = new IncomeLog();
        $incomeLog->mall_id         = $user->mall_id;
        $incomeLog->user_id         = $user->id;
        $incomeLog->order_detail_id = $checkoutOrder->id;
        $incomeLog->type            = $data['type'];
        $incomeLog->money           = $price;
        $incomeLog->desc            = $desc;
        $incomeLog->flag            = $data['flag'];
        $incomeLog->from            = $data['from'];
        $incomeLog->income          = $user->total_income;
        $incomeLog->created_at      = $checkoutOrder->created_at;
        if(!$incomeLog->save()){
            throw new \Exception("收入记录生成失败！");
        }
    }
}