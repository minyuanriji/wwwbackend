<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\stock\forms\mall;


use app\helpers\ArrayHelper;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\PriceLog;
use app\models\User;
use app\plugins\stock\forms\common\StockLevelCommon;
use app\plugins\stock\models\GoodsPriceLog;
use app\plugins\stock\models\Stock;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockPriceLog;
use app\plugins\stock\models\StockPriceLogType;
use app\plugins\stock\models\StockSetting;
use app\models\BaseModel;

class GoodsPriceLogListForm extends BaseModel
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
        $query = GoodsPriceLog::find()
            ->alias('p')
            ->where(['p.is_delete' => 0, 'p.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = p.user_id');
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)
            ->select('p.*,u.nickname,u.avatar_url')
            ->orderBy('id desc')->asArray()->all();
        $newList = [];

        foreach ($list as $item) {
            /**
             * @var User $user ;
             *
             */
            $newItem['price'] = $item['price'];
            $newItem['id'] = $item['id'];
            $newItem['user_id'] = $item['user_id'];
            $newItem['nickname'] = $item['nickname'];
            $newItem['avatar_url'] = $item['avatar_url'];
            $newItem['order_no'] = $item['order_no'];
            $newItem['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
            $agent = StockAgent::findOne(['user_id' => $item['user_id'], 'is_delete' => 0]);
            if (!$agent) {
                $newItem['agent_level_name'] = '默认等级';
            } else {
                $level = StockLevel::findOne(['level' => $agent->level]);
                if (!$level) {
                    $newItem['agent_level_name'] = '默认等级';
                } else {
                    $newItem['agent_level_name'] = $level->name;
                }
            }
            $goods = Goods::findOne($item['goods_id']);
            if ($goods) {
                $goodsInfo = $goods->goodsWarehouse;
                $newItem['goods_name'] = $goodsInfo->name;
                $newItem['cover_pic'] = $goodsInfo->cover_pic;
            }
            $user = User::findOne($item['buy_user_id']);
            if ($user) {
                $newItem['buy_user_nickname'] = $user->nickname;
                $newItem['buy_user_avatar_url'] = $user->avatar_url;
            }
            if ($item['type'] == 0) {
                $newItem['type'] = '商城下单';
            }
            if ($item['type']==1) {
                $newItem['type'] = '拿货下单';
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