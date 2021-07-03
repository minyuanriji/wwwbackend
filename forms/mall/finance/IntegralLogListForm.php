<?php
namespace app\forms\mall\finance;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\IntegralLog;
use app\models\User;

class IntegralLogListForm extends BaseModel{

    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $user_id;
    public $is_manual;

    public function rules(){
        return [
            [['page', 'limit', 'user_id'], 'integer'],
            [['keyword', 'start_date', 'end_date'], 'trim'],
            [['is_manual',], 'safe'],
        ];
    }
    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = IntegralLog::find()->alias('il')->where([
            'il.mall_id' => \Yii::$app->mall->id,
        ])->orderBy('il.id desc');
        $query->innerJoin(["u" => User::tableName()], "u.id=il.user_id");

        if ($this->user_id) {
            $query->andWhere(['il.user_id' => $this->user_id]);
        }

        if(!empty($this->keyword)){
            $query->andWhere([
                "OR",
                "u.nickname LIKE '%".$this->keyword."%'"
            ]);
        }

        if ($this->is_manual != '') {
            $query->andWhere(['il.is_manual' => $this->is_manual]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'il.created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'il.created_at', strtotime($this->start_date)]);
        }

        $query->select(["il.*", "u.nickname"]);

        $list = $query->page($pagination, $this->limit)->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}