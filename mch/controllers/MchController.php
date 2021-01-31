<?php
namespace app\mch\controllers;

use app\core\ApiCode;
use app\mch\behavior\LoginFilter;
use app\mch\forms\mch\MchEditPasswordForm;
use app\mch\forms\mch\MchEditStoreForm;
use app\mch\forms\mch\MchForm;
use app\models\Mall;
use yii\web\Controller;

class MchController extends Controller {

    public function init(){
        parent::init();

        if (property_exists(\Yii::$app, 'appIsRunning') === false) {
            exit('property not found.');
        }

        $this->loadMall();
    }

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'loginFilter' => [
                'class' => LoginFilter::class,
                'safeRoutes' => [
                    'mch/menus/index',
                    'mch/admin/login',
                    'mch/admin/logout',
                ],
            ],
        ]);
    }

    public function actionMchSetting(){
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => []
        ];
    }

    /**
     * 商城头部导航
     * @return array
     * @throws \Exception
     */
    public function actionHeaderBar(){
        $mchAdmin = \Yii::$app->mchAdmin->identity;
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'user' => [
                    'id' => $mchAdmin->id,
                    'mch_id' => $mchAdmin->mch_id,
                    'username' => $mchAdmin->username
                ],
                'mall' => [
                    'id' => \Yii::$app->mall->id,
                    'name' => \Yii::$app->mall->name,
                ],
                'navs' => [],
                'update_password_status' => 0
            ],
        ];
    }

    private function loadMall(){
        $mchAdmin = \Yii::$app->mchAdmin->identity;
        if(!$mchAdmin) return;

        $logoutUrl = \Yii::$app->urlManager->createAbsoluteUrl([
            "mch/admin/logout"
        ]);
        $mallId = !empty($mchAdmin->mchModel->mall_id) ? $mchAdmin->mchModel->mall_id : 0;
        $mall = Mall::find()->where(['id' => $mallId, 'is_delete' => 0])->with('option')->one();
        if (!$mall || $mall->is_delete || $mall->is_recycle) {
            return $this->redirect($logoutUrl)->send();
        }

        $newOptions = [];
        foreach ($mall['option'] as $item) {
            $newOptions[$item['key']] = $item['value'];
        }
        $mall->options = (object)$newOptions;

        \Yii::$app->mallId  = $mallId;
        \Yii::$app->mall    = $mall;
        \Yii::$app->mchId   = $mchAdmin->mchModel->id;
    }

    /**
     * 店铺信息
     * @return string|\yii\web\Response
     */
    public function actionEditStore(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MchEditStoreForm();
                $data = \Yii::$app->request->post('form');
                $form->attributes   = $data;
                $form->province_id  = $data['district'][0];
                $form->city_id      = $data['district'][1];
                $form->district_id  = $data['district'][2];
                $form->attributes   = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new MchForm();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit-store');
        }
    }

    /**
     * 账号设置
     */
    public function actionPassword(){
        if (\Yii::$app->request->isPost) {
            $form = new MchEditPasswordForm();
            $form->attributes = \Yii::$app->request->post("form");
            return $this->asJson($form->save());
        }else{
            return $this->render('password');
        }

    }
}