<?php
namespace app\plugins\hotel\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\models\Hotels;

class HotelSimpleListForm extends BaseModel{

    public $page;

    public function rules(){
        return [
            [['page'], 'integer'],
            [['start_date', 'end_date'], 'string']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = Hotels::find()->alias("ho")->where([
                "ho.is_delete"  => 0,
                "ho.is_open"    => 1,
                "ho.is_booking" => 1,
                "ho.mall_id"    => \Yii::$app->mall->id
            ]);

            $selects = ["ho.id", "ho.thumb_url", "ho.name", "ho.type", "ho.cmt_grade", "ho.cmt_num", "ho.price"];

            $rows = $query->select($selects)->page($pagination, 10, max(1, (int)$this->page))
                          ->asArray()->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $rows ? $rows : [],
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