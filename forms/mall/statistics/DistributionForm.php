<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销统计
 * Author: zal
 * Date: 2020-04-15
 * Time: 15:50
 */

namespace app\forms\mall\statistics;

use app\core\ApiCode;
use app\forms\mall\export\DistributionStatisticsExport;
use app\models\BaseModel;
use app\models\User;
use app\plugins\distribution\models\Distribution;

class DistributionForm extends BaseModel
{
    public $name;
    public $order;

    public $page;
    public $limit;

    public $flag;
    public $fields;

    public $platform;

    public function rules()
    {
        return [
            [['flag'], 'string'],
            [['page', 'limit'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['name', 'order', 'platform'], 'string'],
            [['fields'], 'trim']
        ];
    }

    /**
     * 搜索
     * @return array|bool
     */
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = $this->where();

        $query->select("s.`user_id` as `id`,s.`user_id`,i.`nickname` as name,s.`total_childs`,
        s.`total_price`,  s.`total_order`,`i`.`platform`")
            ->groupBy('s.`user_id`,s.`mall_id`');

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $this->export($new_query);
            return false;
        }

        $list = $query->with('user')
            ->page($pagination)
            ->asArray()
            ->all();

        foreach ($list as $key => $value) {
            $list[$key]['nickname'] = $value['user']['nickname'];
            $list[$key]['avatar'] = $value['user']['avatar_url'];
            unset($list[$key]['user']);
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
            ]
        ];
    }

    /**
     * 组装查询条件
     * @return \app\models\BaseActiveQuery
     */
    protected function where()
    {
        $query = Distribution::find()->alias('s')->where(['s.is_delete' => 0, 's.mall_id' => \Yii::$app->mall->id,])
            //->leftJoin(['sc' => DistributionCash::tableName()], 'sc.`user_id` = s.`user_id` AND sc.`mall_id`=s.`mall_id` AND sc.`status` = 2 ')
            ->leftJoin(['i' => User::tableName()], 'i.id = s.user_id');
        if ($this->name) {
            $query->andWhere(['or', ['s.user_id' => $this->name], ['like', 'i.nickname', $this->name]]);
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }
        $query->orderBy(!empty($this->order) ? $this->order : 's.id');

        return $query;
    }

    /**
     * 导出
     * @param $query
     */
    protected function export($query)
    {
        $exp = new DistributionStatisticsExport();
        $exp->export($query);
    }
}