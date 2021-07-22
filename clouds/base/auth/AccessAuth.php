<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-12 14:39
 */
namespace app\clouds\base\auth;


use app\clouds\base\action\Action;
use app\clouds\base\errors\CloudException;

class AccessAuth extends Auth
{
    private $action;

    public function __construct(Action $action, $config = [])
    {
        parent::__construct($config);
        $this->action = $action;
    }

    /**
     * 授权检验列表
     * @return array
     */
    public function chainList()
    {
        return [
            "app\\clouds\\base\\auth\\ActionAuth"  => $this->action,
            "app\\clouds\\base\\auth\\ModuleAuth"  => $this->action->getModule(),
            "app\\clouds\\base\\auth\\ProjectAuth" => $this->action->getProject()
        ];
    }


    /**
     * 是否通过授权检查
     * @return bool
     */
    public function pass()
    {
        $allowAccess = false;

        foreach($this->chainList() as $authClass => $object)
        {
            if(!class_exists($authClass))
            {
                throw new CloudException("”".$authClass."“授权类不存在");
            }

            $class = new $authClass($object);
            if($class->pass())
            {
                $allowAccess = true;
                break;
            }
        }

        return $allowAccess;
    }
}