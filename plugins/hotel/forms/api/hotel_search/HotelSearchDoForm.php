<?php
namespace app\plugins\hotel\forms\api\hotel_search;


use app\component\lib\LockTools;
use app\core\ApiCode;
use app\plugins\hotel\libs\IPlateform;
use app\plugins\hotel\libs\plateform\BookingListResult;
use app\plugins\hotel\models\Hotels;
use app\plugins\hotel\models\HotelSearch;

class HotelSearchDoForm extends HotelSearchForm{

    public $search_id;

    public function rules(){
        return [
            [['search_id'], 'required']
        ];
    }

    public function run($lock = null){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $lock_name = 'LOCK:HotelSearchTaskDo';
        try {

            //为防止多任务重复执行相同数据，加入锁操作
            if($lock != null){
                while(!$lock->lock($lock_name)){
                   usleep(10);
                }
            }

            $searchData = static::getSearchData($this->search_id);
            $search = HotelSearch::findOne(["search_id" => $this->search_id]);;
            $doHotelIds = [];
            $content = [];
            if($searchData && $searchData['is_running']){
                $content    = !empty($searchData['content']) ? json_decode($searchData['content'], true) : [];
                $hotelIds   = isset($content['hotel_ids']) ? $content['hotel_ids'] : [];
                $count      = count($hotelIds);
                $length     = $count <= 5 ? $count : 5;
                $doHotelIds = array_slice($hotelIds, 0, $length);
                $hotelIds   = array_slice($hotelIds, $length);
                sort($hotelIds);
                $content['hotel_ids'] = $hotelIds;
                $search->content = "";
                static::updateSearchTaskData($search, $content);
            }

            if($searchData && $searchData['is_running'] && empty($doHotelIds)){
                static::finish($search); //无可执行数据，结束任务
            }
            if($lock != null) {
                $lock->unlock($lock_name);
            }

            if($searchData && $searchData['is_running'] && !empty($doHotelIds)){
                $hotels = Hotels::find()->andWhere([
                    "AND",
                    ["is_open"    => 1],
                    ["is_booking" => 1],
                    ["is_delete"  => 0],
                    ["IN", "id", $doHotelIds]
                ])->all();
                if(!empty($hotels)){
                    $founds = [];
                    foreach($hotels as $hotel){
                        $hotelPlateforms = $hotel->getPlateforms();
                        foreach($hotelPlateforms as $hotelPlateform){
                            $className = $hotelPlateform->plateform_class;
                            if(empty($className) || !class_exists($className)) continue;
                            $classObject = new $className();
                            if(!$classObject instanceof IPlateform) continue;
                            $result = $classObject->getBookingList($hotel, $hotelPlateform, $content['attrs']['start_date'], $content['attrs']['days']);
                            if(!$result instanceof BookingListResult)
                                continue;
                            if($result->code != BookingListResult::CODE_SUCC)
                                continue;
                            $bookings = $result->getAll();
                            if($bookings && count($bookings) > 0){
                                $founds[] = $hotel->id;
                                break;
                            }
                        }
                    }
                    if($lock != null) {
                        while(!$lock->lock($lock_name)){
                            usleep(10);
                        }
                    }
                    $searchData = static::getSearchData($this->search_id);
                    if($searchData){
                        $content = !empty($searchData['content']) ? json_decode($searchData['content'], true) : [];
                        $content['found_ids'] = array_unique(array_merge($content['found_ids'], $founds));
                        static::updateSearchTaskData($search, $content);
                    }
                    if($lock != null) {
                        $lock->unlock($lock_name);
                    }
                }

            }

            $searchData = static::getSearchData($this->search_id);
            $data['search_id']    = null;
            $data['finished']     = 0;
            $data['founds']       = 0;
            $data['do_hotel_ids'] = $doHotelIds;
            if($search){
                $content = !empty($searchData['content']) ? json_decode($searchData['content'], true) : [];
                $data['search_id'] = $searchData['search_id'];
                $data['finished']  = $searchData['is_running'] ? 0 : 1;
                $data['founds']    = isset($content['found_ids']) ? count($content['found_ids']) : 0;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $data
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    public function jobListCacheKey(){
        return "HotelSearchJobList:" . $this->search_id;;
    }

    public function addJob(){
        $cache = \Yii::$app->getCache();
        $jobList = $cache->get($this->jobListCacheKey());
        if(!empty($jobList) && is_array($jobList)){
            $jobList[] = posix_getpid();
        }else{
            $jobList = [posix_getpid()];
        }
        $cache->set($this->jobListCacheKey(), $jobList);
    }

    public function removeJob(){
        $cache = \Yii::$app->getCache();
        $jobList = $cache->get($this->jobListCacheKey());
        if(!empty($jobList) && is_array($jobList)){
            $jobList = array_diff($jobList, [posix_getpid()]);
            $cache->set($this->jobListCacheKey(), $jobList);
        }
    }
}