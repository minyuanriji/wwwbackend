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
use app\plugins\stock\models\StockGoods;

class StockGoodsListForm extends BaseModel
{
    public $keyword;
    public $platform;
    public $limit = 10;
    public $page = 1;
    public $sort;
    public $level;
    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['keyword', 'platform'], 'trim'],
            [['keyword', 'platform', 'flag'], 'string'],
            [['limit', 'page', 'level'], 'integer'],
            [['fields'], 'safe'],
            [['sort'], 'default', 'value' => ['sg.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = StockGoods::find()->alias('sg')
            ->where(['sg.is_delete' => 0, 'sg.mall_id' => $mall->id])
            ->leftJoin(['g' => Goods::tableName()], 'g.id = sg.goods_id')
            ->leftJoin(['w' => GoodsWarehouse::tableName()], 'w.id=g.goods_warehouse_id');
        if ($this->keyword) {
            $query->andWhere(['like', 'w.name', $this->keyword]);
        }
        $list = $query->select('w.name,w.cover_pic,sg.*')->page($pagination, $this->limit, $this->page)
            ->orderBy($this->sort)->asArray()->all();

        foreach ($list as &$item) {
            if ($item['agent_price']) {
                $item['agent_price'] = SerializeHelper::decode($item['agent_price']);
            }else{
                $item['agent_price'] = [];
            }
            if ($item['equal_level_list']) {
                $item['equal_level_list'] = SerializeHelper::decode($item['equal_level_list']);
            }else{
                $item['equal_level_list'] = [];
            }
            if ($item['fill_level_list']) {
                $item['fill_level_list'] = SerializeHelper::decode($item['fill_level_list']);
            }else{
                $item['fill_level_list'] = [];
            }
            if ($item['over_level_list']) {
                $item['over_level_list'] = SerializeHelper::decode($item['over_level_list']);
            }else{
                $item['over_level_list'] = [];
            }
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}