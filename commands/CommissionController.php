<?php
namespace app\commands;

use app\commands\commission_action\RegionAction;
use app\commands\commission_action\RegionGoodsAction;
use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\area\models\AreaAgent;
use app\plugins\commission\models\CommissionRuleChain;
use app\plugins\commission\models\CommissionRules;
use yii\db\ActiveQuery;
use function EasyWeChat\Kernel\Support\generate_sign;

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
        $newParentDatas = [];
        for($i=0; $i < count($parentDatas); $i++){
            $parentData = $parentDatas[$i];
            $newParentDatas[] = $parentData;
        }

        $levelScore = ["user" => 0, "store" => 1, "partner" => 2, "branch_office" => 3];
        $parentDatas = [];
        for($i=0; $i < count($newParentDatas); $i++){
            $allowLevel = true;
            $score1 = $levelScore[$newParentDatas[$i]['role_type']];
            for($j=0;$j<count($parentDatas);$j++){
                $score2 = $levelScore[$parentDatas[$j]['role_type']];
                if($score1 < $score2){
                    $allowLevel = false;
                    break;
                }
            }
            if($allowLevel){
                $parentDatas[] = $newParentDatas[$i];
            }
        }

        $newParentDatas = [];
        $roleTypes = [];
        for($i=0; $i < count($parentDatas); $i++){
            $roleType = $parentDatas[$i]['role_type'];
            if(!isset($roleTypes[$roleType])){
                $roleTypes[$roleType] = 0;
            }

            if(in_array($roleType, ["partner", "store"]) && $roleTypes[$roleType] < 2){
                $newParentDatas[] = $parentDatas[$i];
                $roleTypes[$roleType]++;
            }elseif(in_array($roleType, ["branch_office"]) && $roleTypes[$roleType] < 1){
                $newParentDatas[] = $parentDatas[$i];
                $roleTypes[$roleType]++;
            }
        }

        $parentDatas = array_reverse($newParentDatas);

        return $parentDatas;
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
    public function getCommissionParentRuleDatas($user_id, $item_id, $item_type, $lianc_user_id = null,
            $is_commisson_price = null, $user_role_type = null, $enable_commisson_price = false){

        $newParentDatas = $this->getCommissionParents($user_id, $lianc_user_id);

        //如果是独立分销价，比消费用户级别低或同级别的都不分佣
        if($item_type == "goods" && $is_commisson_price){
            $newNewParentDatas = [];
            foreach($newParentDatas as $newParentData){
                if($user_role_type == "branch_office" && in_array($newParentData['role_type'], ["branch_office", "partner", "store", "user"])){
                    continue;
                }
                if($user_role_type == "partner" && in_array($newParentData['role_type'], ["partner", "store", "user"])){
                    continue;
                }
                if($user_role_type == "store" && in_array($newParentData['role_type'], ["store", "user"])){
                    continue;
                }
                if($user_role_type == "user" && in_array($newParentData['role_type'], ["user"])){
                    continue;
                }
                $newNewParentDatas[] = $newParentData;
            }
            $newParentDatas = $newNewParentDatas;
        }

        //生成相关规则键
        $generateRelKeys = function($index) use($newParentDatas, $user_role_type){
            if(!$newParentDatas)
                return [];
            $count = count($newParentDatas);
            $item = $newParentDatas[$index];
            $relKeys = [];
            for($i=$index;$i < $count; $i++){
                $relKeys[] = $newParentDatas[$i]['role_type'];
            }
            if($user_role_type != "user" && $index == ($count - 1)){
                if($item['role_type'] == "branch_office" && in_array($user_role_type, ["branch_office", "partner", "store"])){
                    $relKeys[] = $user_role_type;
                }elseif($item['role_type'] == "partner" && in_array($user_role_type, ["partner", "store"])){
                    $relKeys[] = $user_role_type;
                }elseif($item['role_type'] == "store" && in_array($user_role_type, ["store"])){
                    $relKeys[] = $user_role_type;
                }else{
                    $relKeys[] = "all";
                }
            }else{
                $relKeys[] = "all";
            }
            return $relKeys;
        };

        //获取独立规则
        $commissionRule = CommissionRules::findOne([
            "item_type"      => "goods",
            "item_id"        => $item_id,
            "apply_all_item" => 0,
            "is_delete"      => 0
        ]);
        if(!$commissionRule){
            $commissionRule = CommissionRules::findOne([
                "item_type"      => "goods",
                "item_id"        => 0,
                "apply_all_item" => 1,
                "is_delete"      => 0
            ]);
        }
        if($commissionRule){
            foreach($newParentDatas as $key => $newParentData){
                $relKey = implode("#", $generateRelKeys($key));
                $newParentDatas[$key]['rel_key'] = $relKey;
                if(empty($relKey)){
                    unset($newParentDatas[$key]);
                    continue;
                }

                $ruleChain = CommissionRuleChain::findOne([
                    "mall_id"    => $commissionRule->mall_id,
                    "rule_id"    => $commissionRule->id,
                    "unique_key" => $relKey
                ]);
                if($ruleChain){
                    $ruleData = [
                        "rule_id"         => $commissionRule->id,
                        "commission_type" => $commissionRule->commission_type,
                        "level"           => $ruleChain->level,
                        "commisson_value" => $ruleChain->commisson_value,
                        "rel_key"         => $relKey
                    ];
                }else{
                    $ruleData = null;
                }
                $newParentDatas[$key]['rule_data'] = $ruleData;
            }
        }

        foreach($newParentDatas as $key => $newParentData){
            $relKey = implode("#", $generateRelKeys($key));
            if(empty($relKey)){
                unset($newParentDatas[$key]);
                continue;
            }
        }

        return $newParentDatas;
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
