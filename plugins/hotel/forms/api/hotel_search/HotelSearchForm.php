<?php
namespace app\plugins\hotel\forms\api\hotel_search;


use app\models\BaseModel;
use app\plugins\hotel\models\HotelSearch;

class HotelSearchForm extends BaseModel{
    /**
     * 生成本次搜索批次ID
     * @params array $attrs
     * @return string
     */
    public static function generateSearchId($attrs){
        ksort($attrs);
        return "HotelSearch:" . strtolower(md5(json_encode($attrs)));
    }

    /**
     * @param string $searchId
     * @return string
     */
    public static function jobListCacheKey($searchId){
        return "HotelSearchJobList:" . $searchId;
    }

    /**
     * 第一步-写入预查询酒店ID数据
     * @param array $hotelIds
     * @param array $attrs
     * @return string
     */
    protected function start($hotelIds, $attrs){
        $prepareId = uniqid();
        $searchId = static::generateSearchId($attrs);

        static::prepareBindSearch($prepareId, $searchId);

        $search = HotelSearch::findone([
            "search_id" => $searchId
        ]);

        //只能有一个客户端搜索任务在执行
        if (!$search || !$search->is_running) {
            if (!$search) {
                $search = new HotelSearch([
                    'mall_id'    => \Yii::$app->mall->id,
                    'search_id'  => $searchId,
                    'created_at' => time()
                ]);
            }

            $taskData = [
                'found_ids'  => [],
                'hotel_ids'  => $hotelIds,
                'attrs'      => $attrs
            ];
            $search->is_running = 1;
            \Yii::$app->getCache()->set(static::jobListCacheKey($searchId), null);
            static::updateSearchTaskData($search, $taskData);
        }

        return [$prepareId, $searchId];
    }

    /**
     * 客户端预查询ID绑定查询号
     * @param string $prepareId
     * @param string $searchId
     * @return void
     */
    private static function prepareBindSearch($prepareId, $searchId){
        $cacheKey = "HotelSearchPrepare:" . $prepareId;
        \Yii::$app->getCache()->set($cacheKey, $searchId);
    }

    /**
     * 通过客户端预查询ID获取查询号
     * @param $prepareId
     * @return string|null
     */
    protected static function getBindSearchIdByPrepareId($prepareId){
        $cacheKey = "HotelSearchPrepare:" . $prepareId;
        return \Yii::$app->getCache()->get($cacheKey);
    }

    /**
     * 通过客户端预查询ID获取执行中的任务数据
     * @param string $prepareId
     * @return HotelSearch|null
     */
    protected static function getSearchDataByPrepareId($prepareId){
        $searchId = static::getBindSearchIdByPrepareId($prepareId);
        return static::getSearchData($searchId);
    }

    /**
     * 更新搜索任务数据
     * @param HotelSearch $search
     * @param array $taskData
     */
    protected static function updateSearchTaskData(HotelSearch $search, $taskData){
        $content = !empty($search->content) ? json_decode($search->content, true) : [];
        $content = array_merge($content, $taskData);
        $search->updated_at = time();
        $search->content    = json_encode($content);

        //先写入缓存再保存数据库
        \Yii::$app->getCache()->set($search->search_id, $search->getAttributes());

        if(!$search->save()){
            throw new \Exception(json_encode($search->getErrors()));
        }
    }

    /**
     * 获取搜索任务数据
     * @param string $searchId
     * @return array|null
     */
    protected static function getSearchData($searchId){
        $cache = \Yii::$app->getCache();
        $searchData = $cache->get($searchId);
        if(!$searchData){
            $search = HotelSearch::findOne(["search_id" => $searchId]);
            if($search){
                $searchData = $search->getAttributes();
                $cache->set($searchId, $searchData);
            }
        }
        return $searchData ? $searchData : null;
    }

    /**
     * 查询结束
     * @param string $searchId
     * @return void
     */
    protected static function finish(HotelSearch $search){
        $search->is_running = 0;
        $searchData = static::getSearchData($search->search_id);
        $content = !empty($searchData['content']) ? json_decode($searchData['content'], true) : [];
        static::updateSearchTaskData($search, $content);
    }
}