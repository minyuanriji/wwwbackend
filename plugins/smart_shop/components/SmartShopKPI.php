<?php

namespace app\plugins\smart_shop\components;

use app\models\User;
use yii\base\Component;

class SmartShopKPI extends Component{

    /**
     * 绑定邀请者
     * @param User $inviter
     * @param User $user
     */
    public function bindInviter(User $inviter, User $user){}

    /**
     * 分享链接访问统计
     * @param $data
     */
    public function linkGoodsDetail($data){}
}