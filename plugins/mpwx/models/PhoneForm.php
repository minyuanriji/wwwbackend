<?php

namespace app\plugins\mpwx\models;

use app\plugins\mpwx\Plugin;

class PhoneForm extends \app\forms\api\PhoneForm
{

    public function getPhone()
    {
        $plugin = new Plugin();
        $postData = \Yii::$app->request->post();
        $data = $plugin->getWechat()->decryptData(
            $postData['encryptedData'],
            $postData['iv'],
            $postData['code']
        );
        return $data;
    }
}
