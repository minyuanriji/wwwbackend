<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 接口-用户model类
 * Author: zal
 * Date: 2020-04-28
 * Time: 10:16
 */

namespace app\forms\api\user;

use app\core\ApiCode;
use app\forms\common\order\OrderCommon;
use app\logic\AppConfigLogic;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\Favorite;
use app\models\Goods;
use app\models\GoodsCollect;
use app\models\MemberLevel;
use app\models\Order;
use app\models\ScoreLog;
use app\models\User;
use app\models\UserCard;
use app\models\UserCoupon;
use app\plugins\mch\models\Mch;

class UserForm extends BaseModel
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
        ];
    }

    /**
     * 获取关联商户信息
     * @return array
     */
    private function getMchInfo(){
        $returnData = [
            'is_mch'    => 0,
            'store'     => null,
            'category'  => null,
            'stat'      => null
        ];
        $mchInfo = Mch::find()->where([
            'user_id'   => \Yii::$app->user->id,
            'is_delete' => 0
        ])->with(["store", "category"])->asArray()->one();
        if($mchInfo){
            $returnData['is_mch']   = 1;
            $returnData['store']    = $mchInfo['store'];
            $returnData['category'] = $mchInfo['category'];
            $returnData['stat']     = [
                'account_money' => (float)$mchInfo['account_money'],
                'order_num'     => 0,
                'goods_num'     => 0
            ];

            //商户订单数量
            $returnData['stat']['order_num'] = (int)Order::find()->where([
                'is_delete'  => 0,
                'is_recycle' => 0,
                'mch_id'     => $mchInfo['id']
            ])->count();

            //商户商品数量
            $returnData['stat']['goods_num'] = (int)Goods::find()->where([
                'is_delete' => 0,
                'is_recycle' => 0,
                'mch_id'    => $mchInfo['id']
            ])->count();
        }

        return $returnData;
    }

    /**
     * 用户信息
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 17:33
     * @return array
     */
    private function getUserInfo()
    {
        if (\Yii::$app->user->isGuest) {
            return null;
        }


        /** @var User $user */
        $user = \Yii::$app->user->identity;

        //先更新用户钱包
        User::updateUserWallet($user);


        $parentName = '系统';
        if ($user->parent_id != 0) {
            $parent = User::findOne([
                'id' => $user->parent_id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ]);

            if ($parent) {
                $parentName = $parent->nickname;
            }
        }

        $levelName = '普通用户';
        $memberPicUrl = '';
        if ($user->level != 0) {
            $level = MemberLevel::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'level' => $user->level,
                'status' => 1, 'is_delete' => 0
            ]);
            if ($level) {
                $levelName = $level->name;
                $memberPicUrl = $level->pic_url;
            }
        }

        $couponCount = UserCoupon::find()->andWhere(['user_id' => $user->id, 'is_delete' => 0,'is_failure'=> 0, 'is_use' => 0])->count();

        $favoriteCount = GoodsCollect::find()->alias('f')->where(['f.user_id' => $user->id, 'f.is_delete' => 0])
            ->leftJoin(['g' => Goods::tableName()], 'g.id = f.goods_id')
            ->andWhere(['g.status' => 1, 'g.is_delete' => 0,'g.is_recycle' => 0])->count();

        //商户信息
        $mchInfo = $this->getMchInfo();

        //判断是否新人
        $newUserIsGetScore = ScoreLog::find()->where([
            "user_id"     => $user->id,
            "source_type" => "new_user"
        ])->exists() ? 1 : 0;


        $result = [
            'user_id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'mobile' => $user->mobile,
            'avatar' => $user->avatar_url,
            'score' => intval($user->score),
            'total_score' => $user->total_score,
            'static_score' => intval($user->static_score),
            'dynamic_score' => intval($user->dynamic_score),
            'birthday' => $user->birthday > 0 ? date("Y-m-d",$user->birthday) : 0,
            'balance' => $user->balance,
            'total_balance' => $user->total_balance,
            'income' => $user->income,
            'income_frozen' => $user->income_frozen,
            'total_income' => $user->total_income,
            'static_integral' => $user->static_integral ?? 0,
            'dynamic_integral' => $user->dynamic_integral ?? 0,
            'favorite' => $favoriteCount ?? '0',
            //'footprint' => FootprintGoodsLog::find()->where(['user_id' => $user->id, 'is_delete' => 0])->count() ?? '0',
            'identity' => [
                'parent_name' => $parentName,
                'level_name' => $levelName,
                'member_level' => $user->level,
                'member_pic_url' => $memberPicUrl,
            ],
            'parent_id' => $user->parent_id,
            'coupon' => $couponCount,
          /*  'card' => $cardCount,*/
            'is_vip_card_user' => 0,
            'is_mch' => $mchInfo['is_mch'],
            'mch_info' => $mchInfo,
            'new_user_is_get_score' => $newUserIsGetScore
        ];

        $pluginUserInfo = \Yii::$app->plugin->getUserInfo($user);
        if(isset($pluginUserInfo["score"])){
            unset($pluginUserInfo["score"]);
        }
        $result = array_merge($result, $pluginUserInfo);
        return $result;
    }

    /**
     * 用户基本信息
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 17:33
     * @return array
     */
    public function getBasicInfo()
    {
        $result = $this->getUserInfo();

        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $cacheKey = 'user_register_' . $user->id . '_' . $user->mall_id;
        $couponList = \Yii::$app->cache->get($cacheKey);
        if ($couponList && count($couponList) > 0) {
            $result['register'] = ['coupon_list' => $couponList];
            \Yii::$app->cache->delete($cacheKey);
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"ok",$result);
    }

    /**
     * 获取用户中心页面配置
     * @return array
     * @throws \Exception
     */
    public function getUserCenterConfig()
    {
        $mall = \Yii::$app->mall->getMallSetting();
        $mall['setting']['web_service_url'] = urlencode($mall['setting']['web_service_url']);

        $userCenter = AppConfigLogic::getUserCenter(1);

        if (!\Yii::$app->user->isGuest) {
            $orderInfoCount = (new OrderCommon())->getOrderInfoCount();
            foreach ($orderInfoCount as $i => $v) {
                $userCenter['order_bar'][$i]['text'] = $orderInfoCount[$i] ?: '';
                $userCenter['order_bar'][$i]['num'] = $orderInfoCount[$i] ?: '';
            }
        }

        $userCenter['order_bar2'][0]['num'] = $userCenter['order_bar2'][0]['text'];
        $userCenter['order_bar'][] = $userCenter['order_bar2'][0];

        unset($userCenter['order_bar2']);

        //TODO
        $isMch = Mch::find()->where([
            'user_id'   => \Yii::$app->user->id,
            'is_delete' => 0
        ])->exists();
        if($isMch){
            $userCenter['menus'][] = [
                "icon_url"  => "https://dev.mingyuanriji.cn/web/uploads/images/thumbs/20210322/07c58e197c00184ba1aee91909f143f8.png",
                "name"      => "商户",
                "link_url"  => "/pages/personalCentre/personalCentre",
                "open_type" => "navigate"
            ];
            $userCenter['menus'][] = [
                "icon_url"  => "https://dev.mingyuanriji.cn/web/static/stock_img.png",
                "name"      => "进货专区",
                "link_url"  => "/pages/diy/diy?page_id=103",
                "open_type" => "navigate"
            ];
            $userCenter['menus'][] = [
                "icon_url"  => "https://dev.mingyuanriji.cn/web/static/CancelTwo.png",
                "name"      => "核销",
                "link_url"  => "/pages/personalCentre/accountingOrder/accountingOrder",
                "open_type" => "navigate"
            ];
        }

        $res = [
            'config' => [
                    'title_bar' => [
                        'background' => '#ff4544',
                        'color' => '#ffffff',
                    ],
                    'user_center' => $userCenter,
                    'copyright' => AppConfigLogic::getCoryRight(),
            ],

        ];
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',$res);
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-05-20
     * @Time: 11:22
     * @Note:余额记录
     * @return array|bool
     */
    public function balanceLog()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };

        $query = BalanceLog::find()->alias('b')->where([
            'b.user_id' => \Yii::$app->user->id,
            'b.mall_id' => \Yii::$app->mall->id,
        ])->orderBy('id desc');

        $list = $query->page($pagination, $this->limit,$this->page)->asArray()->all();

        foreach ($list as &$v) {
            $v["created_at"] = date("Y-m-d H:i:s",$v["created_at"]);
            $v['info_desc'] = json_decode($v['custom_desc'], true);
            unset($v["custom_desc"]);
        };
        unset($v);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $this->getPaginationInfo($pagination)
            ]
        ];
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-05-20
     * @Time: 11:22
     * @Note:积分记录
     * @return array|bool
     */
    public function scoreLog()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };

        $query = ScoreLog::find()->alias('i')->where([
            'i.user_id' => \Yii::$app->user->id,
            'i.mall_id' => \Yii::$app->mall->id,
        ])->orderBy('id desc');

        $list = $query->page($pagination, $this->limit,$this->page)->asArray()->all();

        foreach ($list as &$v) {
            $v["created_at"] = date("Y-m-d H:i:s",$v["created_at"]);
            $v['info_desc'] = json_decode($v['custom_desc'], true);
            unset($v["custom_desc"]);
        };
        unset($v);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $this->getPaginationInfo($pagination)
            ]
        ];
    }
}
