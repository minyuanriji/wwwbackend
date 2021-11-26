<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchGroup;

class MchGroupForm extends BaseModel{

    public $page;
    public $keyword;

    public function rules(){
        return [
            [['page'], 'safe'],
            [['keyword'], 'trim']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = MchGroup::find()->alias("mg")->where([
                "mg.is_delete" => 0,
                "m.is_delete"  => 0
            ]);
            $query->innerJoin(["m" => Mch::tableName()], "m.id=mg.mch_id");
            $query->innerJoin(["s" => Store::tableName()], "s.id=mg.store_id");

            if(!empty($this->keyword)){
                $query->andWhere([
                    "OR",
                    ["m.mobile" => $this->keyword],
                    ["LIKE", "s.name", $this->keyword]
                ]);
            }

            $query->select(["mg.*", "m.mobile", "s.name", "s.cover_url"]);

            $list = $query->orderBy("mg.id DESC")->page($pagination, 20, $this->page)->asArray()->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list,
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