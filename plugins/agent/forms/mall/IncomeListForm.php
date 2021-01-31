<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\agent\forms\mall;


use app\helpers\ArrayHelper;
use app\models\CommonOrderDetail;
use app\models\PriceLog;
use app\models\User;
use app\plugins\agent\forms\common\AgentLevelCommon;
use app\plugins\agent\models\Agent;
use app\plugins\agent\models\AgentLevel;
use app\plugins\agent\models\AgentPriceLogType;
use app\plugins\agent\models\AgentSetting;
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
            [['sort'], 'default', 'value' => ['p.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = PriceLog::find()
            ->alias('p')
            ->where(['p.sign' => 'agent'])
            ->with(['user'])
            ->with(['commonOrderDetail'])
            ->andWhere(['p.is_delete' => 0, 'p.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = p.user_id');
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }
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
                $agent = Agent::findOne(['user_id' => $user->id, 'is_delete' => 0]);
                if (!$agent) {
                    $newItem['agent_level_name'] = '默认等级';
                } else {
                    $level = AgentLevel::findOne(['level' => $agent->level]);
                    if (!$level) {
                        $newItem['agent_level_name'] = '默认等级';
                    } else {
                        $newItem['agent_level_name'] = $level->name;
                    }
                }

                $common_order_detail = $item->commonOrderDetail;
                $newItem['order_no'] = $common_order_detail->order_no;
                $newItem['goods_price'] = $common_order_detail->price;
                $newItem['price'] = $item->price;
                $newItem['created_at'] = date('Y-m-d H:i:s', $item->created_at);
                $newItem['is_price'] = $item->is_price ? '已发放' : '待发放';
                $price_type = AgentPriceLogType::findOne(['price_log_id' => $item->id, 'is_delete' => 0]);
                if (!$price_type) {
                    $newItem['price_type'] = '未知类型';
                } else {
                    $price_type_arr = AgentPriceLogType::PRICE_TYPE;
                    $newItem['price_type'] = $price_type_arr[$price_type->type];
                }


                if ($common_order_detail->status == 0) {
                    $newItem['status'] = '未完成';
                }

                if ($common_order_detail->status == 1) {
                    $newItem['status'] = '已完成';
                }

                if ($common_order_detail->status == -1) {
                    $newItem['status'] = '无效';
                }


            }


            $newList[] = $newItem;
        }


        $agent_level_list = AgentLevel::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->all();
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'agent_level_list'=>$agent_level_list
            ]
        ];
    }
}