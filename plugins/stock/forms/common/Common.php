<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 经销插件-处理经销分佣公共类
 * Author: zal
 * Date: 2020-05-29
 * Time: 18:10
 */

namespace app\plugins\stock\forms\common;

use app\core\BasePagination;
use app\helpers\SerializeHelper;
use app\helpers\sms\Sms;
use app\logic\OptionLogic;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\MallSetting;
use app\models\Option;
use app\models\OrderDetail;
use app\models\PriceLog;
use app\models\User;
use app\models\UserChildren;
use app\models\UserParent;
use app\plugins\stock\helpers\StockFillMessage;
use app\plugins\stock\models\FillIncomeLog;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\FillPriceLog;
use app\plugins\stock\models\GoodsPriceLog;
use app\plugins\stock\models\Stock;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockOrder;
use app\plugins\stock\models\StockPriceLog;
use app\plugins\stock\models\StockPriceLogType;
use app\plugins\stock\models\StockSetting;
use app\plugins\stock\Plugin;
use app\models\BaseModel;
use function Couchbase\defaultDecoder;

class Common extends BaseModel
{
    public $config;
    public $sign;
    public static $instance;
    public $mall;


    /**
     * 获取公共类
     * @param $mall
     * @return Common
     * @throws \Exception
     */
    public static function getCommon($mall)
    {
        if (self::$instance) {
            return self::$instance;
        }
        $form = new Common();
        $form->mall = $mall;
        self::$instance = $form;
        return $form;
    }

    /**
     * 获取配置
     * @return StockSetting|null
     */
    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }
        $config = StockSetting::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$config) {
            $config = new StockSetting();
            $config->mall_id = $this->mall->id;
        }
        $this->config = $config;

        return $config;
    }

    /**
     * 获取经销用户信息
     * @param $user
     * @return array|null
     */
    public function getAgentInfo($user)
    {
        if (!$user) {
            return null;
        }
        $plugin = new Plugin();
        $sign = $plugin->getName();
        if (!$sign) {
            $this->sign = 'mall';
        } else {
            $this->sign = $sign;
        }
        $returnData = [];
        $month_price = 0;
        $week_price = 0;
        $frozen_price = 0;
        $yesterday_price = 0;
        /**
         * @var StockAgent $agent
         */
        $agent = $this->getAgentUser($user);
        $level_name = '普通用户';
        $is_price = 0;
        if($user){
            $returnData['nickname'] = $user->nickname;
            $returnData['avatar_url'] =$user->avatar_url;
            $returnData['mobile'] = $user->mobile;
            $returnData['user_id'] = $user->id;
        }
        $returnData["is_agent"] = 0;
        if (!empty($agent)) {
            $stock_level = StockLevel::findOne(['level' => $agent->level, 'mall_id' => $agent->mall_id, 'is_delete' => 0, 'is_use' => 1]);
            $total_price = $agent->total_price;
            $level_name = $stock_level ? $stock_level->name : "默认等级";
            $frozen_price = floatval($agent->frozen_price);
            $is_price = $this->getPrice(0, 0, $user->id);

            //昨日收益
            $yesterday = date("Y-m-d", strtotime("-1 day"));
            $begin_at = strtotime($yesterday . " 00:00:00");
            $end_at = strtotime($yesterday . " 23:59:59");
            $yesterday_price = $this->getPrice($begin_at, $end_at, $user->id);
            //本周
            $begin_at = strtotime(date('Y-m-d 00:00:01', (time() - ((date('w', time()) == 0 ? 7 : date('w', time())) - 1) * 24 * 3600)));
            $end_at = $begin_at + 7 * 24 * 60 * 60;
            $week_price = $this->getPrice($begin_at, $end_at, $user->id);
            $begin_at = strtotime(date('Y-m-01 00:00:01', time()));
            $end_at = time();
            $month_price = $this->getPrice($begin_at, $end_at, $user->id);
            $returnData["is_parent"] = 0;
            $returnData["is_agent"] = 1;
            $returnData['parent_name'] = '平台';
            $returnData['parent_avatar_url'] = MallSetting::getValueByKey('logo', \Yii::$app->mall->id);
            $returnData['mobile'] = MallSetting::getValueByKey('contact_tel', \Yii::$app->mall->id);
            $returnData['parent_level_name'] = '平台方';
        }
        $user_parent = UserParent::find()
            ->alias('uc')
            ->where(['uc.user_id' => \Yii::$app->user->identity->id, 'uc.is_delete' => 0])
            ->leftJoin(['a' => StockAgent::tableName()], 'a.user_id=uc.parent_id')
            ->andWhere(['a.is_delete' => 0])
            ->orderBy('uc.level ASC')->asArray()->one();
        if ($user_parent) {
            $info['is_parent'] = 1;
            $user = User::findOne($user_parent['parent_id']);
            if ($user) {
                $parent_agent['nickname'] = $user->nickname;
                $parent_agent['avatar_url'] = $user->avatar_url;
                $agent = StockAgent::findOne(['user_id' => $user->id, 'is_delete' => 0]);
                if ($agent) {
                    $level = StockLevel::findOne(['level' => $agent->level, 'mall_id' => \Yii::$app->mall->id]);
                    if (!$level) {
                        $parent_agent['level_name'] = '普通代理商';
                    } else {
                        $parent_agent['level_name'] = $level->name;
                    }
                }
                $returnData['parent_agent'] = $parent_agent;
            }
        }
        $parent = User::findOne($user->parent_id);
        if ($parent) {
            $returnData["parent_nickname"] = $parent->nickname;
            $returnData["parent_avatar"] = $parent->avatar_url;
            $returnData["parent_mobile"] = $parent->mobile;
            $returnData["parent_id"] = $parent->id;
            $returnData["is_parent"] = 1;
        } else {
            $returnData["is_parent"] = 0;
        }
        $level_list = StockLevel::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'is_use' => 1])->asArray()->all();
        foreach ($level_list as &$level) {
               $user_count=UserChildren::find()->alias('uc')
                   ->leftJoin(['sa'=>StockAgent::tableName()],'sa.user_id=uc.child_id')
                   ->andWhere(['sa.level'=>$level['level'],'sa.is_delete'=>0,'uc.is_delete'=>0,'uc.user_id'=>$user->id])
                   ->count();
               $level['count']=$user_count??0;
        }
        $returnData["total_price"] = floatval($is_price);
        $returnData["yesterday_price"] = $yesterday_price ?? '0.00';
        $returnData["month_price"] = $month_price ?? '0.00';
        $returnData["week_price"] = $week_price ?? '0.00';
        $returnData["level_name"] = $level_name;
        $returnData["frozen_price"] = $frozen_price ?? '0.00';
        $returnData['level_list']=$level_list;
        return $returnData;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-01
     * @Time: 18:16
     * @Note:
     * @param int $begin_at
     * @param int $end_at
     * @param $user_id
     * @return int|mixed
     */
    private function getPrice($begin_at = 0, $end_at = 0, $user_id)
    {

        $price = 0;
        if ($begin_at == 0) {
//            $price1 = FillPriceLog::find()->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user_id, 'is_price' => 1])
//                ->sum("income");
            $price1 = GoodsPriceLog::find()
                ->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user_id])
                ->andWhere(['between', "created_at", $begin_at, $end_at])->sum("price");
            $price1 = $price1 ? $price1 : 0;
            $price2 = FillIncomeLog::find()->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user_id])->sum("price");
            $price2 = $price2 ? $price2 : 0;
            $price3 = StockPriceLog::find()->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user_id])->sum("income");
            $price3 = $price3 ? $price3 : 0;
            $price4 = FillOrder::find()->alias('fo')
                ->leftJoin(['od' => FillOrderDetail::tableName()], 'od.order_id=fo.id')
                ->where(["fo.mall_id" => \Yii::$app->mall->id, "fo.user_id" => $user_id, 'od.is_give' => 1])
                ->sum("od.fill_price");
            $price4 = $price4 ? $price4 : 0;
            $price = $price1 + $price2 + $price3 + $price4;
        } else {
            //其实没用，拿的是货款收益，货款收益对应的表FillIncomeLog中type=0的记录
//            $price1 = FillPriceLog::find()
//                ->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user_id, 'is_price' => 1])
//                ->andWhere(['between', "created_at", $begin_at, $end_at])->sum("income");
            $price1 = GoodsPriceLog::find()
                ->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user_id])
                ->andWhere(['between', "created_at", $begin_at, $end_at])->sum("price");
            $price1 = $price1 ? $price1 : 0;
            //所有收益（货款，平级，越级）
            $price2 = FillIncomeLog::find()
                ->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user_id])
                ->andWhere(['between', "created_at", $begin_at, $end_at])->sum("price");
            $price2 = $price2 ? $price2 : 0;
            //云库存结算佣金
            $price3 = StockPriceLog::find()
                ->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user_id])
                ->andWhere(['between', "created_at", $begin_at, $end_at])->sum("income");
            $price3 = $price3 ? $price3 : 0;
            //补货奖励
            $price4 = FillOrder::find()->alias('fo')
                ->leftJoin(['od' => FillOrderDetail::tableName()], 'od.order_id=fo.id')
                ->where(["fo.mall_id" => \Yii::$app->mall->id, "fo.user_id" => $user_id, 'od.is_give' => 1])
                ->andWhere(['between', "fo.created_at", $begin_at, $end_at])
                ->sum("od.fill_price");

            $price4 = $price4 ? $price4 : 0;
            $price = $price1 + $price2 + $price3 + $price4;
        }
        return $price;
    }

    /**
     * 获取经销用户信息
     * @param $user
     * @return StockAgent|null
     */
    public function getAgentUser($user)
    {
        if (empty($user)) {
            return null;
        }
        $agent = StockAgent::findOne(['user_id' => $user->id, 'mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$agent) {
            return null;
        }
        return $agent;
    }

    /**
     * 获取经销记录列表
     * @param $user
     * @param $params
     * @return array
     */
    public function getAgentLogList($user, $params)
    {
        $plugin = new Plugin();
        $sign = $plugin->getName();
        if (!$sign) {
            $this->sign = 'mall';
        } else {
            $this->sign = $sign;
        }

        $is_price = isset($params['is_price']) ? $params['is_price'] : -1;//分佣状态
        $page = isset($params["page"]) ? $params["page"] : 1;//经销层级
        $query = PriceLog::find()
            ->alias('l')
            ->leftJoin(['u' => User::tableName()], 'u.id=l.child_id')
            ->where([
                'l.sign' => $this->sign,
                'l.mall_id' => \Yii::$app->mall->id,
                'l.is_delete' => 0
            ]);
        if ($user) {
            $query->andWhere(['l.user_id' => $user->id]);
        }
        if ($is_price != -1) {
            if ($is_price == 0) {
                $query->andWhere(['l.is_price' => 0]); //未结算
            }
            if ($is_price == 1) {
                $query->andWhere(['l.is_price' => 1]); //已结算
            }
        }

        $query = $query
            ->orderBy(['l.created_at' => SORT_ASC]);

        if ($page) {
            /**
             * @var BasePagination $pagination
             */
            $query = $query->page($pagination, 20, $page);
        }
        $list = $query->select('l.*,u.avatar_url,u.nickname')->orderBy('l.created_at DESC')->asArray()->all();
        foreach ($list as &$item) {
            $common_order_detail = CommonOrderDetail::findOne(['is_delete' => 0, 'id' => $item['common_order_detail_id']]);
            if (!$common_order_detail) {
                $item['goods_name'] = '未知商品';
                continue;
            } else {
                $item['goods_price'] = $common_order_detail->price;
                //需要加入商品类型
                $goods = Goods::findOne($common_order_detail->goods_id);
                if (!$goods) {
                    $item['goods_name'] = '未知商品';
                } else {
                    $item['goods_name'] = $goods->name;
                }
            }
            if ($item['status'] == 0) {
                $item['status'] = '待结算';
            }
            if ($item['status'] == 1) {
                $item['status'] = '已结算';
            }
            if ($item['status'] == -1) {
                $item['status'] = '无效';
            }
            $price_type = StockPriceLogType::findOne(['price_log_id' => $item['id']]);
            if ($price_type) {
                $price_type_list = StockPriceLogType::PRICE_TYPE;
                $item['price_type'] = $price_type_list[$price_type->type];
            } else {
                $item['price_type'] = '未知奖项';

            }
            if ($item['is_price'] == 1) {
                $item['is_price'] = '已发放';
            } else {
                $item['is_price'] = '未发放';
            }
            $item['order_no'] = $common_order_detail->order_no;
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
        }
        unset($item);
        $returnData['list'] = $list;
        $returnData["pagination"] = $pagination;
        return $returnData;
    }

    /**
     * 获取经销订单列表
     * @param $order
     * @return array
     */
    public function getAgentOrderList($order)
    {
        $order_id = $order["id"];
        $query = StockOrder::find()->where(["mall_id" => \Yii::$app->mall->id, "order_id" => $order_id]);
        $query = $query->orderBy("id desc");
        $list = $query->with("order")->all();
        return $list;
    }

    /**
     * 更新商品库存（//因为一下单就扣了平台库存，所以这里需要把库存还回去）
     * @param $goodsId
     * @param $orderDetailId
     * @param $num
     * @param $outIn 1增加2减少
     * @return bool
     * @throws \Exception
     */
    public static function updateGoodsStock($goodsId,$orderDetailId,$num,$outIn = 1){
        //sub：减库存，add：加库存
        $type = "sub";
        if($outIn == 1){
            $type = "add";
        }
        $orderDetailInfo = OrderDetail::findOne($orderDetailId);
        if(!empty($orderDetailInfo)){
            $goods_info = $orderDetailInfo->goods_info;
            $goodsInfo = $orderDetailInfo->decodeGoodsInfo($goods_info);
            $attr_id = isset($goodsInfo["goods_attr"]["id"]) ? $goodsInfo["goods_attr"]["id"] : 0;
            $goodsAttr = GoodsAttr::findOne($attr_id);
            \Yii::warning("stock common updateGoodsStock goodsAttr=".var_export($goodsAttr,true));
        }else{
            /** @var GoodsAttr $goodsAttr */
            $goodsAttr = GoodsAttr::find()->where(["goods_id" => $goodsId,"is_delete" => GoodsAttr::IS_DELETE_NO])->one();
            \Yii::warning("stock common updateGoodsStock else goodsAttr=".var_export($goodsAttr,true));
        }

        if(!empty($goodsAttr)){
            //检查库存是否充足
            if ($num > $goodsAttr->stock) {
                \Yii::error('updateGoodsStock 商品:' . $goodsAttr->goods_id . '库存不足! ');
                throw new \Exception('stock common updateGoodsStock 商品:' . $goodsAttr->goods_id . '库存不足! ');
            }
            //补货直接扣除平台库存
            $result  = (new GoodsAttr())->updateStock($num, $type, $goodsAttr->id);
            if(!$result){
                \Yii::error("stock common updateGoodsStock 商品规格id={$goodsAttr->id}更新失败");
                throw new \Exception("stock common updateGoodsStock 商品规格id={$goodsAttr->id}更新失败");
            }
        }
        return true;
    }

    /**
     * 发送补货通知
     * @param $stockUserId
     * @param $tempFillTime
     * @param $goodsId
     * @param $remainNum
     * @param $userId
     * @return bool
     * @throws \Overtrue\EasySms\Exceptions\NoGatewayAvailableException
     */
    public static function sendFillNoticeSms($stockUserId,$tempFillTime,$goodsId,$remainNum,$userId){
        $res = OptionLogic::get(
            Option::NAME_SMS,
            \Yii::$app->mall->id,
            Option::GROUP_ADMIN
        );
        if(!$res || $res['status'] == 0) {
            \Yii::error("sendFillNoticeSms sms 验证码功能未开启");
            return false;
        }
        $users = User::findOne($stockUserId);
        $mobile = "";
        if(!empty($users)){
            $mobile = $users->mobile ? $users->mobile : "";
        }
        $sms = new Sms();
        \Yii::warning("sendFillNoticeSms mobile = {$mobile}");
        $fiiSms = StockSetting::getValueByKey(StockSetting::FILL_SMS, \Yii::$app->mall->id);
        if(empty($fiiSms)){
            \Yii::error("sendFillNoticeSms sms 缺少相应配置参数");
            return false;
        }
        $fillSms = json_decode($fiiSms,true);
        $message = new StockFillMessage($goodsId,$remainNum, $tempFillTime,$fillSms);
        $content = $message->getContent();
        \Yii::warning("sendFillNoticeSms content={$content}");
        $plugin = new Plugin();
        return $plugin->sendSms($mobile,$message,$sms,$content);
    }
}
