<?php

namespace app\plugins\integral_card\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\integral_card\models\ScoreFromFree;

class FromFreeListForm extends BaseModel {

    public $page;
    public $limit;
    public $keyword;
    public $status;

    public function rules(){
        return [
            [['page', 'limit'], 'integer'],
            [['keyword', 'status'], 'trim'],
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = ScoreFromFree::find()->where(["is_delete" => 0]);
            if($this->keyword){
                $query->andWhere([
                    "OR",
                    ["LIKE", "name", $this->keyword]
                ]);
            }
            if($this->status && $this->status == "enable"){
                $query->andWhere(["enable_score" => 1]);
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
                    $item['start_at'] = date("Y-m-d", $item['start_at'] ? $item['start_at'] : time());
                    $item['end_at'] = date("Y-m-d", $item['end_at'] ? $item['end_at'] : time());
                    $item['score_give_settings'] = array_merge($scoreGiveSettings, !empty($item['score_setting']) ? (array)@json_decode($item['score_setting']) : []);
                    $item['score_give_settings']['is_permanent'] = (int)$item['score_give_settings']['is_permanent'];
                    $item['number'] = (int)$item['number'];
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