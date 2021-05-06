<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-08
 * Time: 14:12
 */

namespace app\controllers\admin;

use app\core\ApiCode;
use app\forms\admin\mall\MallCopyrightForm;
use app\forms\admin\MallForm;
use app\models\Admin;
use app\models\Mall;
use yii\db\Query;

class MallController extends BaseController
{
    public function actionIndex($keyword = null, $is_recycle = 0)
    {
        if (\Yii::$app->request->isAjax) {
            $query = Mall::find()->where([
                'is_recycle' => $is_recycle,
                'is_delete' => 0,
            ]);
            /** @var Admin $admin */
            $admin = \Yii::$app->admin->identity;
            if ($admin->admin_type != Admin::ADMIN_TYPE_SUPER) {
                $query->andWhere(['admin_id' => $admin->id]);
            }
            $keyword = trim($keyword);
            if (!empty($keyword)) {
                $query->andWhere(['LIKE', 'name', $keyword]);
            }
            $count = $query->count();
            $pagination = new \yii\data\Pagination(['totalCount' => intval($count)]);
            $list = $query
                ->with(['admin' => function ($query) {
                    /** @var Query $query */
                    $query->select('id,username');
                }])
                ->orderBy('id DESC')
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->asArray()
                ->all();

            foreach ($list as $key => $item) {
                //$copyright = OptionLogic::get(Option::NAME_COPYRIGHT, $item['id'], Option::GROUP_APP);
                //$list[$key]['copyright'] = $copyright;
                $list[$key]['count_data'] = $this->getMallCountData($item['id']);
                if ($item['expired_at'] == '0') {
                    $list[$key]['expired_at_text'] = '永久';
                } elseif ($item['expired_at'] < time()) {
                    $list[$key]['expired_at_text'] = '已过期';
                } else {
                    $list[$key]['expired_at_text'] = date("Y-m-d H:i:s",$item['expired_at']);
                }
            }
            $adminInfo = $admin;
            if ($admin->admin_type == Admin::ADMIN_TYPE_SUPER) {
                $adminInfo->mall_num = -1;
            }
            $permission = \Yii::$app->role->permission;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination,
                    'admin_info' => $adminInfo,
                    'showCopyright' => is_array($permission) ? in_array('copyright', $permission) : $permission
                ],
            ];
        } else {
            return $this->render('index');
        }
    }


    /**
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @Note: 获取商城用户和订单总数
     * @param $mallId
     * @return array|mixed
     */
    private function getMallCountData($mallId)
    {
        $cacheKey = 'SIMPLE_MALL_DATA_OF_' . $mallId;
        $cacheDuration = 600;
        $data = \Yii::$app->cache->get($cacheKey);
        if ($data) {
            return $data;
        }
        $userCount = 0;
        $orderCount = 0;
        $data = [
            'user_count' => intval($userCount ? $userCount : 0),
            'order_count' => intval($orderCount ? $orderCount : 0),
        ];
        \Yii::$app->cache->set($cacheKey, $data, $cacheDuration);
        return $data;
    }

    /**
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @Note: 创建商城
     * @return \yii\web\Response
     */
    public function actionCreate()
    {
        $form = new MallForm();
        $data = \Yii::$app->request->post();
        return $this->asJson($form->save($data));
    }


    /**
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @Note: 移除或加入回收站
     * @return \yii\web\Response
     */
    public function actionUpdate()
    {
        $form = new MallForm();
        $id = \Yii::$app->request->post("id");
        return $this->asJson($form->edit($id));
    }

    /**
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @Note: 进入商城
     * @return \yii\web\Response
     */
    public function actionEntry($id)
    {
        return \Yii::$app->createForm('app\forms\admin\MallForm')->entryJxMall($id);
    }

    /**
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @Note: 迁移小程序
     * @return \yii\web\Response
     */
    public function actionRemoval()
    {
        $form = new MallCopyrightForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->save());
    }

    /**
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @Note: 商城禁用
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionDisable()
    {
        $form = new MallForm();
        $id = \Yii::$app->request->get("id");

        return $this->asJson($form->disable($id));
    }

    /**
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @Note: 商城回收站删除
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        $form = new MallForm();
        $id = \Yii::$app->request->get("id");

        return $this->asJson($form->delete($id));
    }

    /**
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @Note: 设置版权
     * @return \yii\web\Response
     */
    public function actionSetCopyright()
    {
        $form = new MallCopyrightForm();
        $id = \Yii::$app->request->post("id");
        $form->attributes = \Yii::$app->request->post();
        return $form->saveCopyright($id);
    }

    /**
     * @Author: zal
     * @Date: 2020-04-09
     * @Time: 15:16
     * @Note: 回收
     * @return \yii\web\Response
     */
    public function actionRecycle()
    {
        return $this->render('recycle');
    }
}
