<?php

namespace app\plugins\group_buy\controllers\mall;

use app\controllers\behavior\LoginFilter;
use app\plugins\Controller;
use app\plugins\group_buy\forms\mall\MultiGroupBuyGoodsEditForm;
use app\plugins\group_buy\forms\mall\GroupBuyGoodsQueryForm;

class IndexController extends Controller
{
    public $enableCsrfValidation = false;

    //页面，首页
    public function actionIndex()
    {
        $title = '拼团首页';
        return $this->render('index', ['title' => $title]);
    }

    //页面，添加页面
    public function actionAdd()
    {
        $title = '添加页面';
        return $this->render('add', ['title' => $title]);
    }

    //页面，编辑页面
    public function actionEdit()
    {
        $title = '编辑页面';
        return $this->render('add', ['title' => $title]);
    }

    //接口,添加数据
    public function actionStoreOrEdit()
    {
        $params = \Yii::$app->request->post();

        $params['form']       = json_decode($params['form'], true);

        $params['group_buy_goods'] = json_decode($params['group_buy_goods'], true);

        $form = new MultiGroupBuyGoodsEditForm();

        $form->form = $params['form'];

        $form->group_buy_goods = $params['group_buy_goods'];

        $return = $form->save();

        return $this->asJson($return);
    }

    /**
     * 接口,详情
     * @return \yii\web\Response
     */
    public function actionShow()
    {
        $params = \Yii::$app->request->get();

        $form = new GroupBuyGoodsQueryForm();
        $form->goods_id = $params['id'];

        $return = $form->show();

        return $this->asJson($return);
    }

    /**
     * 接口，列表数据
     * @return \yii\web\Response
     */
    public function actionGetList()
    {
        $params = \Yii::$app->request->get();

        $form             = new GroupBuyGoodsQueryForm();
        $form->attributes = $params;

        $return = $form->queryList();

        return $this->asJson($return);
    }

    /**
     * 接口,删除
     * @return \yii\web\Response
     */
    public function actionDel()
    {
        $params = \Yii::$app->request->get();

        $form           = new MultiGroupBuyGoodsEditForm();
        $form->goods_id = $params['goods_id'];
        $return         = $form->del();

        return $this->asJson($return);
    }
}