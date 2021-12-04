<?php
namespace app\plugins\taolijin\controllers\mall;

use app\plugins\Controller;
use app\plugins\taolijin\forms\mall\TaoLiJinGoodsDeletesForm;
use app\plugins\taolijin\forms\mall\TaoLiJinGoodsEditForm;
use app\plugins\taolijin\forms\mall\TaoLiJinGoodsListForm;
use app\plugins\taolijin\forms\mall\TaoLiJinGoodsLoadAliDataForm;

class GoodsController extends Controller{

    /**
     * 商品列表
     * @return string|\yii\web\Response
     */
    public function actionList(){

        if (\Yii::$app->request->isAjax) {
            $form = new TaoLiJinGoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 编辑商品
     * @return string|\yii\web\Response
     */
    public function actionEdit(){
        $form = new TaoLiJinGoodsEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除
     * @return string|\yii\web\Response
     */
    public function actionDelete(){
        $form = new TaoLiJinGoodsDeletesForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->recycle());
    }

    /**
     * 加载联盟数据
     * @return string|\yii\web\Response
     */
    public function actionLoadAliData(){
        $form = new TaoLiJinGoodsLoadAliDataForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getData());
    }
}