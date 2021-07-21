<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-12 15:06
 */
namespace app\clouds\base\auth;


use yii\base\BaseObject;

abstract class Auth extends BaseObject
{
    /**
     * 是否授权成功
     * @return boolean
     */
    abstract public function pass();
}