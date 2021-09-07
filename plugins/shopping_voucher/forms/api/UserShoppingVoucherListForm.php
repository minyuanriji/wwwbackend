<?php

namespace app\plugins\shopping_voucher\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherLog;

class UserShoppingVoucherListForm extends BaseModel {

    public $page;
    public $scene;
    public $type;
    public $created_at;


    public function rules(){
        return [
            [['page', 'type'], 'integer'],
            [['scene', 'created_at'], 'string']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = ShoppingVoucherLog::find()->where([
                "mall_id" => \Yii::$app->mall->id,
                "user_id" => \Yii::$app->user->id
            ])->orderBy("id DESC");

            if($this->type){
                $query->andWhere(["type" => $this->type]);
            }

            if ($this->created_at) {
                $query->andWhere('FROM_UNIXTIME(created_at,"%Y年%m月")="' . $this->created_at . '"');
            }

            if($this->scene){
                $query->andWhere(["source_type" => $this->scene]);
            }

            $selects = ["id", "type", "current_money", "money", "desc", "source_type", "created_at"];

            $incomeQuery = clone $query;
            $income = $incomeQuery->andWhere(['type' => 1])->sum('integral');

            $expenditureQuery = clone $query;
            $expenditure = $expenditureQuery->andWhere(['type' => 2])->sum('integral');

            $list = $query->select($selects)->page($pagination, 10, max(1, (int)$this->page))
                ->asArray()->all();

            if($list){
                foreach($list as &$item){
                    $item['created_at'] = date("m月d日 H:i", $item['created_at']);
                    $item['money'] = sprintf("%.2f", $item['money']);
                    $item['income'] = sprintf("%.2f", $item['income']);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ?: [],
                    'detailed_count'    => [
                        'income'        => $income ?: 0,
                        'expenditure'   => $expenditure ?: 0,
                    ],
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}