<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\distribution\forms\mall;


use app\models\BaseModel;
use app\models\CommonOrderDetail;
use app\models\PriceLog;
use app\models\User;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionLevel;

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
            ->with(['user'])
            ->with(['commonOrderDetail'])
            ->where(['p.is_delete' => 0, 'p.mall_id' => $mall->id])
            ->leftJoin(['c' => CommonOrderDetail::tableName()], 'c.id = p.common_order_detail_id')
            ->leftJoin(['u' => User::tableName()], 'u.id = p.user_id');
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }

        $query->andWhere(['p.sign' => 'distribution']);
        $list = $query->page($pagination, $this->limit, $this->page)
            ->select('p.*,u.nickname,u.avatar_url,c.order_no,c.price as goods_price,c.status as c_status')
            ->orderBy('id desc')->asArray()->all();
        $newList = [];


        foreach ($list as $item) {
            /**
             * @var User $user ;
             *
             */


            $newItem['id'] = $item['id'];
            $newItem['user_id'] = $item['user_id'];
            $newItem['nickname'] = $item['nickname'];
            $newItem['avatar_url'] = $item['avatar_url'];

            $user = User::findOne($item['user_id']);
            if ($user) {


                $newItem['parent_name'] = $user->parent ? $user->parent->nickname : '平台';
                $distribution = Distribution::findOne(['user_id' => $user->id, 'is_delete' => 0]);
                $newItem['distribution_level_name'] = '默认等级';
                if ($distribution) {
                    $level = DistributionLevel::findOne(['mall_id' => $item['mall_id'], 'level' => $distribution->level, 'is_delete' => 0, 'is_use' => 1]);
                    if ($level) {
                        $newItem['distribution_level_name'] = $level->name;
                    }
                }
            }
            $newItem['order_no'] = $item['order_no'];
            $newItem['goods_price'] = $item['goods_price'];
            $newItem['price'] = $item['price'];
            $newItem['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
            $newItem['is_price'] = $item['is_price'] ? '已发放' : '待发放';
            if ($item['c_status'] == 0) {
                $newItem['status'] = '未完成';
            }
            if ($item['c_status'] == 1) {
                $newItem['status'] = '已完成';
            }
            if ($item['c_status'] == -1) {
                $newItem['status'] = '无效';
            }
            $newList[] = $newItem;
            
        }


        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,

            ]
        ];
    }
}