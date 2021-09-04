<?php

namespace app\plugins\shopping_voucher\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherLog;

class UserShoppingVoucherListForm extends BaseModel {

    public $page;
    public $scene;
    public $type;

    public function rules(){
        return [
            [['page', 'type'], 'integer'],
            [['scene'], 'string']
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

            if($this->scene){
                $query->andWhere(["source_type" => $this->scene]);
            }

            $selects = ["id", "type", "current_money", "money", "desc", "source_type", "created_at"];
            $list = $query->select($selects)->page($pagination, 10, max(1, (int)$this->page))
                ->asArray()->all();

            if($list){
                foreach($list as &$item){
                    $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
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