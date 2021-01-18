<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-29
 * Time: 9:10
 */

namespace app\controllers\api;


use app\forms\api\cat\CatListForm;

class CatController extends ApiController
{
    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 9:10
     * @Note:分类列表
     */
    public function actionList()
    {
        $form = new CatListForm();
        $form->attributes = $this->requestData;
        return $form->search();
    }
}