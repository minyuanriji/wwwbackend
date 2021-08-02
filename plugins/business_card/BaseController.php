<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-28
 * Time: 10:09
 */

namespace app\plugins\business_card;

use app\core\ApiCode;
use app\plugins\ApiController;
use app\plugins\business_card\behavior\BusinessCardBehavior;
use app\plugins\business_card\controllers\api\filters\BusinessCardFilter;
use app\plugins\business_card\models\BusinessCard;

class BaseController extends ApiController
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'track' =>[
                'class' => BusinessCardBehavior::class,
                'user_id' => \Yii::$app->user->id,
            ],
            'cardVerify' =>[
                'class' => BusinessCardFilter::class,
                'id' => isset($this->requestData["id"]) ? $this->requestData["id"] : 0,
            ]
        ]);
    }

    public function init()
    {
        parent::init();
    }
}