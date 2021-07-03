<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 自定义商品显示价格
 * Author: xuyaoxiang
 * Date: 2020/10/27
 * Time: 19:31
 */

namespace app\controllers\mall;

use app\services\Goods\GoodsPriceDisplayServices;
use app\services\ReturnData;

class GoodsPriceDisplayController extends MallController
{
    use ReturnData;

    /**
     * 添加或更新数据
     * @return \yii\web\Response
     */
    public function actionStore()
    {
        $params            = \Yii::$app->request->post();
        $params['mall_id'] = \Yii::$app->mall->id;

        $GoodsPriceDisplayServices = new GoodsPriceDisplayServices();
        $model                     = $GoodsPriceDisplayServices->store($params);

        if (!$model) {
            $error = $GoodsPriceDisplayServices->getServiceError();
            return $this->asJson($this->returnApiResultData($error['code'], $error['msg']));
        }

        return $this->asJson($this->returnApiResultData(0, "保存成功", $model));
    }

    /**
     * 列表数据
     * @return \yii\web\Response
     */
    public function actionList()
    {
        $params                    = \Yii::$app->request->get();
        $params['mall_id']         = \Yii::$app->mall->id;
        $GoodsPriceDisplayServices = new GoodsPriceDisplayServices();

        return $this->asJson(
            $this->returnApiResultData(0, "请求成功", $GoodsPriceDisplayServices->getList($params))
        );
    }

    public function actionDestroy()
    {
        $params                    = \Yii::$app->request->get();
        $params['mall_id']         = \Yii::$app->mall->id;
        $GoodsPriceDisplayServices = new GoodsPriceDisplayServices();

        $return = $GoodsPriceDisplayServices->destroy($params);

        if (!$return) {
            $error = $GoodsPriceDisplayServices->getServiceError();
            return $this->asJson($this->returnApiResultData($error['code'], $error['msg']));
        }
        return $this->asJson(
            $this->returnApiResultData(0, "删除成功")
        );
    }
}