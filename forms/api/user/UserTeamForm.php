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
use app\forms\common\UserRelationshipLinkForm;
use app\logic\UserLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\CommonOrder;
use app\models\MemberLevel;
use app\models\User;
use app\models\UserChildren;
use app\models\PriceLog;
use app\models\UserRelationshipLink;
use app\plugins\commission\models\CommissionGoodsPriceLog;

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
        $result = [
            'team_level' => [],
            'team_commission' => [
                'direct_push_total' => 0,
                'space_push_total'  => 0, //团队数
                'team_order_count'  => 0, //团队订单
                'team_order_total'  => 0  //订单金额
            ]
        ];

        //获取用户
        $user = User::findOne(\Yii::$app->user->id);
        $userLink = UserRelationshipLink::findOne(["user_id" => $user->id]);

        //用户直推统计
        $result['team_commission']['direct_push_total'] = UserRelationshipLinkForm::countDirectPush($user, $userLink);

        //用户团队统计
        $result['team_commission']['space_push_total'] = UserRelationshipLinkForm::countUserTeam($user, $userLink);

        //团队订单
        $result['team_commission']['team_order_count'] = UserRelationshipLinkForm::countUserTeamOrder($user, $userLink);

        //团队订单
        $result['team_commission']['team_order_total'] = UserRelationshipLinkForm::countUserTeamOrderTotoal($user, $userLink);

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

        //获取用户
        $user = User::findOne(\Yii::$app->user->id);
        $userLink = UserRelationshipLink::findOne(["user_id" => $user->id]);

        if ($this->flag == 1) { //直推
            $query = UserRelationshipLinkForm::getDirectListQuery($user, $userLink);
        }else{ //间推
            $query = UserRelationshipLinkForm::getSecondList($user, $userLink);
        }
        $select = ["u.id", "u.role_type", "u.avatar_url", "u.nickname", "u.mobile", "u.junior_at", "u.created_at"];
        $users = $query->orderBy("u.id DESC")->select($select)->page($pagination, 10, max(1, $this->page))->all();
        $list = [];
        if($users){
            foreach($users as $user){
                $item = [
                    'id'            => $user->id,
                    'user_id'       => $user->id,
                    'children'  => [
                        'avatar_url' => $user->avatar_url,
                        'id'         => $user->id,
                        'junior_at'  => $user->junior_at,
                        'mobile'     => $user->mobile,
                        'nickname'   => $user->nickname
                    ],
                    'created_at'    => date("Y-m-d H:i:s", $user->created_at),
                ];

                //订单数量
                $item['order_count'] = (int)Order::find()->where([
                    "user_id"    => $user->id,
                    "is_pay"     => 1,
                    "is_delete"  => 0,
                    "is_recycle" => 0
                ])->count();

                //订单金额
                $item['total_price'] = round((float)Order::find()->where([
                    "user_id"    => $user->id,
                    "is_pay"     => 1,
                    "is_delete"  => 0,
                    "is_recycle" => 0
                ])->sum("total_goods_original_price"), 2);

                //团队数量
                $userLink = UserRelationshipLink::findOne(["user_id" => $user->id]);
                $teamQuery = UserRelationshipLinkForm::userTeamQuery($user, $userLink);
                $item['team_user_count'] = (int)$teamQuery->count();

                //团队订单金额
                $item['team_total_price'] = round((float)Order::find()->andWhere([
                    "AND",
                    ["is_pay" => 1],
                    ["is_delete" => 0],
                    ["is_recycle" => 0],
                    ["IN", "user_id", $teamQuery->select(["ut.id"])]
                ])->sum("total_goods_original_price"), 2);

                $list[] = $item;
            }
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
            'list'       => $list,
            'pagination' => $pagination
        ]);
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

        //获取用户
        $user = User::findOne(\Yii::$app->user->id);
        $userLink = UserRelationshipLink::findOne(["user_id" => $user->id]);


        $query = CommissionGoodsPriceLog::find()->alias("cgpl");
        $query->innerJoin(["o" => Order::tableName()], "o.id=cgpl.order_id");
        $query->innerJoin(["u" => User::tableName()], "u.id=o.user_id");
        $query->groupBy("o.order_no");

        $query->andWhere([
            "AND",
            ["cgpl.user_id" => $user->id],
            /*["o.is_delete" => 0],
            ["o.is_recycle" => 0]*/
         ]);

        if(is_numeric($this->status)){
            if($this->status == 0){
                $query->andWhere(["o.status" => 0]);
            }elseif($this->status == 1){
                $query->andWhere(["o.status" => 1]);
            }elseif($this->status == 2){
                $query->andWhere(["o.status" => 2]);
            }
        }
        $select = ['u.nickname', 'u.avatar_url', 'u.mobile', 'u.id as uid',  'o.order_no', 'o.status', 'o.created_at'];
        $select[] = "sum(cgpl.price) AS price";

        $list = $query->orderBy("cgpl.id DESC")->select($select)->page($pagination, 10, $this->page)->asArray()->all();

        if($list){
            $order = new Order();
            foreach ($list as &$item) {
                $item['status_text'] = $order->orderStatusText($item);
                $item['created_at']  = date('Y-m-d H:i:s',$item['created_at']);
                $item['price']       = round((float)$item['price'], 2);
            }
        }

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, ['list' => $list, 'pagination' => $pagination]);
    }
}
