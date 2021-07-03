<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商品
 * Author: xuyaoxiang
 * Date: 2020/9/22
 * Time: 14:43
 */

namespace app\plugins\group_buy\controllers\api;

use app\plugins\group_buy\forms\api\poster\PosterForm;
use app\plugins\ApiController;

class PosterController extends ApiController
{
    /**
     * 拼团商品详情
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionGoods(){
        $form = new PosterForm();
        $goodsForm = $form->goods();
        $goodsForm->sign = "group_buy/";
        $goodsForm->goods_id = isset($this->requestData["goods_id"]) ? $this->requestData["goods_id"] : 0;
        return $this->asJson($goodsForm->get());
    }

    /**
     * 拼团分享
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionShare(){
        $form = new PosterForm();
        $goodsForm = $form->share();
        $goodsForm->sign = "group_buy/";
        $goodsForm->goods_id = isset($this->requestData["goods_id"]) ? $this->requestData["goods_id"] : 0;
        $goodsForm->detail_id = isset($this->requestData["detail_id"]) ? $this->requestData["detail_id"] : 0;
        return $this->asJson($goodsForm->get());
    }
}