<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-02
 * Time: 12:56
 */

namespace app\handlers;


use yii\base\BaseObject;


/**
 * Class BaseHandler
 * @package app\handlers
 *
 */
abstract class BaseHandler extends BaseObject
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-02
     * @Time: 12:57
     * @Note:所有的事件都要通过此方法进去注册
     * @return mixed
     */
     abstract public function register();

}