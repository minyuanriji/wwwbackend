<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-13
 * Time: 14:11
 */

namespace app\controllers\mall;

use app\forms\common\PickLinkForm;
use app\models\Admin;

class LinkController extends MallController
{
    public function init()
    {
        /** @var Admin $admin */
        $admin = \Yii::$app->admin->identity;
        if ($admin->admin_type == Admin::ADMIN_TYPE_SUPER) {
            $this->superAdminSetMallId();
        }
        parent::init();
    }

    /**
     * 获取小程序菜单 可跳转链接菜单
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new PickLinkForm();
        $ignore = \Yii::$app->request->get('ignore');
        // 暂时只有导航底栏
        if ($ignore && $ignore == PickLinkForm::IGNORE_NAVIGATE) {
            $model->ignore = $ignore;
        }
        $res = $model->getLink();
        return $res;
    }

    /**
     * 超级管理员可通过GET的mall_id参数设置当前商城ID
     */
    private function superAdminSetMallId()
    {
        $mallId = \Yii::$app->request->get('mall_id');
        if (!$mallId) {
            return;
        }
        \Yii::$app->setMallId($mallId);
    }
}
