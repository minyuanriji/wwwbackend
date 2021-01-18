<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 18:06
 */

namespace app\plugins\area\forms\common;


use app\models\BaseModel;
use app\models\User;

class AreaTeamCommon extends BaseModel
{

    public $mall;
    public $userInfo;

    public function info($userId, $status = 1)
    {
        if (!$this->userInfo) {
            /* @var User $user */
            $user = User::find()->with(['thirdChildren'])->where(['user_id' => $userId])->one();
            $this->userInfo = $user;
        }
        $userInfo = $this->userInfo;
        $userList = [];
        switch ($status) {
            case 1:
                if (is_array($userInfo->firstChildren)) {
                    $userList = array_column($userInfo->firstChildren, 'user_id');
                }
                break;
            case 2:
                if (is_array($userInfo->secondChildren)) {
                    $userList = array_column($userInfo->secondChildren, 'user_id');
                }
                break;
            case 3:
                if (is_array($userInfo->thirdChildren)) {
                    $userList = array_column($userInfo->thirdChildren, 'user_id');
                }
                break;
            default:
                $userList = [$userId];
        }
        return $userList;
    }

}