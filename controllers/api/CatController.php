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
use app\helpers\APICacheHelper;

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
        $search = APICacheHelper::get(APICacheHelper::API_CAT_LIST, function($helper){
            $form = new CatListForm();
            $form->attributes = $this->requestData;
            return $helper($form->search());
        });

        return $search;
    }
}