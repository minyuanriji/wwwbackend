<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户消息类
 * Author: zal
 * Date: 2020-04-24
 * Time: 09:11
 */

namespace app\core\jmessage;

use JMessage\IM\User;

class JmessageUserModule extends JmessageModule
{
    public $userModel;
    public $jm;

    public function __construct()
    {
        $this->userModel = new User($this->jm);
    }

    /**
     * 查询
     * @param $userId
     * @return string
     */
    public function addSingleNodisturb($userId)
    {
        $user = "user_{$userId}";
        $response = $this->userModel->addSingleNodisturb($user, [$user]);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 移除
     * @param $userId
     * @return bool|mixed
     */
    public function removeSingleNodisturb($userId){
        $user = 'user_'.$userId;
        $response = $this->userModel->removeSingleNodisturb($user, [$user]);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 打开
     * @param $userId
     * @return array|string
     */
    public function openGlobalNodisturb($userId){
        $user = 'user_'.$userId;
        $response = $this->userModel->openGlobalNodisturb($user);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 关闭
     * @param $userId
     * @return array|string
     */
    public function closeGlobalNodisturb($userId){
        $user = 'user_'.$userId;
        $response = $this->userModel->closeGlobalNodisturb($user);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 批量注册
     * @param $users
     * @return bool|mixed
     */
    public function batchRegister($users){

//        $users = [
//            ['username' => 'user_0', 'password' => 'password'],
//            ['username' => 'user_1', 'password' => 'password'],
//            ['username' => 'user_2', 'password' => 'password'],
//            ['username' => 'user_3', 'password' => 'password'],
//            ['username' => 'user_4', 'password' => 'password'],
//            ['username' => 'user_5', 'password' => 'password'],
//            ['username' => 'user_6', 'password' => 'password'],
//            ['username' => 'user_7', 'password' => 'password'],
//            ['username' => 'user_8', 'password' => 'password'],
//            ['username' => 'user_9', 'password' => 'password'],
//            ['username' => 'user_10', 'password' => 'password']
//        ];

        $response = $this->userModel->batchRegister($users);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 注册
     * @param $userId
     * @return bool|mixed
     */
    public function register($userId){
        $username = 'username_'.$userId;
        $password = 'jx8888';
        $response = $this->userModel->register($username, $password);
        $result = self::parsingResponse($response);
        return $result;
    }

    public function getList($limit = 100){
        $response = $this->userModel->listAll($limit);
        $result = self::parsingResponse($response);
        return $result;
    }

    public function show($userId){
        $response = $this->userModel->show("user_".$userId);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 更新
     * @param $userId
     * @param $updateData 要更新的字段数据 ['nickname' => 'user_nickname_0', 'gender' => 2]
     * @return bool|mixed
     */
    public function update($userId,$updateData){
        $response = $this->userModel->update("user_".$userId,$updateData);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 统计
     * @param $userId
     * @param $updateData
     * @return bool|mixed
     */
    public function stat($userId,$updateData){
        $response = $this->userModel->stat("user_".$userId);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 修改密码
     * @param $userId
     * @param $password
     * @return bool|mixed
     */
    public function updatePassword($userId,$password){
        $response = $this->userModel->updatePassword("user_".$userId,$password);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 删除用户
     * @param $userId
     * @return bool|mixed
     */
    public function delete($userId){
        $response = $this->userModel->delete("user_".$userId);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 查看所在组
     * @param $userId
     * @return bool|mixed
     */
    public function groups($userId){
        $response = $this->userModel->groups("user_".$userId);
        $result = self::parsingResponse($response);
        return $result;
    }

}
