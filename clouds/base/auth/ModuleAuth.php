<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-12 15:22
 */
namespace app\clouds\auth;


use app\clouds\base\helpers\IdentityHelper;
use app\clouds\base\module\Module;

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

    }
}