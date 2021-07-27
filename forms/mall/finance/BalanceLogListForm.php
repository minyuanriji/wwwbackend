<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-13
 * Time: 14:09
 */

namespace app\forms\mall\finance;


use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BalanceLog;
use app\models\BaseModel;
class BalanceLogListForm extends BaseModel
{
    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;

    public $user_id;

    public function rules()
    {
        return [
            [['page', 'limit', 'user_id'], 'integer'],
            [['keyword', 'start_date', 'end_date'], 'trim'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };
        $query = BalanceLog::find()->alias('b')->where([
            'b.mall_id' => \Yii::$app->mall->id,
        ])->joinwith(['user' => function ($query) {
            if ($this->keyword) {
                $query->where(['like', 'nickname', $this->keyword]);
            }
        }])->orderBy('id desc');
        if ($this->user_id) {
            $query->andWhere(['b.user_id' => $this->user_id]);
        }
        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'b.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'b.created_at', strtotime($this->start_date)]);
        }
        $list = $query->page($pagination, $this->limit)->asArray()->all();
        foreach ($list as &$v) {
            $v['info_desc'] = SerializeHelper::decode($v['custom_desc']);
        };
        unset($v);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];

    }


}