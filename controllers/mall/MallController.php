<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-06
 * Time: 11:50
 */

namespace app\controllers\mall;


use app\controllers\admin\BaseController;
use app\controllers\behavior\AdminPermissionsBehavior;
use app\controllers\behavior\LoginFilter;
use app\logic\AppConfigLogic;
use app\logic\OptionLogic;
use app\models\Admin;
use app\models\Mall;
use app\models\Option;
use app\models\Payment;
use app\models\Wechat;

class MallController extends BaseController
{
    public $layout = 'mall';

    public function init()
    {
        //\Yii::$app->validateCloudFile();
        parent::init();
        if (property_exists(\Yii::$app, 'appIsRunning') === false) {
            exit('property not found.');
        }
        if (mb_stripos(\Yii::$app->requestedRoute, 'mall/plugin/') === 0) {
            return;
        }
        $this->loadMall();

    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'loginFilter' => [
                'class' => LoginFilter::class,
            ],
            'permissions' => [
                'class' => AdminPermissionsBehavior::class,
            ],
        ]);
    }

    /**
     * 加载商城
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:55
     * @return $this
     */
    private function loadMall()
    {
        $id = \Yii::$app->getSessionJxMallId();
        if (!$id) {
            $id = \Yii::$app->getMallId();
        }
        /* @var Admin $admin */
        $loginAdmin = \Yii::$app->admin->identity;
        //需考虑特殊情况，存商城id的session突然失效，这时候多加判断，将登录用户的商城id再存一次session
        if (!$id) {
            // 角色为员工时 存储
            /* @var Admin $admin */
            $admin = Admin::findOne(['id' => \Yii::$app->admin->id]);
            if ($admin && $admin->admin_type == Admin::ADMIN_TYPE_OPERATE) {
                $id = $loginAdmin->mall_id;
                \Yii::$app->setSessionJxMallId($id);
            }
        }
        // 角色为商户时,存储
        if ($loginAdmin && $loginAdmin->mch_id) {
            \Yii::$app->mchId = $loginAdmin->mch_id;
            if (!$id) {
                $id = $loginAdmin->mall_id;
                \Yii::$app->setSessionJxMallId($id);
            }
        }
        $url = \Yii::$app->branch->logoutUrl();
        if (!$id) {
            return $this->redirect($url)->send();
        }
        $mall = Mall::find()->where(['id' => $id, 'is_delete' => 0])->with('option')->one();
        if (!$mall) {
            return $this->redirect($url)->send();
        }
        if ($mall->is_delete !== 0 || $mall->is_recycle !== 0) {
            return $this->redirect($url)->send();
        }
        $newOptions = [];
        foreach ($mall['option'] as $item) {
            $newOptions[$item['key']] = $item['value'];
        }
        $mall->options = (object)$newOptions;
        \Yii::$app->mallId = $id;
        \Yii::$app->mall = $mall;
        $this->setWechatParmas($id);
        return $this;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-20
     * @Time: 18:12
     * @Note:设置微信公众号，微信支付
     */
    protected function setWechatParmas($mall_id)
    {
        if (!$mall_id) {
            return;
        }
        $info = Wechat::findOne(['mall_id' => $mall_id, 'is_delete' => 0]);
        if ($info) {
            \Yii::$app->params['wechatConfig'] = [
                'app_id' => $info->app_id,
                'secret' => $info->secret,
                'token' => $info->token,
                'aes_key' => $info->aes_key,
            ];
        }
        $payment = OptionLogic::get(Option::NAME_PAYMENT, $mall_id, Option::GROUP_APP);
        if (!empty($payment) && $payment["wechat_status"] == 1) {
            \Yii::$app->params['wechatPaymentConfig'] = [
                'app_id' => isset($payment['wechat_app_id']) ? $payment['wechat_app_id'] : "",
                'mch_id' => isset($payment['wechat_mch_id']) ? $payment['wechat_mch_id'] : "",
                'key' => isset($payment['wechat_pay_secret']) ? $payment['wechat_pay_secret'] : "",
                'cert_path' => isset($payment['wechat_cert_pem_path']) ? $payment['wechat_cert_pem_path'] : "",
                'key_path' => isset($payment['wechat_key_pem_path']) ? $payment['wechat_key_pem_path'] : "",
                'notify_url' => ''//回调地址
            ];
        }
    }
}