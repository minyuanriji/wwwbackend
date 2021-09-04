<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\Shopping_voucher\models\ShoppingVoucherLog;

class ShoppingVoucherLogListForm extends BaseModel{

    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;

    public function rules(){
        return [
            [['page', 'limit'], 'integer'],
            [['keyword', 'start_date', 'end_date'], 'trim'],
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = ShoppingVoucherLog::find()->alias("l")->where([
                'l.mall_id' => \Yii::$app->mall->id
            ])->innerJoin(["u" => User::tableName()], "l.user_id=u.id");

            if ($this->start_date && $this->end_date) {
                $query->andWhere([
                    "AND",
                    ["<", "l.created_at", strtotime($this->end_date)],
                    [">", "l.created_at", strtotime($this->start_date)]
                ]);
            }

            if($this->keyword){
                $query->andWhere([
                    "OR",
                    ["u.id" => (int)$this->keyword],
                    ["LIKE", "u.nickname", $this->keyword],
                    ["LIKE", "u.mobile", $this->keyword]
                ]);
            }

            $query->select(["l.*", "u.nickname", "u.role_type", "u.avatar_url"]);

            $query->orderBy("l.id DESC");

            $list = $query->page($pagination, $this->limit)->asArray()->all();

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ? $list : [],
                'pagination' => $pagination,
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}