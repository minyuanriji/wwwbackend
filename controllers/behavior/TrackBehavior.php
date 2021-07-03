<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 行为轨迹记录
 * Author: zal
 * Date: 2020-07-14
 * Time: 14:12
 */

namespace app\controllers\behavior;

use app\logic\AdminLogic;
use app\plugins\business_card\forms\common\BusinessCardTrackLogCommon;
use app\plugins\business_card\models\BusinessCardTrackLog;
use yii\base\ActionFilter;
use Yii;

class TrackBehavior extends ActionFilter
{

    public $params;
    /**
     * 轨迹路由，只有以下路由才添加行为轨迹
     * @var array
     */
    private $safeRoute = [
        'api/goods/detail' => BusinessCardTrackLog::TRACK_TYPE_GOODS,
        'api/index/index' => BusinessCardTrackLog::TRACK_TYPE_MALL_INDEX,
        'plugin/short_video/api/video/video-remark' => BusinessCardTrackLog::TRACK_TYPE_LOOK_VIDEO
    ];

    public function beforeAction($action)
    {
        $routeUrl = \Yii::$app->requestedRoute;
        $id = isset($this->params["id"]) ? $this->params["id"] : 0;
        $videoId = isset($this->params["video_id"]) ? $this->params["video_id"] : 0;
        foreach ($this->safeRoute as $key => $value){
            if($routeUrl == $key){
                $trackType = $this->safeRoute[$routeUrl];
                $trackUserId = empty($this->params['x-parent-id'][0]) ? 0 : $this->params['x-parent-id'][0];
                if(strpos($key,"short_video") !== false){
                    $id = $videoId;
                }
                BusinessCardTrackLogCommon::addTrackLog($trackUserId,$id,$trackType);
            }
        }
        return true;
    }
}
