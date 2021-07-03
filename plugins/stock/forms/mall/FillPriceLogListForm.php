<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\stock\forms\mall;


use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\User;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillPriceLog;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockGoods;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockOrder;

class FillPriceLogListForm extends BaseModel
{
    public $keyword;
    public $limit = 10;
    public $page = 1;
    public $sort;

    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['keyword', 'flag'], 'string'],
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
        $query = FillPriceLog::find()->alias('l')
            ->where(['l.is_delete' => 0, 'l.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = l.user_id')
            ->leftJoin(['sa' => StockAgent::tableName()], 'sa.user_id=u.id')
            ->leftJoin(['o' => FillOrder::tableName()], 'o.id=l.order_id');
        if ($this->keyword) {
            $query->andWhere(['like', 'u.nickname', $this->keyword]);
        }
        $list = $query->select('u.nickname,l.*,u.avatar_url,sa.level,o.order_no,o.user_id as buy_user_id')
            ->page($pagination, $this->limit, $this->page)
            ->orderBy($this->sort)->asArray()->all();
        foreach ($list as &$item) {
            $level = StockLevel::findOne(['level' => $item['level'], 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_use' => 1]);
            if (!$level) {
                $item['level_name'] = '默认等级';
            } else {
                $item['level_name'] = $level->name;
            }
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
            $goods = Goods::findOne($item['goods_id']);
            if ($goods) {
                $item['goods'] = $goods->goodsWarehouse;
            }
            $user = User::findOne($item['buy_user_id']);
            if ($user) {
                $item['buy_user_nickname'] = $user->nickname;
                $item['buy_user_avatar'] = $user->avatar_url;
                $agent = StockAgent::findOne(['user_id' => $user->id]);
                if ($agent) {
                    $level = StockLevel::findOne(['level' => $agent->level, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_use' => 1]);
                    if (!$level) {
                        $item['buy_level_name'] = '默认等级';
                    } else {
                        $item['buy_level_name'] = $level->name;
                    }
                }
            }
        }
        return [
            'code' => 0,
            'mso' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}