<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-29
 * Time: 16:10
 */

namespace app\controllers\api;


use app\forms\api\article\ArticleForm;

class ArticleController extends ApiController
{


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-29
     * @Time: 16:11
     * @Note:文章列表
     */
    public function actionList()
    {

        $form = new ArticleForm();
        $form->attributes = $this->requestData;

        return $this->asJson($form->getList());


    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-29
     * @Time: 16:11
     * @Note:文章详情
     */
    public function actionDetail()
    {
        $form = new ArticleForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getDetail());
    }


}