<?php
namespace app\plugins\taolijin\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\core\ApiCode;
use app\helpers\APICacheHelper;
use app\plugins\ApiController;
use app\plugins\taolijin\forms\api\TaolijinGoodsCatListForm;
use app\plugins\taolijin\forms\api\TaolijinGoodsDetailForm;
use app\plugins\taolijin\forms\api\TaolijinGoodsSearchForm;

class GoodsController extends ApiController{

    public function behaviors(){
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => []
            ],
        ]);
    }

    /**
     * 获取商品
     * @return \yii\web\Response
     */
    public function actionSearch(){
        $form = new TaolijinGoodsSearchForm();
        $form->attributes = $this->requestData;
        $form->is_login   = !\Yii::$app->user->isGuest;
        $form->login_uid  = $form->is_login ? \Yii::$app->user->id : 0;
        $form->host_info  = \Yii::$app->request->getHostInfo();

        $res = APICacheHelper::get($form);
        if($res['code'] == ApiCode::CODE_SUCCESS){
            $res = $res['data'];
        }

        return $this->asJson($res);
    }

    /**
     * 获取商品分类
     * @return \yii\web\Response
     */
    public function actionCatList(){
        $form = new TaolijinGoodsCatListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->get());
    }

    /**
     * 获取商品详情
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new TaolijinGoodsDetailForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->detail());
    }
}