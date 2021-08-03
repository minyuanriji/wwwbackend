<?php
namespace app\commands;

use app\commands\commission_action\Addcredit3rAction;
use app\commands\commission_action\AddcreditAction;
use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\commission\models\CommissionRuleChain;
use yii\db\ActiveQuery;

class CommissionController extends BaseCommandController{

    public function actionTest ()
    {
        (new AddcreditAction(null,null))->run();
        (new Addcredit3rAction(null,null))->run();
    }

    public function actions(){
        return [
            "goods"         => "app\\commands\\commission_action\\GoodsAction",
            "checkout"      => "app\\commands\\commission_action\\CheckoutAction",
            "store"         => "app\\commands\\commission_action\\StoreAction",
            "hotel"         => "app\\commands\\commission_action\\HotelAction",
            "hotel3r"       => "app\\commands\\commission_action\\Hotel3rAction",
            "addcredit"     => "app\\commands\\commission_action\\AddcreditAction",
            "addcredit3r"   => "app\\commands\\commission_action\\Addcredit3rAction",
            "giftpacks"     => "app\\commands\\commission_action\\GiftpacksAction",
        ];
    }

    public function actionMaintantJob(){

        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 分佣守候程序启动...完成\n";

        $pm = new \Swoole\Process\ProcessManager();
        foreach($this->actions() as $id => $class){
            $pm->add(function (\Swoole\Process\Pool $pool, int $workerId) use($id){
                if(!defined("Yii")){
                    require_once(__DIR__ . '/../vendor/autoload.php');
                    require_once __DIR__ . '/../config/const.php';
                    new \app\core\ConsoleApplication();
                }
                $this->commandOut("[Worker #{$workerId}] WorkerStart, Task:{$id}, pid: " . posix_getpid());
                $this->runAction($id);
            });
        }
        $pm->start();
    }


    /**
     * 计算利润
     * @param $order_price
     * @param $transfer_rate
     * @return mixed
     */
    public function calculateCheckoutOrderProfitPrice($order_price, $transfer_rate){
        return max(0, $order_price * ($transfer_rate/100 - 0.1) * 0.6);
    }

    /**
     * 获取要分佣的父级列表
     * @param $user_id
     * @param $item_id
     * @param $item_type
     * @return array
     * @throws \Exception
     */
    public function getCommissionParentRuleDatas($user_id, $item_id, $item_type){

        //获取支付用户信息
        $user = User::findOne($user_id);
        $userLink = UserRelationshipLink::findOne(["user_id" => $user_id]);
        if(!$user || !$userLink){
            throw new \Exception("支付用户[ID:".($user ? $user->id : 0)."]不存在或关系链异常");
        }

        $query = User::find()->alias("u")
                    ->leftJoin("{{%user_relationship_link}} url", "url.user_id=u.id");
        $query->andWhere([
            "AND",
            ["u.is_delete" => 0],
            ["IN", "u.role_type", ["store", "partner", "branch_office"]],
            ("url.`left` < '".$userLink->left."' AND url.`right` > '".$userLink->right."'")
        ])->select(["u.id", "u.parent_id", "u.total_income", "u.role_type", "u.nickname"])->orderBy("url.`left` DESC");

        $parentDatas = $query->asArray()->all();
        if(!$parentDatas){
            throw new \Exception("无法获取上级[ID:".$userLink->parent_id."]信息", self::ERR_CODE_NOT_FOUND_PARENTS);
        }

        //对获取的所有上级进行处理
        $existData = $newParentDatas = [];
        $partner2Data = null;
        foreach($parentDatas as $parentData){
            if(count($existData) >= 3) break;
            $appendNew = false;
            if(empty($partner2Data) && isset($existData['partner']) && $parentData['role_type'] == "partner"){
                if($existData['partner']['parent_id'] == $parentData['id']){
                    $partner2Data = $parentData;
                    continue;
                }
            }elseif($parentData['role_type'] == "store" && !isset($existData['store']) && !isset($existData['branch_office']) && !isset($existData['partner'])){
                $appendNew = true;
            }elseif($parentData['role_type'] == "partner"&& !isset($existData['partner']) && !isset($existData['branch_office'])){
                $appendNew = true;
            }elseif($parentData['role_type'] == "branch_office" && !isset($existData['branch_office'])){
                $appendNew = true;
            }
            if($appendNew){
                $existData[$parentData['role_type']] = $parentData;
                $newParentDatas[] = $parentData;
            }
        }

        //平级合伙人插入
        $newParentDatas = array_reverse($newParentDatas);
        if(!empty($partner2Data)){
            $tmpDatas = [];
            foreach($newParentDatas as $parentData){
                if($parentData['role_type'] == "partner"){
                    $tmpDatas[] = $partner2Data;
                }
                $tmpDatas[] = $parentData;
            }
            $newParentDatas = $tmpDatas;
        }

        //生成相关规则键
        $parentDatas = [];
        while(!empty($newParentDatas)){
            $relKeys = [];
            foreach($newParentDatas as $newParentData){
                $relKeys[] = $newParentData['role_type'] . "#all";
            }
            $parentData = array_shift($newParentDatas);
            $parentData['rel_keys'] = $relKeys;
            $parentDatas[] = $parentData;
        }

        $getChainRuleData = function(ActiveQuery $query, $item_id){
            //商品独立设置规则
            $newQuery = clone $query;
            $newQuery->andWhere([
                "AND",
                ['cr.apply_all_item' => 0],
                ['cr.item_id' => $item_id]
            ]);
            $ruleData = $newQuery->one();

            //无独立规则，使用全局规则
            if(!$ruleData){
                $newQuery = clone $query;
                $newQuery->andWhere(['cr.apply_all_item' => 1]);
                $ruleData = $newQuery->one();
            }

            return $ruleData;
        };

        $currentLevel = count($parentDatas);

        foreach($parentDatas as $key => $parentData){

            $query = CommissionRuleChain::find()->alias("crc");
            $query->leftJoin("{{%plugin_commission_rules}} cr", "cr.id=crc.rule_id");
            $query->andWhere([
                "AND",
                ["cr.is_delete"  => 0],
                ['cr.item_type'  => $item_type],
                ['crc.role_type' => $parentData['role_type']],
                ['crc.level'     => $currentLevel]
            ]);
            $query->orderBy("crc.level DESC");
            $query->select(["cr.commission_type", "crc.level", "crc.commisson_value"]);
            $query->asArray();

            //查找规则
            $relKeys = array_reverse($parentData['rel_keys']);
            $ruleData = null;
            foreach($relKeys as $relKey){
                $newQuery = clone $query;
                $newQuery->andWhere("crc.unique_key LIKE '%{$relKey}'" );
                $ruleData = $getChainRuleData($newQuery, $item_id);

                //$this->commandOut("current LEVEL:" . $currentLevel);
                //$this->commandOut($newQuery->createCommand()->getRawSql());
                //$this->commandOut(json_encode($ruleData));
                if($ruleData) break;
            }

            $parentDatas[$key]['rule_data'] = $ruleData ? $ruleData : null;

            $currentLevel--;
        }

        return $parentDatas;
    }

}
