<?php
namespace app\helpers;

use app\core\ApiCode;

class APICacheHelper{

    //获取商城设置接口
    const API_MALL_CONFIG                = "api_mall_config";
    const API_INDEX_INDEX                = "api_index_index";
    const PLUGIN_DIY_API_PAGE_DETAIL     = "plugin/diy/api/page/detail";
    const API_GOODS_RRECOMMAND           = "api/goods/recommend";
    const API_CAT_LIST                   = "api/cat/list";
    const API_GOODS_LIST                 = "api/goods/list";
    const API_GOODS_DETAIL               = "api/goods/detail";
    const MCH_API_GET_MCHS_CATAS         = "mch/api/get-mchs-cats";
    const MCH_API_GET_MCHS               = "mch/api/get-mchs";
    const PLUGIN_BAOPIN_API_GOODS_SEARCH = "plugin/baopin/api/goods/search";
    const MCH_API_GET_MCH_GOODS_CATS     = "mch/api/get-mch-goods-cats";
    const MCH_API_GET_MCH_GOODS          = "mch/api/get-mch-goods";
    const MCH_API_GET_MCH_STORE          = "mch/api/get-mch-store";

    public static function setting(){
        $params[self::MCH_API_GET_MCH_STORE] = [
            'expire'  => 24 * 3600,
            'headers' => [],
            'gets'    => [],
            'posts'   => ["mch_id"]
        ];
        $params[self::MCH_API_GET_MCH_GOODS] = [
            'expire'  => 3600,
            'headers' => [],
            'gets'    => [],
            'posts'   => ["page", "keyword", "mch_id", "limit", "cat_id", "label"]
        ];
        $params[self::MCH_API_GET_MCH_GOODS_CATS] = [
            'expire'  => 3600,
            'headers' => [],
            'gets'    => [],
            'posts'   => ["mch_id"]
        ];
        $params[self::PLUGIN_BAOPIN_API_GOODS_SEARCH] = [
            'expire'  => 3600,
            'headers' => [],
            'gets'    => [],
            'posts'   => ["mch_id", "keyword", "limit", "page", "sort_prop", "sort_type"]
        ];
        $params[self::MCH_API_GET_MCHS] = [
            'expire'  => 3600,
            'headers' => ["x-latitude", "x-longitude", "x-city-id"],
            'gets'    => [],
            'posts'   => ["cat_id", "page", "keyword", "effect", "lat", "lnt"]
        ];
        $params[self::MCH_API_GET_MCHS_CATAS] = [
            'expire'  => 24 * 3600,
            'headers' => [],
            'gets'    => [],
            'posts'   => []
        ];
        $params[self::API_GOODS_DETAIL] = [
            'expire'  => 3600,
            'headers' => [],
            'gets'    => ["id"],
            'posts'   => []
        ];
        $params[self::API_GOODS_LIST] = [
            'expire'  => 3600,
            'headers' => [],
            'gets'    => ["cat_id", "keyword", "page", "limit", "label"],
            'posts'   => []
        ];
        $params[self::API_CAT_LIST] = [
            'expire'  => 24 * 3600,
            'headers' => [],
            'gets'    => [],
            'posts'   => []
        ];
        $params[self::API_GOODS_RRECOMMAND] = [
            'expire'  => 24 * 3600,
            'headers' => ["x-access-token", "x-city-id", "x-latitude", "x-longitude"],
            'gets'    => ["page", "type", "goods_id"],
            'posts'   => []
        ];
        $params[self::PLUGIN_DIY_API_PAGE_DETAIL] = [
            'expire'  => 24 * 3600,
            'headers' => [],
            'gets'    => ["id"],
            'posts'   => []
        ];
        $params[self::API_MALL_CONFIG] = [
            'expire'  => 24 * 3600,
            'headers' => [],
            'gets'    => [],
            'posts'   => []
        ];
        $params[self::API_INDEX_INDEX] = [
            'expire'  => 24 * 3600,
            'headers' => [],
            'gets'    => [],
            'posts'   => []
        ];
        return $params;
    }

    /**
     * 无感更新
     */
    public static function silentUpdate(){

    }

    /**
     * @param $key
     * @param \Closure $callable
     * @return array|mixed|null
     */
    public static function get($key, \Closure $callable){
        $headers = \Yii::$app->request->headers;

        $settings = static::setting();
        $setting = isset($settings[$key]) ? $settings[$key] : null;

        //头部
        $paramsKey = array_merge(isset($setting['headers']) ? $setting['headers'] : [], [
            'x-app-platform', 'x-mall-id', 'host'
        ]);
        $params = [];
        foreach($paramsKey as $paramKey){
            if(isset($headers[$paramKey])){
                $params[$paramKey] = $headers[$paramKey];
            }
        }

        //GET参数
        $getKeys = isset($setting['gets']) && is_array($setting['gets']) ? $setting['gets'] : [];
        if(!empty($_GET)){
            $GET = array_change_key_case($_GET, CASE_LOWER);
            foreach($getKeys as $getKey){
                if(isset($GET[$getKey])){
                    $params[$getKey] = $GET[$getKey];
                }
            }
        }

        //POST参数
        $postKeys = isset($setting['posts']) && is_array($setting['posts']) ? $setting['posts'] : [];
        if(!empty($_POST)){
            $POST = array_change_key_case($_POST, CASE_LOWER);
            foreach($postKeys as $postKey){
                if(isset($POST[$postKey])){
                    $params[$postKey] = $POST[$postKey];
                }
            }
        }

        @ksort($params);

        $paramStringArray = [];
        foreach($params as $k => $v){
            $paramStringArray[] = "{$k}={$v}";
        }

        $md5Key = md5(date("ymd") . implode("&", $paramStringArray));

        $cacheObject = \Yii::$app->getCache();
        $cacheDatas = $cacheObject->exists($key) ? $cacheObject->get($key) : [];
        $cacheData = isset($cacheDatas[$md5Key]) ? $cacheDatas[$md5Key] : null;
        $helperFunc = function($data){
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'update_data' => $data
            ];
        };
        if(!$cacheData){
            $result = $callable($helperFunc);
            if(isset($result['code']) && $result['code'] == ApiCode::CODE_SUCCESS){
                $updateData = isset($result['update_data']) ? $result['update_data'] : [];
                $cacheDatas[$md5Key] = $updateData;
                $expire = isset($param['expire']) ? (int)$param['expire'] : 0;
                $cacheObject->set($key, $cacheDatas, $expire >= 0 ? $expire : null);
                $cacheData = $updateData;
            }else{
                $cacheData = $result;
            }
        }
        return $cacheData;
    }
}
