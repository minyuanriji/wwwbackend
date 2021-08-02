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


use app\component\jobs\EfpsTransferJob;
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
