<?php
namespace app\plugins\hotel\forms\api\hotel_search;


use app\models\BaseModel;
use app\plugins\hotel\models\HotelSearch;

class HotelSearchForm extends BaseModel{

    /**
     * 生成本次搜索批次ID
     * @return string
     */
    public function generateSearchId(){
        $attrs = $this->getAttributes();
        ksort($attrs);
        return "HotelSearch:" . strtolower(md5(json_encode($attrs)));
    }

    /**
     * 把查询到的酒店写入到临时缓存中
     * @param string $searchId
     * @param string $prepareId
     * @param array $founds
     * @return integer
     */
    public function pushFound($searchId, $prepareId, $founds){
        $cache = \Yii::$app->getCache();
        $foundData = $this->getFoundData($searchId);
        if(!$foundData || !isset($foundData[$prepareId])){
            $foundData[$prepareId] = [];
        }
        $foundData[$prepareId] = array_merge($foundData[$prepareId], $founds);
        $cache->set($searchId, $foundData, 3600 * 24);
        return count($foundData[$prepareId]);
    }

    /**
     * 更新查询到的酒店
     * @param string $searchId
     * @param string $prepareId
     */
    public function updateFound($searchId, $prepareId){
        if(empty($searchId))
            return;

        $foundData = $this->getFoundData($searchId);
        $hotelIds = isset($foundData[$prepareId]) && is_array($foundData[$prepareId]) ? $foundData[$prepareId] : [];

        $search = HotelSearch::findone([
            "search_id" => $searchId
        ]);
        if(!$search){
            $search = new HotelSearch([
                'mall_id' => \Yii::$app->mall->id,
                'search_id' => $searchId,
                'created_at' => time(),
                'updated_at' => time()
            ]);
        }

        //如果上一次查询距离本次日期超过1小时
        if((time() - $search->updated_at) > 3600 * 6){
            $search->content = "";
        }

        $oldHotelIds = !empty($search->content) ? (array)json_decode($search->content, true) : [];
        $search->content = json_encode(array_unique(array_merge($oldHotelIds, $hotelIds)));
        $search->updated_at = time();
        $search->save();

        static::removeSearchTask($searchId);
    }

    /**
     * 添加搜索任务
     * @param $searchId
     * @param $prepareId
     */
    public function addSearchTask($searchId){
        $cache = \Yii::$app->getCache();
        $cacheKey = "HotelSearchTask";
        $foundData = $this->getFoundData($searchId);
        $taskData = $cache->get($cacheKey);
        $taskData[$searchId] = $foundData['newest_prepare_id'];
        $cache->set($cacheKey, $taskData);
    }

    /**
     * 获取所有搜索任务数据
     * @return array|mixed
     */
    public static function getAllSearchTaskDatas(){
        $cache = \Yii::$app->getCache();
        $cacheKey = "HotelSearchTask";
        $taskData = $cache->get($cacheKey);
        return !empty($taskData) && is_array($taskData) ? $taskData : [];
    }

    /**
     * 移除搜索任务
     * @param string $searchId
     */
    public static function removeSearchTask($searchId){
        $cache = \Yii::$app->getCache();
        $cacheKey = "HotelSearchTask";
        $taskData = $cache->get($cacheKey);
        if(isset($taskData[$searchId])){
            unset($taskData[$searchId]);
        }
        $cache->set($cacheKey, $taskData);
    }

    /**
     * 通过prepareId移除搜索任务
     * @param string $prepareId
     */
    public static function removeSearchTaskByPrepareId($prepareId){
        $taskData = static::getAllSearchTaskDatas();
        $taskData = array_flip($taskData);
        if(isset($taskData[$prepareId])){
            $searchId = $taskData[$prepareId];
            static::removeSearchTask($searchId);
        }
    }

    /**
     * 获取查询到的酒店ID
     * @param string $searchId
     * @return array
     */
    public function getFoundHotelIds($searchId){
        $search = HotelSearch::findOne([
            "search_id" => $searchId
        ]);
        $hotelIds = [];
        if($search && !empty($search->content)){
            $hotelIds = @json_decode($search->content, true);
        }
        return $hotelIds;
    }

    /**
     * 获取查询到酒店ID数据
     * @param $searchId
     * @return array
     */
    public function getFoundData($searchId){
        $cache = \Yii::$app->getCache();
        return $cache->get($searchId);
    }

    /**
     * 写入预查询酒店ID数据
     * @param $hotelIds
     * @param null $prepareId
     * @return string|null
     */
    protected function writePrepareData($hotelIds, $prepareId = null){
        $cache = \Yii::$app->getCache();
        if(!empty($prepareId)){
            $data = $cache->get($this->prepareCacheKey($prepareId));
            $data['hotel_ids'] = $hotelIds;
            $cache->set($this->prepareCacheKey($prepareId), $data, 3600 * 24);
        }else{
            $prepareId = uniqid();
            $searchId = $this->generateSearchId();
            $cache->set($this->prepareCacheKey($prepareId), [
                "hotel_ids"  => $hotelIds,
                "init_attrs" => $this->getAttributes(),
                "search_id"  => $searchId
            ], 1800);
            $foundData = $this->getFoundData($searchId);
            $foundData[$prepareId] = [];
            $foundData['newest_prepare_id'] = $prepareId;
            $cache->set($searchId, $foundData, 3600 * 24);
        }

        return $prepareId;
    }

    /**
     * 弹出部分预查询酒店ID数据
     * @param $prepareId
     * @return array
     */
    public function popPrepareData($prepareId){
        $cache = \Yii::$app->getCache();

        $data = $cache->get($this->prepareCacheKey($prepareId));
        $hotelIds = isset($data['hotel_ids']) ? $data['hotel_ids'] : [];
        $attrs    = isset($data['init_attrs']) ? $data['init_attrs'] : [];
        $searchId = isset($data['search_id']) ? $data['search_id'] : "";

        $popIds = [];

        if(!empty($hotelIds)){
            $count = count($hotelIds);
            $length = $count <= 5 ? $count : 5;
            $popIds = array_slice($hotelIds, 0, $length);
            $hotelIds = array_slice($hotelIds, $length);
            sort($hotelIds);
            $this->writePrepareData($hotelIds, $prepareId);
        }
        return [
            "hotel_ids" => $popIds,
            "attrs"     => $attrs,
            "search_id" => $searchId
        ];
    }

    private function prepareCacheKey($prepareId){
        return "HotelSearchPrepare:" . $prepareId;
    }
}