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
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockGoods;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\StockOrder;

class FillOrderListForm extends BaseModel
{
    public $keyword;

    public $limit = 10;
    public $page = 1;
    public $sort;
    public $level;
    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['keyword', 'flag'], 'string'],
            [['limit', 'page', 'level'], 'integer'],
            [['fields'], 'safe'],
            [['sort'], 'default', 'value' => ['so.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = FillOrder::find()->alias('so')
            ->where(['so.is_delete' => 0, 'so.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = so.user_id')
            ->leftJoin(['sa' => StockAgent::tableName()], 'sa.user_id=u.id');
        if ($this->keyword) {
            $query->andWhere(['like', 'u.nickname', $this->keyword]);
        }
        $list = $query->select('u.nickname,so.*,u.avatar_url,sa.level')
            ->page($pagination, $this->limit, $this->page)
            ->orderBy($this->sort)->asArray()->all();
        foreach ($list as &$item) {
            $level = StockLevel::findOne(['level' => $item['level'], 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_use']);
            if (!$level) {
                $item['level_name'] = '默认等级';
            } else {
                $item['level_name'] = $level->name;
            }
            $item['created_at']=date('Y-m-d H:i:s',$item['created_at']);
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