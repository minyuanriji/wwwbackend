<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\stock\forms\mall;

use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\User;
use app\plugins\stock\jobs\CommonOrderPayedJob;
use app\plugins\stock\models\FillIncomeLog;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockLevel;
use app\models\BaseModel;
use app\plugins\stock\models\UpgradeBagLog;

class UpgradeBagLogListForm extends BaseModel
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
        $query = UpgradeBagLog::find()
            ->alias('p')
            ->where(['p.is_delete' => 0, 'p.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = p.user_id')
            ->leftJoin(['c' => CommonOrderDetail::tableName()], 'c.id=p.common_order_detail_id')
            ->leftJoin(['sa' => StockAgent::tableName()], 'sa.user_id=u.id');
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)
            ->select('p.*,c.goods_id,c.order_no,u.avatar_url,u.nickname,c.price as goods_price,c.num,sa.level as agent_level')
            ->orderBy('id desc')
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
            $goods = Goods::findOne($item['goods_id']);
            if ($goods) {
                $item['goods_name'] = $goods->goodsWarehouse->name;
                $item['cover_pic'] = $goods->goodsWarehouse->cover_pic;
            }
            $item['agent_level_name'] = '默认等级';
            $stockAgentLevel = StockLevel::findOne(['level' => $item['agent_level'], 'mall_id' => \Yii::$app->mall->id]);
            if ($stockAgentLevel) {
                $item['agent_level_name'] = $stockAgentLevel->name;
            }
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,

            ]
        ];
    }
}