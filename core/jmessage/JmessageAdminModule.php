<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 后台管理员消息类
 * Author: zal
 * Date: 2020-07-24
 * Time: 20:11
 */

namespace app\core\jmessage;


use app\core\jmessage\JmessageModule;
use JMessage\IM\Admin;

class JmessageAdminModule extends JmessageModule
{
    public $adminModel;
    public $jm;

    public function __construct()
    {
        $this->adminModel = new Admin($this->jm);
    }

    /**
     * 查询
     * @param $jm
     * @param $mobile
     * @param $message
     * @return string
     */
    public function selectAll()
    {
        $response = $this->adminModel->listAll(10);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 添加
     * @param $info ['username' => 'admin','password' => 'password'];
     * @return array|string
     */
    public function add($info){
        $response = $this->adminModel->register($info);
        $result = self::parsingResponse($response);
        return $result;
    }
}
