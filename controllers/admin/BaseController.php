<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-08
 * Time: 14:13
 */

namespace app\controllers\admin;


use app\controllers\admin\behaviors\PermissionsBehavior;
use app\controllers\admin\behaviors\RoleUserBehavior;
use app\controllers\behavior\AdminPermissionsBehavior;
use app\controllers\behaviors\LoginFilter;
use app\core\ApiCode;
use Yii;

class BaseController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;
    public $pageSize = 10;
    public function init()
    {
        $res = \Yii::$app->efps->withdrawalToCard([
            "customerCode"    => \Yii::$app->efps->getCustomerCode(),
            "outTradeNo"      => date("YmdHis") . rand(1000, 9999),
            "notifyUrl"       => "http://",
            "amount"          => 100,
            "bankUserName"    => "杨桢",
            "bankCardNo"      => "6216610100014295485",
            "bankName"        => "中国银行",
            "bankAccountType" => "2"
        ]);
        print_r($res);exit;
        //\Yii::$app->validateCloudFile();
        parent::init();
    }

    public $layout = 'admin';

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'loginFilter' => [
                'class' => \app\controllers\behavior\LoginFilter::class,
                'safeRoutes' => [
                    'admin/admin/login',
                    'admin/admin/mch-login',
                    'admin/admin/logout',
                    'admin/admin/register',
                    'admin/admin/mch-setting',
                    'admin/admin/role-setting',
                ],
            ],
            'adminPermissions' => [
                'class' => AdminPermissionsBehavior::class,
            ],
        ]);
    }

    /**
     * 请求成功统消息格式化处理
     * @param array $url
     * @param string $msg
     * @param array $data
     * @return void
     */
    public function success($msg='success',$data=[],$url=[]){
        if(Yii::$app->request->isAjax){
            return $this->asJson(array(
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $msg,
                'data' => $data
            ));
        }
        Yii::$app->getSession()->setFlash('success',$msg);
        $url = !empty($url) ? $url : Yii::$app->request->getReferrer();
        return $this->redirect($url);
    }

    /**
     * 请求失败消息格式化处理
     * @param array $url
     * @param string $msg
     * @param array $data
     * @return void
     */
    public function error($msg='failed',$data=[],$url=[]){
        if(Yii::$app->request->isAjax){
            return $this->asJson(array(
                'code' => ApiCode::CODE_FAIL,
                'msg' => $msg,
                'data' => $data
            ));
        }
        Yii::$app->getSession()->setFlash('error',$msg);
        $url = !empty($url) ? $url : Yii::$app->request->getReferrer();
        return $this->redirect($url);
    }
}
