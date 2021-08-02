<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 店铺管理-页面管理-分销设置
 * Author: zal
 * Date: 2020-04-14
 * Time: 15:50
 */

namespace app\controllers\mall;

use app\core\ApiCode;
use app\forms\AppDistributionForm;
use app\forms\PickLinkForm;
use app\logic\OptionLogic;
use app\models\Option;

class PageController extends ShopManagerController
{
    public function actionShareSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new AppDistributionForm();
                $list = $form->links();

                $selectList = OptionLogic::get(
                    Option::NAME_APP_SHARE_SETTING,
                    \Yii::$app->mall->id,
                    Option::GROUP_APP,
                    []
                );

                $id = 0;
                foreach ($list as $key => $item) {
                    $id++;
                    $list[$key]['id'] = $id;
//                    $list[$key]['title'] = '';
//                    $list[$key]['pic_url'] = '';

                    foreach ($selectList as $sItem) {
                        if ($item['page_url'] == $sItem['page_url']) {
                            $list[$key]['title'] = $sItem['title'];
                            $list[$key]['pic_url'] = $sItem['pic_url'];
                        }
                    }
                }

                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'list' => $list
                    ]
                ]);
            } else {
                $value = \Yii::$app->request->post('list');
                $res = OptionLogic::set(
                    Option::NAME_APP_SHARE_SETTING,
                    $value,
                    \Yii::$app->mall->id,
                    Option::GROUP_APP
                );

                if (!$res) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '保存失败',
                    ];
                }

                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }
        } else {
            return $this->render('share-setting');
        }
    }
}
