<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 登录过滤器
 * Author: zal
 * Date: 2020-04-17
 * Time: 20:01
 */

namespace app\plugins\business_card\controllers\api\filters;

use app\core\ApiCode;
use app\plugins\business_card\models\BusinessCard;
use yii\base\ActionFilter;

class BusinessCardFilter extends ActionFilter
{
    public $id = 0;

    /**
     * 轨迹路由，只有以下路由才添加行为轨迹
     * @var array
     */
    private $safeRoute = [
        //'plugin/business_card/api/business-card/index',
        'plugin/business_card/api/business-card/to-edit',
        'plugin/business_card/api/business-card/do-edit',
        'plugin/business_card/api/business-card/create',
        'plugin/business_card/api/business-card/add-tag'
    ];

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $result = $this->checkBusinessCardIsExist();
        if($result == false){
            if(!in_array(\Yii::$app->requestedRoute,$this->safeRoute)){
                \Yii::$app->response->data = [
                    'code' => ApiCode::CODE_CARD_NOT_EXIST,
                    'msg' => '请先创建名片！',
                ];
                return false;
            }
        }
        return true;
    }

    /**
     * 检测名片是否存在
     * @return mixed
     */
    public function checkBusinessCardIsExist(){
        $params = [];
        //没有传名片id，查看的是自己的名片
        if(!empty($this->id)){
            $params["id"] = $this->id;
        }else{
            $params["user_id"] = \Yii::$app->user->id;
        }
        $params["mall_id"] = \Yii::$app->mall->id;
        $params["is_one"] = 1;
        $detail = BusinessCard::getData($params);
        if(empty($detail)){
            return false;
        }
        return true;
    }
}
