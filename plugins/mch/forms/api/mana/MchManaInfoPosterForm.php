<?php
namespace app\plugins\mch\forms\api\mana;

use app\forms\api\poster\SharePosterForm;
use app\models\User;
use app\plugins\mch\controllers\api\mana\MchAdminController;

class MchManaInfoPosterForm extends SharePosterForm {

    public $route;

    public function rules(){
        return [
            [['route'], 'required'],
            [['route'], 'string']
        ];
    }

    protected function optionDiff($option, $default): array{
        $option = parent::optionDiff($option, $default);
        $option['name']['text'] = MchAdminController::$adminUser['store']['name'];
        $option['qr_code']['size'] = 200;
        $option['qr_code']['top'] -= 50;
        $option['name']['top']    -= 50;
        $option['head']['top']    -= 50;
        return $option;
    }

    public function getSharePoster(){

        //设置商户绑定的小程序账号
        $user = User::findOne(MchAdminController::$adminUser['mch']['user_id']);
        \Yii::$app->user->setIdentity($user);

        return parent::get($this->route);
    }

    protected function h5Path(){
        $mallId = MchAdminController::$adminUser['mall_id'];
        $pid    = MchAdminController::$adminUser['mch']['user_id'];
        $mchId  = MchAdminController::$adminUser['mch_id'];
        $path = "/h5/#/" . $this->route . "?mall_id=" . $mallId . "&pid=" . $pid . "&source=".User::SOURCE_SHARE_POSTER . "&mch_id=" . $mchId;
        return $path;
    }

    protected function h5Dir(){
        $mallId = MchAdminController::$adminUser['mall_id'];
        $pid    = MchAdminController::$adminUser['mch']['user_id'];
        $mchId  = MchAdminController::$adminUser['mch_id'];
        $dir = 'mch-share/' . md5(strtolower($this->route)) . "_" . $mallId."_".$pid. '.jpg';
        return $dir;
    }
}