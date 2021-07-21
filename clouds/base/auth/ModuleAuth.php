<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-12 15:22
 */
namespace app\clouds\base\auth;


use app\clouds\base\helpers\IdentityHelper;
use app\clouds\base\module\Module;
use app\clouds\base\tables\CloudAuthModuleList;

class ModuleAuth extends Auth
{
    private $module;

    public function __construct(Module $module, $config = [])
    {
        parent::__construct($config);
        $this->module = $module;
    }

    /**
     * 是否授权成功
     * @return boolean
     */
    public function pass()
    {
        $access = false;
        $identity = IdentityHelper::getIdentity();
        if($this->module->getModel()->security == "public" || ($identity && $identity->getId() == $this->module->getModel()->author_id)){
            $access = true;
        }elseif($identity && $this->module->getModel()->security == "authorize"){
            $authItem = CloudAuthModuleList::findOne([
                "target_id" => $this->module->getModel()->id,
                "user_id"   => $identity->getId()
            ]);
            if($authItem){
                $access = true;
            }
        }
        return $access;
    }
}