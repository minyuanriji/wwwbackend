<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-12 15:06
 */
namespace app\clouds\base\auth;


use app\clouds\base\action\Action;
use app\clouds\base\helpers\IdentityHelper;
use app\clouds\base\tables\CloudAuthActionList;

class ActionAuth extends Auth
{
    private $action;

    public function __construct(Action $action, $config = [])
    {
        parent::__construct($config);
        $this->action = $action;
    }

    /**
     * 是否授权成功
     * @return boolean
     */
    public function pass()
    {
        $access = false;
        $identity = IdentityHelper::getIdentity();
        if($this->action->getModel()->security == "public" || ($identity && $identity->getId() == $this->action->getModel()->author_id)){
            $access = true;
        }elseif($identity && $this->action->getModel()->security == "authorize"){
            $authItem = CloudAuthActionList::findOne([
                "target_id" => $this->action->getModel()->id,
                "user_id"   => $identity->getId()
            ]);
            if($authItem){
                $access = true;
            }
        }
        return $access;
    }
}