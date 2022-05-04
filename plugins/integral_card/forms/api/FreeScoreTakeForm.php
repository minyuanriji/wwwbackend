<?php

namespace app\plugins\integral_card\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Integral;
use app\plugins\integral_card\models\ScoreFromFree;
use app\plugins\integral_card\models\ScoreSendLog;

class FreeScoreTakeForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function take(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $fromFree = ScoreFromFree::findOne($this->id);
            if(!$fromFree || $fromFree->is_delete){
                throw new \Exception("积分免费领活动不存在");
            }

            if(!$fromFree->enable_score || time() < $fromFree->start_at || (time() > $fromFree->end_at + 3600 * 24 - 1)){
                throw new \Exception("活动未开启或已结束");
            }

            $sendLog = ScoreSendLog::findOne([
                "user_id"     => \Yii::$app->user->id,
                "mall_id"     => $fromFree->mall_id,
                "source_id"   => $fromFree->id,
                "source_type" => "from_free"
            ]);
            if(!$sendLog){
                $sendLog = new ScoreSendLog([
                    "mall_id"     => $fromFree->mall_id,
                    "user_id"     => \Yii::$app->user->id,
                    "source_id"   => $fromFree->id,
                    "source_type" => "from_free",
                    "status"      => "success",
                    "created_at"  => time(),
                    "updated_at"  => time(),
                    "data_json"   => json_encode($fromFree->getAttributes())
                ]);

                if($sendLog->save()){
                    $scoreSetting = @json_decode($fromFree->score_setting, true);
                    $scoreSetting['integral_num'] = floatval($fromFree->number);
                    $scoreSetting['source_type']  = 'from_free';
                    $scoreSetting['source_id']    = $fromFree->id;
                    $res = Integral::addIntegralPlan(\Yii::$app->user->id, $scoreSetting, '免费领积分', '0');
                }else{
                    throw new \Exception($this->responseErrorMsg($sendLog));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}