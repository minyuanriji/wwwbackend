<?php
namespace app\commands;

use app\commands\commission_action\RegionAction;
use app\commands\commission_action\RegionGoodsAction;
use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\area\models\AreaAgent;
use app\plugins\commission\models\CommissionRuleChain;
use yii\db\ActiveQuery;

class CommissionController extends BaseCommandController{

    const ERR_CODE_NOT_FOUND_PARENTS = 50001;

    public function actions(){
        return [
            "goods"              => "app\\commands\\commission_action\\GoodsAction",
            "checkout"           => "app\\commands\\commission_action\\CheckoutAction",
            "store"              => "app\\commands\\commission_action\\StoreAction",
            "hotel"              => "app\\commands\\commission_action\\HotelAction",
            "hotel3r"            => "app\\commands\\commission_action\\Hotel3rAction",
            "addcredit"          => "app\\commands\\commission_action\\AddcreditAction",
            "addcredit3r"        => "app\\commands\\commission_action\\Addcredit3rAction",
            "giftpacks"          => "app\\commands\\commission_action\\GiftpacksAction",
            "region"             => "app\\commands\\commission_action\\RegionAction",//门店二维码收款区域分红
            "regionGoods"        => "app\\commands\\commission_action\\RegionGoodsAction",//商品消费区域分红
            "smartShop"          => "app\\commands\\commission_action\\SmartShopOrderAction", //智慧门店推荐分佣
            "smartShop3r"        => "app\\commands\\commission_action\\SmartShopOrder3rAction",
            "smartShopCyorder"   => "app\\commands\\commission_action\\SmartShopCyorderAction", //智慧门店小程序订单推荐分佣
            "smartShopCyorder3r" => "app\\commands\\commission_action\\SmartShopCyorder3rAction",
        ];
    }
/*
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
    }*/


    /**
     * 计算利润
     * @param $order_price
     * @param $transfer_rate
     * @param $integral_fee_rate
     * @return mixed
     */
    public function calculateCheckoutOrderProfitPrice($order_price, $transfer_rate, $integral_fee_rate = 0){
        return max(0, $order_price * ($transfer_rate/100 - 0.1 + $integral_fee_rate/100) * 0.5);
    }

    /**
     * 获取上级信息
     * @param $user_id
     * @param $lianc_user_id 品牌商ID。如果消费用户是品牌商直推的，品牌商临时升级成分公司
     * @return array
     * @throws \Exception
     */
    public function getCommissionParents($user_id, $lianc_user_id = null){
        //获取支付用户信息
        $user = User::findOne($user_id);
        $userLink = UserRelationshipLink::findOne(["user_id" => $user_id]);
        if(!$user || !$userLink){
            throw new \Exception("支付用户[ID:".($user ? $user->id : 0)."]不存在或关系链异常");
        }

        $selects = ["u.id", "u.parent_id", "(u.income+u.income_frozen) as total_income", "u.role_type", "u.nickname"];
        $query = User::find()->alias("u")
            ->leftJoin("{{%user_relationship_link}} url", "url.user_id=u.id");
        $query->andWhere([
            "AND",
            ["u.is_delete" => 0],
            ["IN", "u.role_type", ["store", "partner", "branch_office"]],
            ("url.`left` < '".$userLink->left."' AND url.`right` > '".$userLink->right."'")
        ])->select($selects)->orderBy("url.`left` DESC");

        $parentDatas = [];
        $rows = $query->asArray()->all();
        if($rows){
            $parentDatas = array_merge($parentDatas, $rows);
        }

        //如果消费用户是品牌商直推的，品牌商临时升级成分公司
        if(!empty($lianc_user_id)){
            //先判断是否已经在列表中
            $isExists = false;
            foreach($parentDatas as $key => $parentData){
                if($parentData['id'] == $lianc_user_id){
                    $parentDatas[$key]['role_type'] = "branch_office";
                    $isExists = true;
                    break;
                }
            }
            //如果不存在说明是直推，在头部插入
            if(!$isExists){
                $parentData = User::find()->alias("u")->where(["u.id" => $lianc_user_id])->select($selects)->asArray()->one();
                if($parentData){
                    $parentData['role_type'] = "branch_office";
                    array_unshift($parentDatas, $parentData);
                }
            }
        }

        if(!$parentDatas){
            throw new \Exception("无法获取上级[ID:".$userLink->parent_id."]信息", self::ERR_CODE_NOT_FOUND_PARENTS);
        }

        //对获取的所有上级进行处理
        $existData = $newParentDatas = [];
        $partner2Data = null;
        unset($parentData);
        for($i=0; $i < count($parentDatas); $i++){
            $parentData = $parentDatas[$i];
            if(count($existData) >= 3) break;
            $appendNew = false;

            if(empty($partner2Data) && isset($existData['partner']) && $parentData['role_type'] == "partner"){
                if($existData['partner']['parent_id'] == $parentData['id']){
                    $partner2Data = $parentData;
                    continue;
                }
            }elseif($parentData['role_type'] == "store" && !isset($existData['store']) && !isset($existData['branch_office']) && !isset($existData['partner'])){
                $appendNew = true;
            }elseif($parentData['role_type'] == "partner" && !isset($existData['partner']) && !isset($existData['branch_office'])){
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
            $partner2Data['pingji'] = 1;
            $tmpDatas = [];
            foreach($newParentDatas as $parentData){
                if($parentData['role_type'] == "partner"){
                    $tmpDatas[] = $partner2Data;
                }
                $tmpDatas[] = $parentData;
            }
            $newParentDatas = $tmpDatas;
        }

        return $newParentDatas;
    }

    /**
     * 获取要分佣的父级列表
     * @param $user_id
     * @param $item_id
     * @param $item_type
     * @param $lianc_user_id 品牌商ID。如果消费用户是品牌商直推的，品牌商临时升级成分公司
     * @return array
     * @throws \Exception
     */
    public function getCommissionParentRuleDatas($user_id, $item_id, $item_type, $lianc_user_id = null, $is_commisson_price = null, $user_role_type = null){

        $newParentDatas = $this->getCommissionParents($user_id, $lianc_user_id);

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

            //独立设置规则
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
            $query->select(["cr.id as rule_id", "cr.commission_type", "crc.level", "crc.commisson_value"]);
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

        //如果是独立分销价，同级别上级不进行分佣
        if($item_type == "goods" && $is_commisson_price){
            $newParentDatas = [];
            foreach($parentDatas as $parentData){
                if($parentData['role_type'] != $user_role_type){
                    $newParentDatas[] = $parentData;
                }
            }
            $parentDatas = $newParentDatas;
        }

        return $parentDatas;
    }

    /* *
     * 获取区域分红符合条件的人
     * */
    public function getRegion ($mall_id, $province_id, $city_id, $district_id)
    {
        $AreaAgent = AreaAgent::find()->where(['mall_id' => $mall_id, 'is_delete' => 0])
            ->andWhere([
                'or',
                [
                    'and',
                    ['province_id' => $province_id],
                    ['level' => 4],
                ],
                [
                    'and',
                    ['city_id' => $city_id],
                    ['level' => 3],
                ],
                [
                    'and',
                    ['district_id' => $district_id],
                    ['level' => 2],
                ],
            ])->orderBy('level DESC')->asArray()->all();

        return $AreaAgent;
    }

}
