<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 接口-用户团队model类
 * Author: zal
 * Date: 2020-06-22
 * Time: 11:16
 */

namespace app\forms\api\user;

use app\core\ApiCode;
use app\core\BasePagination;
use app\logic\UserLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\CommonOrder;
use app\models\MemberLevel;
use app\models\User;
use app\models\UserChildren;
use app\models\PriceLog;

class UserTeamForm extends BaseModel
{
    public $page;
    public $limit;
    //插件标识
    public $sign;
    //1直推2间推
    public $flag;
    //分佣订单状态
    public $status='';

    public function rules()
    {
        return [
            [['page', 'limit', 'flag','status'], 'integer'],
            [['sign'], 'string'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
        ];
    }

    /**
     * 我的团队数据
     * @return array
     */
    public function getMyTeamData()
    {
        $user_id = \Yii::$app->user->id;
        $teamAllData = UserLogic::getUserTeamAllData($user_id, "");
        $teamList = $teamAllData["child_list"];
        $result = [
            //团队成员
            'team_level' => $this->getUserTeamLevelTotal($teamList),
            //团队佣金
            'team_commission' => $this->getUserTeamCommissionTotal($teamAllData),
        ];
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '请求成功', $result);
    }

    /**
     * 不同会员等级团队成员数
     * @param $teamList
     * @Author: zal
     * @Date: 2020-06-22
     * @Time: 14:33
     * @return array
     */
    public function getUserTeamLevelTotal($teamList)
    {
        if (\Yii::$app->user->isGuest) {
            return null;
        }
        $user_id = \Yii::$app->user->id;
        $returnData = $data = [];
        $userTeamLevelList = UserLogic::getUserTeamLevelTotal($teamList);
        //组装默认等级统计数
        $data[0]["id"] = -1;
        $data[0]["pic_url"] = "";
        $data[0]["name"] = "普通会员";
        $data[0]["level"] = -1;
        $data[0]["total"] = isset($userTeamLevelList[-1]) ? count($userTeamLevelList[-1]) : 0;
        $memberLevels = MemberLevel::find()->where(["mall_id" => \Yii::$app->mall->id, "status" => MemberLevel::YES, "is_delete" => MemberLevel::NO])->select("id,pic_url,name,level")
            ->asArray()->all();
        foreach ($memberLevels as $val) {
            $level = $val["level"];
            $val["total"] = isset($userTeamLevelList[$level]) ? count($userTeamLevelList[$level]) : 0;
            $returnData[] = $val;
        }

        $userTeamLevelData = array_merge($data, $returnData);

        return $userTeamLevelData;
    }

    /**
     * 团队佣金
     * @param $teamAllData
     * @Author: zal
     * @Date: 2020-06-22
     * @Time: 15:33
     * @return array
     */
    public function getUserTeamCommissionTotal($teamAllData)
    {
        if (\Yii::$app->user->isGuest) {
            return null;
        }
        $user_id = \Yii::$app->user->id;
        //获取间推或直推数据
        $userStatData = UserLogic::getStatUserPushTotal(["user_id" => $user_id]);
        //
        //$teamUserIds = self::getMyTeamIds($teamAllData);
        $child_list = $teamAllData["child_list"];
        $teamUserIds = [];
        if (!empty($child_list)) {
            foreach ($child_list as $value) {
                $teamUserIds[] = $value["id"];
            }
        }
        $orderStatData = UserLogic::getUserTeamOrderStatInfo($teamUserIds,["is_pay" => 1]);
        //var_dump($teamUserIds);exit;
        $returnData = array_merge($userStatData,$orderStatData);
        return $returnData;
    }

    /**
     * 我的团队成员列表
     * @return array
     */
    public function getMyTeamList()
    {
        $returnData = $data = [];
        $list = UserLogic::getUserTeamPushList($this->attributes);
        $commonOrderModel = new CommonOrder();
        $commonOrderModel->mall_id = \Yii::$app->mall->id;
        if (!empty($list["list"])) {
            foreach ($list["list"] as $item) {
                $user = $item["user"];
                $data["id"] = $user["id"];
                $data["avatar_url"] = $user["avatar_url"];
                $data["nickname"] = $user["nickname"];
                $data["mobile"] = $user["mobile"];
                $data["created_at"] = date("Y-m-d H:i:s", $user["created_at"]);
                $commonOrderModel->user_id = \Yii::$app->user->id;
                $count = $commonOrderModel->getList(["user_id" => $user["id"]], "count");//所有订单
                $total = $commonOrderModel->getList(["user_id" => $user["id"]], "sum");//所有订单总额
                //$pay_count =  $commonOrderModel->getList(["user_id" => $user["id"],"is_pay"=>1], "count");//所有支付订单
                //$pay_total = $commonOrderModel->getList(["user_id" => $user["id"],"is_pay"=>1], "sum");//所有支付订单总额
                $data["order_count"] = empty($count) ? 0 : $count;
                $data["order_total"] = empty($total) ? 0 : $total;
                //$data["order_pay_count"] = empty($pay_count) ? 0 : $pay_count;
                //$data["order_pay_total"] = empty($pay_total) ? 0 : $pay_total;
                $teamData = UserLogic::getUserTeamAllData($user["id"]);
                $data["team_count"] = count($teamData["child_list"]);
                $team_total = $commonOrderModel->getList(["more_user_id" => $teamData["child_list"]], "sum");
                $data["team_total"] = empty($team_total) ? 0 : floatval($team_total);
                $returnData[] = $data;
            }
        }
        $result = ["list" => $returnData, "pagination" => $list["pagination"]];
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '请求成功', $result);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-01
     * @Time: 16:53
     * @Note:获取团队列表
     */
    public function getTeamList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $query = UserChildren::find()->where(['user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0]);
        if ($this->flag == 1) {
            $query->andWhere(['level' => 1]);
        }
        if ($this->flag == 2) {
            $query->andWhere(['>', 'level', 1]);
        }
        /**
         * @var BasePagination $pagination
         */
        $list = $query->with(['children' => function ($query) {
            $query->select('id, nickname, avatar_url, junior_at, mobile');
        }])->page($pagination, 10, $this->page)->orderBy(['id'=>SORT_DESC])->asArray()->all();
        foreach($list as $key => $item){
            if(empty($item['children'])){
                unset($list[$key]);
            }
        }

        $list = array_values($list);
        
        foreach ($list as &$item) {
            $query = CommonOrder::find()->alias('o')
                ->leftJoin(['uc' => UserChildren::tableName()], 'uc.child_id=o.user_id')
                ->andWhere(['uc.user_id' => $item['child_id'], 'uc.is_delete' => 0])
                ->andWhere(['o.is_pay' => 1]);
            
            $team_order_count = $query->count();
            $item['team_order_count'] = $team_order_count ?? 0;
            $team_total_price = $query->sum('o.pay_price');
            $item['team_total_price'] = $team_total_price ?? '0.00';
            $query = CommonOrder::find()->alias('o')
                ->andWhere(['o.user_id' => $item['child_id'], 'o.is_delete' => 0, 'o.is_pay' => 1]);
            $order_count = $query->count();
            $item['order_count'] = $order_count ?? 0;
            $total_price = $query->sum('o.pay_price');
            $total_price = $total_price ?? '0.00';
            $item['total_price'] = $total_price;
            $item['team_order_count'] += $order_count;
            $team_total_price = $item['team_total_price'] + $total_price;
            $item['team_total_price'] = number_format($team_total_price,2);
            $team_user_count = UserChildren::find()->where(['user_id' => $item['child_id'], 'is_delete' => 0])->count();
            $item['team_user_count'] = $team_user_count ?? '0';
            $item['created_at']=date('Y-m-d H:i:s',$item['created_at']);
            $item['avatar_url'] = !empty($item['avatar_url']) ? $item['avatar_url'] : "http://";
           
        }
        
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, ['list' => $list, 'pagination' => $pagination]);
    }


    /**
     * 我的团队成员列表id
     * @param $teamAllData
     * @return array 成员id数组
     */
    private static function getMyTeamIds($teamAllData)
    {
        $parentData = $childData = [];
        $parentList = $teamAllData["parent_list"];
        $child_list = $teamAllData["child_list"];
        if (!empty($parentList)) {
            foreach ($parentList as $value) {
                $parentData[] = $value["id"];
            }
        }

        if (!empty($child_list)) {
            foreach ($child_list as $value) {
                $childData[] = $value["id"];
            }
        }
        $data = array_merge($parentData, $childData);
        return $data;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-01
     * @Time: 16:53
     * @Note:获取团队订单分佣列表
     */
    public function getTeamOrderList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        
        $query = UserChildren::find()->alias('uc')->where(['uc.user_id' => \Yii::$app->user->identity->id, 'uc.is_delete' => 0])
            ->select('u.nickname,u.avatar_url,u.mobile,u.id as uid,pl.price,pl.common_order_detail_id,o.order_no,o.status,o.created_at')
            ->leftJoin(['u' => User::tableName()], 'u.id=uc.child_id')
            ->leftJoin(['co' => CommonOrder::tableName()], 'co.user_id=u.id and co.is_delete=0')
            ->leftJoin(['o' => Order::tableName()], 'o.id=co.order_id')
            ->leftJoin(['pl' => PriceLog::tableName()], 'pl.user_id=\''.\Yii::$app->user->identity->id.'\' and pl.order_id=co.order_id') -> orderBy('created_at DESC');


        if ($this->status>=0 && $this->status<=2) {
            $query->andWhere(['co.status' => $this->status]);
        }
        $list = $query->page($pagination, 10, $this->page)->asArray()->all();
        if($list){
            $order = new Order();
            foreach ($list as &$item) {
                $item['status_text'] = $order->orderStatusText($item);
                $item['created_at'] = date('Y-m-d H:i:s',$item['created_at']);
                  if(!empty($item['price'])){
                    $item['price'] = mb_substr($item['price'],0,strpos($item['price'],'.')) . substr($item['price'],strpos($item['price'],'.'),3);
                }
            }
        }
        //print_r(debug_backtrace());
        //exit;
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, ['list' => $list, 'pagination' => $pagination]);
    }
}
