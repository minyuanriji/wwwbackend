<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\boss\forms\mall;


use app\helpers\ArrayHelper;
use app\models\CommonOrderDetail;
use app\models\PriceLog;
use app\models\User;
use app\plugins\boss\forms\common\BossLevelCommon;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossLevel;
use app\plugins\boss\models\BossOrderGoodsLog;
use app\plugins\boss\models\BossPriceLogType;
use app\plugins\boss\models\BossSetting;
use app\models\BaseModel;

class IncomeListForm extends BaseModel
{

    public $keyword;
    public $platform;
    public $limit = 10;
    public $page = 1;
    public $sort;
    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['keyword', 'platform'], 'trim'],
            [['keyword', 'platform', 'flag'], 'string'],
            [['limit', 'page'], 'integer'],
            [['fields'], 'safe'],
            [['sort'], 'default', 'value' => ['l.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = BossOrderGoodsLog::find()
            ->alias('l')
            ->with(['user'])
            ->with(['commonOrderDetail'])
            ->where(['l.is_delete' => 0, 'l.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = l.user_id');
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }
        //结算方式0订单金额1利润
        $compute_type = BossSetting::getValueByKey(BossSetting::COMPUTE_TYPE, $mall->id);
        $compute_type = empty($compute_type) ? BossSetting::COMPUTE_TYPE_PRICE : BossSetting::COMPUTE_TYPE_PROFIT;
        $list = $query->page($pagination, $this->limit, $this->page)
            ->orderBy('id desc')->all();
        $newList = [];
        foreach ($list as $item) {
            /**
             * @var User $user ;
             *
             */

            $user = $item->user ? $item->user : null;

            if ($user) {
                $newItem['id'] =$item['id'];
                $newItem['user_id'] = $user->id;
                $newItem['nickname'] = $user->nickname;
                $newItem['avatar_url'] = $user->avatar_url;
                $newItem['parent_name'] = $user->parent ? $user->parent->nickname : '平台';
                $boss = Boss::findOne(['user_id' => $user->id, 'is_delete' => 0]);
                if (!$boss) {
                    $newItem['boss_level_name'] = '默认等级';
                } else {
                    $level = BossLevel::findOne(['level' => $boss->level]);
                    if (!$level) {
                        $newItem['boss_level_name'] = '默认等级';
                    } else {
                        $newItem['boss_level_name'] = $level->name;
                    }
                }
                /** @var CommonOrderDetail $common_order_detail */
                $common_order_detail = $item->commonOrderDetail;
                $newItem['order_no'] = $common_order_detail->order_no;
                $goods_price = $common_order_detail->price;
                //结算方式是利润，获取订单详情中的利润字段
                if ($compute_type == BossSetting::COMPUTE_TYPE_PROFIT) {
                    $goods_price = $common_order_detail->profit;
                }
                $newItem['goods_price'] = $goods_price;
                $newItem['price'] = $item->price;
                $newItem['created_at'] = date('Y-m-d H:i:s', $item->created_at);
                if($item->type==0){
                    $newItem['price_type'] = '永久分红';
                }else{
                    $newItem['price_type'] = '额外分红';
                }
                if ($common_order_detail->status == 0) {
                    $newItem['order_status'] = '未完成';
                }
                if ($common_order_detail->status == 1) {
                    $newItem['order_status'] = '已完成';
                }
                if ($common_order_detail->status == -1) {
                    $newItem['order_status'] = '无效';
                }
                $newItem["commission"] = number_format($goods_price * $item->price / 100,2);
            }
            $newList[] = $newItem;
        }


        $boss_level_list = BossLevel::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->all();
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'boss_level_list'=>$boss_level_list
            ]
        ];
    }
}