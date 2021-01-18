<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 黑名单消息类
 * Author: zal
 * Date: 2020-07-24
 * Time: 19:11
 */

namespace app\core\jmessage;

use JMessage\IM\Blacklist;
use JMessage\IM\User;

class JmessageBlackListModule extends JmessageModule
{
    public $blackListModel;
    public $jm;

    public function __construct()
    {
        $this->blackListModel = new Blacklist($this->jm);
    }

    /**
     * 查询
     * @param $jm
     * @param $mobile
     * @param $message
     * @return string
     */
    public function getList($userId)
    {
        $blacklist = new Blacklist($this->jm);
        $user = 'user_'.$userId;

        $response = $blacklist->listAll($user);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 添加黑名單
     * @param $userId
     * @return bool|mixed
     */
    public function add($userId){
        $user = 'user_'.$userId;
        $response = $this->blackListModel->add($user, ['user_'.$userId]);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 删除黑名单
     * @param $userId
     * @return bool|mixed
     */
    public function remove($userId){
        $user = 'user_'.$userId;
        $response = $this->blackListModel->remove($user, ['user_'.$userId]);
        $result = self::parsingResponse($response);
        return $result;
    }
}
