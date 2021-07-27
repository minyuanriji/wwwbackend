<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 开团查询form
 * Author: xuyaoxiang
 * Date: 2020/9/4
 * Time: 10:04
 */

namespace app\plugins\group_buy\forms\common;

use app\models\BaseModel;
use app\plugins\group_buy\models\PluginGroupBuyActive;
use app\models\User;
use app\plugins\group_buy\models\PluginGroupBuyGoods;

class ActiveQueryCommonForm extends BaseModel
{
    public $id;
    public $page = 1;
    public $limit = 10;
    public $mall_id;
    public $status;
    public $group_buy_id;
    public $goods_id;
    public $nickname;
    public $begin_time;
    public $end_time;

    protected $as_array = true;
    protected $pagination = null;

    public function rules()
    {
        return [
            [['page', 'limit', 'status', 'id', 'group_buy_id', 'goods_id'], 'integer'],
            [['nickname', 'begin_time', 'end_time'], 'string'],
            [['id'], 'required', 'on' => 'show'],
            [['goods_id'], 'required', 'on' => 'goods']
        ];
    }

    public function init()
    {
        $this->mall_id = \Yii::$app->mall->id;
    }

    /**
     * 返回查询条件
     * @return \yii\db\ActiveQuery
     */
    protected function queryData()
    {
        $query = PluginGroupBuyActive::find()
            ->alias('a')
            ->leftJoin(['u' => User::tableName()], 'u.id=a.creator_id')
            ->leftJoin(['gbg' => PluginGroupBuyGoods::tableName()], 'a.group_buy_id=gbg.id')
            ->with('goods')
            ->with('creator')
            ->with('groupBuyGoods')
            ->where(['a.mall_id' => $this->mall_id, 'a.deleted_at' => 0, 'gbg.deleted_at' => 0]);

        $query->andFilterWhere(['a.id' => $this->id]);

        $query->andFilterWhere(['like', 'u.nickname', $this->nickname]);

        $query->andFilterWhere(['a.status' => $this->status]);

        $query->andFilterWhere(['a.group_buy_id' => $this->group_buy_id]);

        $query->andFilterWhere(['a.goods_id' => $this->goods_id]);

        $query->andFilterWhere(['>=', 'a.start_at', $this->begin_time]);

        $query->andFilterWhere(['<', 'a.start_at', $this->end_time]);

        $query->asArray($this->as_array);

        return $query;
    }

    public function returnOne()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
        }

        $query = $this->queryData();

        return $query->one();
    }

    /**
     * 返回分页数据
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function returnAll()
    {
        $query = $this->queryData();

        $query->page($this->pagination, $this->limit, $this->page);

        $all = $query->all();

        return $all;
    }

    public function queryShow()
    {
        return PluginGroupBuyActive::find()
            ->with('creator')
            ->with('activeItems')
            ->where(['id' => $this->id, 'mall_id' => $this->mall_id])->asArray($this->as_array)->one();
    }

    /**
     * 对外返回数据
     * @param $all
     * @return array
     */
    protected function returnData($all)
    {
        return $this->returnApiResultData(0, "", [
            'list'       => $all,
            'pagination' => $this->pagination
        ]);
    }

    /**
     * 获取某商品累计拼人数
     * @return mixed
     */
    public function getActualPeopleSum()
    {
        $this->scenario = "goods";

        if (!$this->validate()) {
            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
        }

        $actual_people = $this->SqlActualPeopleSum($this->goods_id);

        return $this->returnApiResultData(0, "", ['actual_people' => $actual_people]);
    }

    /**
     * 获取某商品累计拼人数
     * @return bool|int|mixed|string
     */
    public function SqlActualPeopleSum($goods_id)
    {
        $return = PluginGroupBuyActive::find()->where(['goods_id' => $goods_id])->sum('actual_people');

        if ($return) {
            return $return;
        }

        return 0;
    }
}