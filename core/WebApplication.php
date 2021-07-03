<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-01
 * Time: 20:56
 */

namespace app\core;

use app\models\Wechat;
use yii\helpers\ArrayHelper;

/**
 * Class WebApplication
 * @package app\core
 */
class WebApplication extends \yii\web\Application
{
    use Application;

    private $appIsRunning = true;

    const MALL_SESSION_KEY_PREFIX = "JXMall_Id_Key_";

    public function __construct($config = null)
    {
        $this->loadDotEnv()->defineConstants();
        require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
        if (!$config) {
            $config = ArrayHelper::merge(
                require __DIR__ . '/../config/web.php',
                file_exists(__DIR__ . '/../config/web-local.php') ? require __DIR__ . '/../config/web-local.php' : []
            );
        }
        parent::__construct($config);
        //涉及到Yii app 的配置需要配置到上面这行下面
        $this->loadErrorReporting()->registerHandlers()->responseAsJson()
            ->loadAppLogger()->loadAppPlugins();
    }

    /**
     * 设置商城会话key
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @param int $id
     * @return bool|null|string
     */
    public function setSessionJxMallId($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $keyOne = md5(self::MALL_SESSION_KEY_PREFIX . "One_" . date('Ym'));
        $keyTwo = md5(self::MALL_SESSION_KEY_PREFIX . "Two_" . date('Ym'));
        $value1 = base64_encode(\Yii::$app->security->encryptByPassword($id, 'key' . $keyOne));
        $value2 = base64_encode(\Yii::$app->security->encryptByPassword('0' . $id, 'key' . $keyTwo));
        $this->getSession()->set($keyOne, $value1);
        $this->getSession()->set($keyTwo, $value2);

    }

    /**
     * 获取商城会话key
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @param null $defaultValue
     * @return bool|null|string
     */
    public function getSessionJxMallId($defaultValue = null)
    {
        $keyOne = md5(self::MALL_SESSION_KEY_PREFIX.'One_' . date('Ym'));
        $encodeDataBase64 = $this->getSession()->get($keyOne, null);
        if ($encodeDataBase64 === null) {
            return $defaultValue;
        }
        $encodeData = base64_decode($encodeDataBase64);
        if (!$encodeData) {
            return $defaultValue;
        }
        $value = \Yii::$app->security->decryptByPassword($encodeData, 'key' . $keyOne);
        if (!$value) {
            return $defaultValue;
        }
        \Yii::warning("webApplication getSessionJxMallId value={$value}");
        return $value;
    }

    /**
     * 删除商城会话key
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     */
    public function removeSessionJxMallId()
    {
        $keyOne = md5(self::MALL_SESSION_KEY_PREFIX.'One_' . date('Ym'));
        $keyTwo = md5(self::MALL_SESSION_KEY_PREFIX.'Two_' . date('Ym'));
        \Yii::$app->session->remove($keyOne);
        \Yii::$app->session->remove($keyTwo);
    }
}