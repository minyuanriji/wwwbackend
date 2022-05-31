<?php

namespace app\plugins\perform_distribution\models;


use app\models\BaseActiveRecord;
use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserRelationshipLink;

class PerformDistributionGoods extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_perform_distribution_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at'], 'required'],
            [['goods_id', 'is_delete', 'award_type'], 'integer'],
            [['award_rules'], 'trim']
        ];
    }

    /**
     * 获取商品数据对象
     * @return \yii\db\ActiveQuery
     */
    public function getGoods(){
        return $this->hasOne(Goods::class, ["id" => "goods_id"]);
    }

    /**
     * 获取到业绩奖励信息
     * @param Order $order
     * @param OrderDetail $orderDetail
     * @return array
     */
    public function getAwardInfo(Order $order, OrderDetail $orderDetail){

        $awardInfo = [
            "award_type"  => $this->award_type, //0按比例，1按固定值
            "award_rules" => $this->award_rules ? json_decode($this->award_rules, true) : [],
            "award_users" => []
        ];

        $goods = $this->goods; //获取商品
        $payUser = User::findOne($order->user_id); //获取支付用户
        if(!$payUser || !$goods){
            return $awardInfo;
        }

        //记录商品名称
        $awardInfo['goods_name'] = $goods->goodsWarehouse->name;

        //获取同区域的所有上级
        $awardParentUser = PerformDistributionUser::findOne([
            "user_id" => $payUser->parent_id,
            "mall_id" => $payUser->mall_id
        ]);

        //区域信息
        $region = PerformDistributionRegion::findOne([
            "mall_id" => $payUser->mall_id,
            "id"      => $awardParentUser ? $awardParentUser->region_id : 0
        ]);

        $parentReLink = UserRelationshipLink::findOne(["user_id" => $payUser->parent_id]);
        if($region && $awardParentUser && $parentReLink){
            $rows = UserRelationshipLink::find()->alias("url")
                ->innerJoin(["u" => User::tableName()], "u.id=url.user_id")
                ->innerJoin(["pdu" => PerformDistributionUser::tableName()], "pdu.user_id=url.user_id AND pdu.is_delete=0")
                ->andWhere([
                    "AND",
                    ["pdu.region_id" => $awardParentUser->region_id],
                    ["<=", "url.left", $parentReLink->left],
                    [">=", "url.right", $parentReLink->right],
                ])->asArray()->select(["u.nickname", "u.mobile", "pdu.level_id", "pdu.user_id"])->all();
            if($rows){
                foreach($rows as $key => $row){
                    foreach($awardInfo['award_rules'] as $rule){
                        if($rule['id'] == $row['level_id']){
                            $row['rule'] = $rule;
                            $awardInfo['award_users'][$row['user_id']] = $row;
                            break;
                        }
                    }
                }
            }

            //记录区域名称
            $awardInfo['region_name'] = $region->name;

            //计算每个奖励人员可获得的奖励
            $profitPrice = floatval($goods->profit_price) * intval($orderDetail->num); //商品利润
            $awardInfo['profit_price'] = $profitPrice;
            foreach($awardInfo['award_users'] as $key => $awardUser){
                if($awardInfo['award_type'] == 0){ //按比例
                    $awardInfo['award_users'][$key]['price'] = $profitPrice * floatval($awardUser['rule']['value']/100);
                }else{ //按固定值
                    $awardInfo['award_users'][$key]['price'] = min($awardUser['rule']['value'], $profitPrice);
                    $profitPrice -= $awardInfo['award_users'][$key]['price'];
                }
            }
        }

        return $awardInfo;
    }
}
