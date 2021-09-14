<?php

namespace app\plugins\integral_card\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\integral_card\models\ScoreFromStore;

class FromStoreEditForm extends BaseModel{

    public $id;
    public $mch_id;
    public $store_id;
    public $name;
    public $cover_url;
    public $start_at;
    public $score_enable;
    public $score_give_settings;
    public $rate;

    public function rules(){
        return [
            [['mch_id', 'store_id', 'name', 'cover_url'], 'required'],
            [['id', 'mch_id', 'store_id'], 'integer'],
            [['start_at'], 'string'],
            [['score_give_settings', 'score_enable', 'rate'], 'safe']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $fromStore = ScoreFromStore::findOne($this->id);
            if(!$fromStore){

                $exists = ScoreFromStore::findOne([
                    "store_id" => $this->store_id
                ]);
                if($exists && !$exists->is_delete){
                    throw new \Exception("已添加过该门店了");
                }

                if(!$exists){
                    $fromStore = new ScoreFromStore([
                        "mall_id"    => \Yii::$app->mall->id,
                        "created_at" => time()
                    ]);
                }else{
                    $fromStore = $exists;
                    $fromStore->is_delete = 0;
                    $fromStore->deleted_at = 0;
                }

            }

            $this->score_give_settings['integral_num'] = 0;
            if(isset($this->score_give_settings['is_permanent']) && $this->score_give_settings['is_permanent']){
                $this->score_give_settings['expire'] = -1;
                $this->score_give_settings['period'] = 1;
            }else{
                $this->score_give_settings['expire'] = max(0, min($this->score_give_settings['expire'], 31));
            }

            $fromStore->mch_id        = $this->mch_id;
            $fromStore->store_id      = $this->store_id;
            $fromStore->updated_at    = time();
            $fromStore->name          = $this->name;
            $fromStore->cover_url     = $this->cover_url;
            $fromStore->start_at      = strtotime($this->start_at);
            $fromStore->enable_score  = $this->score_enable == "true" ? 1 : 0;
            $fromStore->score_setting = is_array($this->score_give_settings) ? json_encode($this->score_give_settings) : '';
            $fromStore->rate          = min(max(0, (float)$this->rate), 100);

            if(!$fromStore->save()){
                throw new \Exception($this->responseErrorMsg($fromStore));
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