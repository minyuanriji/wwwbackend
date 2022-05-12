<?php

namespace app\plugins\integral_card\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\integral_card\models\ScoreFromFree;

class FromFreeEditForm extends BaseModel{

    public $id;
    public $name;
    public $start_at;
    public $end_at;
    public $score_enable;
    public $score_give_settings;
    public $parent_award_enable;
    public $number;

    public function rules(){
        return [
            [['name'], 'required'],
            [['id'], 'integer'],
            [['number'], 'number'],
            [['start_at', 'end_at'], 'string'],
            [['score_give_settings', 'score_enable', 'parent_award_enable', 'number'], 'safe']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $fromFree = ScoreFromFree::findOne($this->id);
            if(!$fromFree){
                $fromFree = new ScoreFromFree([
                    "mall_id"    => \Yii::$app->mall->id,
                    "created_at" => time()
                ]);
            }

            $this->score_give_settings['integral_num'] = 0;
            if(isset($this->score_give_settings['is_permanent']) && $this->score_give_settings['is_permanent']){
                $this->score_give_settings['expire'] = -1;
                $this->score_give_settings['period'] = 1;
            }else{
                $this->score_give_settings['integral_num'] = $this->number;
                $this->score_give_settings['expire'] = max(0, min($this->score_give_settings['expire'], 30));
            }

            $fromFree->updated_at    = time();
            $fromFree->name          = $this->name;
            $fromFree->start_at      = strtotime($this->start_at);
            $fromFree->end_at        = strtotime($this->end_at);
            $fromFree->enable_score  = $this->score_enable == "true" ? 1 : 0;
            $fromFree->enable_parent_award  = $this->parent_award_enable == "true" ? 1 : 0;
            $fromFree->score_setting = is_array($this->score_give_settings) ? json_encode($this->score_give_settings) : '';
            $fromFree->number        = (int)$this->number;

            if(!$fromFree->save()){
                throw new \Exception($this->responseErrorMsg($fromFree));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}