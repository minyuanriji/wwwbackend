<?php
namespace app\plugins\hotel\forms\api\hotel_search;


use app\core\ApiCode;

class HotelSearchWaitForm extends HotelSearchForm{

    public $prepare_id;

    public function rules(){
        return [
            [['prepare_id'], 'required']
        ];
    }

    public function waitTask(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            sleep(3);
            $search = static::getSearchByPrepareId($this->prepare_id);
            if(!$search){
                throw new \Exception("搜索异常，请重新搜索");
            }

            $content = !empty($search->content) ? json_decode($search->content, true) : [];
            $isFinished = 0;
            if(!$search->is_running){
                $jobList = \Yii::$app->getCache()->get(static::jobListCacheKey($search->search_id));
                print_r($jobList);
                exit;
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "founds"    => count($content['found_ids']),
                    "finished"  => $isFinished,
                    "search_id" => $search->search_id
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