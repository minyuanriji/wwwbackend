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

        $cacheObj = \Yii::$app->getCache();

        try {

            /*sleep(1);

            $searchData = static::getSearchDataByPrepareId($this->prepare_id);
            if(!$searchData){
                throw new \Exception("搜索异常，请重新搜索");
            }

            $content = !empty($searchData['content']) ? json_decode($searchData['content'], true) : [];
            $isFinished = 0;
            if(!$searchData['is_running'] || empty($content['hotel_ids'])){
                $jobList = $cacheObj->get(static::jobListCacheKey($searchData['search_id']));
                if(!empty($jobList)){
                    $nowTime = time();
                    foreach($jobList as $pid => $time){
                        if(($nowTime - $time) > 10){ //10秒超时
                            unset($jobList[$pid]);
                        }
                    }
                }
                $isFinished = empty($jobList) ? 1 : 0;
            }else{

            }*/

            /*if($isFinished){
                $cacheKey = "HotelSearchWaitTask:" . $this->prepare_id;
                $delayCount = $cacheObj->get();
                if(!$delayCount || $delayCount < 3){
                    //$isFinished = 0;
                    $cacheObj->set($cacheKey, intval($delayCount) + 1, 1800);
                }
            }*/

            $content = ["found_ids" => []];
            $searchData['search_id'] = $this->prepare_id;
            $isFinished = 1;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "founds"    => count($content['found_ids']),
                    "finished"  => $isFinished,
                    "search_id" => $searchData['search_id']
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