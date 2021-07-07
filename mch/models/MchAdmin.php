<?php
namespace app\mch\models;

use app\models\Admin;
use app\plugins\mch\models\Mch;
use Yii;


class MchAdmin extends Admin implements \yii\web\IdentityInterface{

    public $mchModel = null;    //商户对象模型

    /**
     * 商户
     */
    const ADMIN_TYPE_MCH = 4;


    public static function findIdentity($id){
        $adminModel = self::findOne($id);
        return static::createMchAdminObject($adminModel);
    }

    public static function findIdentityByAccessToken($token, $type = null){
        $adminModel = self::findOne(['access_token' => $token]);
        return static::createMchAdminObject($adminModel);
    }

    /**
     * 创建商家登录凭据
     * @return MchAdmin|null
     */
    public static function createMchAdminObject($adminModel){
        if($adminModel && $adminModel->mch_id){
            $mchModel = Mch::find()->with("store")->andWhere(["id" => $adminModel->mch_id])->one();
            if($mchModel){
                $adminModel->mchModel = $mchModel;
            }
        }
        return $adminModel;
    }

}
