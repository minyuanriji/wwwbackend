<?php

namespace app\canal\table;

use app\notification\AddOfflineNotification;

class User
{

    public function insert($rows)
    {
    }

    public function update($mixDatas)
    {
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if (isset($update['parent_id']) && $update['parent_id']) {
                $user = \app\models\User::findone($condition);
                $user && AddOfflineNotification::send($user);
            }
        }
    }
}