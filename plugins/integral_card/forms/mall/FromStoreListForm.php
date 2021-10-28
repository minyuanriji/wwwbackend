<?php

namespace app\plugins\integral_card\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\integral_card\models\ScoreFromStore;

class FromStoreListForm extends BaseModel {

    public $page;
    public $limit;
    public $keyword;

    public function rules(){
        return [
            [['page', 'limit'], 'integer'],
            [['keyword'], 'trim'],
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = ScoreFromStore::find()->where(["is_delete" => 0]);
            if($this->keyword){
                $query->andWhere([
                    "OR",
                    ["mch_id" => (int)$this->keyword],
                    ["LIKE", "name", $this->keyword]
                ]);
            }

            $query->orderBy("id DESC");

            $list = $query->page($pagination, $this->limit, $this->page)->asArray()->all();

            if($list){
                $scoreGiveSettings = [
                    "is_permanent" => 0,
                    "integral_num" => 0,
                    "period"       => 1,
                    "period_unit"  => "month",
                    "expire"       => 30
                ];
                foreach($list as &$item){
                    $item['start_at'] = date("Y-m-d H:i:s", $item['start_at'] ? $item['start_at'] : time());
                    $item['score_give_settings'] = array_merge($scoreGiveSettings,
                        !empty($item['score_setting']) ? (array)@json_decode($item['score_setting']) : []);
                    $item['score_give_settings']['is_permanent'] = (int)$item['score_give_settings']['is_permanent'];
                    $item['rate'] = (float)$item['rate'];
                }
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ?: [],
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}