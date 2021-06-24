<?php
namespace app\plugins\hotel\forms\api\hotel_search;


use app\models\BaseModel;

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
     */
    public function pushFound($searchId, $prepareId, $founds){
        $cache = \Yii::$app->getCache();
        $foundData = $this->getFoundData($searchId);
        if(!$foundData || !isset($foundData[$prepareId])){
            $foundData[$prepareId] = [];
        }
        $foundData[$prepareId] = array_merge($foundData[$prepareId], $founds);
        $cache->set($searchId, $foundData, 3600);
    }

    /**
     * 更新查询到的酒店
     * @param string $searchId
     * @param string $prepareId
     */
    public function updateFound($searchId, $prepareId){
        $cache = \Yii::$app->getCache();
        $foundData = $this->getFoundData($searchId);
        $hotelIds = isset($foundData[$prepareId]) && is_array($foundData[$prepareId]) ? $foundData[$prepareId] : [];
        $foundData['hotel_ids'] = array_unique($hotelIds);
        $cache->set($searchId, $foundData, 3600);
    }

    /**
     * 获取查询到的酒店ID
     * @param string $searchId
     * @return array
     */
    public function getFoundHotelIds($searchId){
        $foundData = $this->getFoundData($searchId);
        $hotelIds = isset($foundData["hotel_ids"]) && is_array($foundData["hotel_ids"]) ? $foundData["hotel_ids"] : [];
        return !empty($hotelIds) && is_array($hotelIds) ? $hotelIds : [];
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
            $cache->set($this->prepareCacheKey($prepareId), $data, 3600);
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
            $cache->set($searchId, $foundData, 3600);
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
        $searchId    = isset($data['search_id']) ? $data['search_id'] : "";

        $popIds = [];

        if(!empty($hotelIds)){
            $count = count($hotelIds);
            $length = $count <= 10 ? $count : 10;
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