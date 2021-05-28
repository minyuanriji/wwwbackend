<?php


namespace app\forms\common;


use app\models\BaseModel;

class WebSocketRequestForm extends BaseModel{

    public $action;
    public $notify_mobile;
    public $notify_data;

    const WEB_SOCKET_REQUEST_LIST_CACHE_KEY = "WebSocketRequestListCacheKey";

    public function rules(){
        return [
            [['action', 'notify_mobile', 'notify_data'], 'required']
        ];
    }

    public static function one(){
        $cache = \Yii::$app->getCache();
        $list = $cache->get(self::WEB_SOCKET_REQUEST_LIST_CACHE_KEY);
        $list = !empty($list) && is_array($list) ? $list : [];

        if(!empty($list)){
            $item = array_shift($list);
            $cache->set(self::WEB_SOCKET_REQUEST_LIST_CACHE_KEY, $list);
            return new WebSocketRequestForm($item);
        }

        return null;
    }

    public static function add(WebSocketRequestForm $form){
        $cache = \Yii::$app->getCache();
        $list = $cache->get(self::WEB_SOCKET_REQUEST_LIST_CACHE_KEY);
        $list = !empty($list) && is_array($list) ? $list : [];
        $list[] = [
            'action'        => $form->action,
            'notify_mobile' => $form->notify_mobile,
            'notify_data'   => $form->notify_data
        ];
        $cache->set(self::WEB_SOCKET_REQUEST_LIST_CACHE_KEY, $list);
    }
}