<?php

namespace app\commands\smart_shop_task;

use app\commands\BaseAction;
use app\models\Mall;
use app\models\User;
use app\models\UserInfo;
use app\plugins\smart_shop\models\Order;

class UserMobileBindAction extends BaseAction {

    public function run(){
        $this->controller->commandOut("UserMobileBindAction start");
        while (true){
            sleep($this->sleepTime);
            try {

                //获取有手机号但是没注册绑定本地用户的记录
                $rows = Order::find()->alias("o")
                    ->leftJoin(["u" => User::tableName()], "u.mobile=o.pay_user_mobile")
                    ->orderBy("o.updated_at ASC")
                    ->andWhere([
                        "AND",
                        "o.pay_user_mobile <> '' AND o.pay_user_mobile IS NOT NULL AND o.is_delete=0",
                        "u.id IS NULL"
                    ])->asArray()->select(["o.id", "o.mall_id", "o.pay_user_mobile"])->limit(1)->all();
                if(!$rows){
                    $this->negativeTime();
                    continue;
                }

                $orderIds = [];
                foreach($rows as $row){
                    $orderIds[] = $row['id'];
                }
                Order::updateAll(["updated_at" => time()], "id IN (".implode(",", $orderIds).")");

                $this->activeTime();

                foreach($rows as $row){
                    $nickname = substr($row['pay_user_mobile'], -4) . uniqid();
                    $user = new User();
                    $user->username         = $nickname;
                    $user->mobile           = $row['pay_user_mobile'];
                    $user->mall_id          = $row['mall_id'];
                    $user->access_token     = \Yii::$app->security->generateRandomString();
                    $user->auth_key         = \Yii::$app->security->generateRandomString();
                    $user->nickname         = $nickname;
                    $user->password         = \Yii::$app->getSecurity()->generatePasswordHash(uniqid());
                    $user->avatar_url       = "/";
                    $user->last_login_at    = time();
                    $user->login_ip         = "#";
                    $user->parent_id        = GLOBAL_PARENT_ID;
                    $user->second_parent_id = 0;
                    $user->third_parent_id  = 0;

                    if (!$user->save()) {
                        throw new \Exception(json_encode($user->getErrors()));
                    }

                    $userInfoModel = new UserInfo();
                    $userInfoModel->mall_id       = $row['mall_id'];
                    $userInfoModel->mch_id        = 0;
                    $userInfoModel->user_id       = $user->id;
                    $userInfoModel->unionid       = "";
                    $userInfoModel->openid        = "";
                    $userInfoModel->platform_data = "";
                    $userInfoModel->platform      = "mp-wx";
                    if (!$userInfoModel->save()) {
                        throw new \Exception(json_encode($userInfoModel->getErrors()));
                    }

                    $this->controller->commandOut("手机号“".$user->mobile."”绑定用户ID:" . $user->id);
                }

            }catch (\Exception $e){
                throw $e;
            }
        }
    }

}