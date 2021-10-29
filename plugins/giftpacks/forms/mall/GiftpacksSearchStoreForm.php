<?php

namespace app\plugins\giftpacks\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;

class GiftpacksSearchStoreForm extends BaseModel {

    public $keyword;

    public function rules(){
        return array_merge(parent::rules(), [
            [['keyword'], 'trim']
        ]);
    }

    public function search(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = Mch::find()->alias("m")->innerJoin(["s" => Store::tableName()], "m.id=s.mch_id");
            $query->andWhere([
                "AND",
                ["m.is_delete"     => 0],
                ["m.review_status" => Mch::REVIEW_STATUS_CHECKED],
                ["LIKE", "s.name", $this->keyword],
                "m.mobile IS NOT NULL"
            ]);

            $rows = $query->asArray()->select(["s.id", "s.name"])->limit(20)->all();

            $list = [];
            if($rows){
                foreach($rows as $row){
                    $list[] = [
                        'value'      => $row['name'],
                        'store_id'   => $row['id'],
                        'store_name' => $row['name']
                    ];
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : []
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