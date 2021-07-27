<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 触发添加标签事件，是否满足标签条件
 * Author: zal
 * Date: 2020-07-14
 * Time: 14:12
 */

namespace app\controllers\behavior;

use app\events\TagEvent;
use app\events\UserInfoEvent;
use app\handlers\RelationHandler;
use app\handlers\TagHandler;
use app\plugins\business_card\forms\common\BusinessCardTrackLogCommon;
use app\plugins\business_card\models\BusinessCardTrackLog;
use yii\base\ActionFilter;
use Yii;

class AddTagBehavior extends ActionFilter
{

    public $params;
    /**
     * 轨迹路由，只有以下路由才添加行为轨迹
     * @var array
     */
    private $safeRoute = [
        'api/identity/register' => 2,
        'api/identity/mini-login' => 2,
        'api/identity/auth-login' => 2,
        'plugin/business_card/api/business-card/like' => [3,4],
        'plugin/business_card/api/track-log/add-track' => 4,
        'api/index/index' => 4,
        'api/goods/detail' => 4,
        'plugin/business_card/api/business-card/my' => 4,
        'api/collect/add' => 4,
        'api/payment/do-pay' => [1,2,4],
    ];

    public function beforeAction($action)
    {
        $routeUrl = \Yii::$app->requestedRoute;
        Yii::warning("AddTagBehavior routeUrl={$routeUrl}");
        foreach ($this->safeRoute as $key => $value){
            if($routeUrl == $key){
                $type = $this->safeRoute[$routeUrl];
                \Yii::$app->trigger(TagHandler::ADD_TAG, new TagEvent([
                    'user_id' => \Yii::$app->user->id,
                    'mall_id' => \Yii::$app->mall->id,
                    'cat_id' => 1,
                    'type' => $type
                ]));
            }
        }

        return true;
    }
}
