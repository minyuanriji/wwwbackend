<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-28
 * Time: 11:49
 */
namespace app\controllers\api;

use app\forms\api\goods\CommentForm;
use app\forms\api\goods\GoodsForm;
use app\forms\api\goods\GoodsListForm;
use app\forms\api\goods\RecommendForm;
use app\forms\api\poster\GoodsPosterForm;
use app\forms\api\poster\PosterForm;

class GoodsController extends ApiController
{

//     * @Author: 广东七件事 ganxiaohao
//     * @Date: 2020-04-30
//     * @Time: 16:11
//     * @Note:商品详情
//     * @return \yii\web\Response
//     *
    public function actionDetail()
    {
        $form = new GoodsForm();
        $form->attributes =$this->requestData;
        return $this->asJson($form->getDetail());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-30
     * @Time: 11:01
     * @Note:评论列表
     * @return \yii\web\Response
     */
    public function actionCommentsList()
    {
        $form = new CommentForm();
        $form->attributes = $this->requestData;
        $form->mall = \Yii::$app->mall;
        return $this->asJson($form->search());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-30
     * @Time: 16:11
     * @Note:
     * @return \yii\web\Response
     */
    public function actionRecommend()
    {
        $form = new RecommendForm();
        $form->attributes =$this->requestData;
        return $this->asJson($form->getNewList());
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-30
     * @Time: 16:21
     * @Note:商品列表
     * @return \yii\web\Response
     */
    public function actionList()
    {
        $form = new GoodsListForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getList());
    }

    /**
     * 商品海报
     * @return \yii\web\R1esponse
     * @throws \Exception
     */
    public function actionPoster(){
        $form = new PosterForm();
        $goodsForm = $form->goods();
        $goodsForm->sign = "goods/";
        $goodsForm->goods_id = isset($this->requestData["goods_id"]) ? $this->requestData["goods_id"] : 0;
        return $this->asJson($goodsForm->get());
    }
}