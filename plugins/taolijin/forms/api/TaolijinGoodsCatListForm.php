<?php

namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinCats;

class TaolijinGoodsCatListForm extends BaseModel{

    public function get(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $query = TaolijinCats::find()->where([
                'mall_id'   => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);
            $selects = ["id", "name", "pic_url"];
            $list = $query->select($selects)->orderBy("sort DESC,id DESC")->asArray()->andWhere(['parent_id' => 0])->all();
            if($list){
                foreach ($list as $item) {

                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list
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