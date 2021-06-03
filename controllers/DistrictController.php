<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 9:39
 */

namespace app\controllers;


use app\forms\api\user\UserAddressForm;
use app\models\DistrictData;
use app\models\Town;
use Curl\Curl;

class DistrictController extends BaseController
{

    public function actionTree()
    {
        $district = DistrictData::getTerritorial();
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'district' => $district
            ]
        ];
    }

    public function actionCommon()
    {
        $form = new UserAddressForm();
        return $form->autoAddressInfo();
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $level = \Yii::$app->request->post('level');
            } elseif (\Yii::$app->request->isGet) {
                $level = \Yii::$app->request->get('level');
            } else {
                $level = 3;
            }
            switch ($level) {
                case 3:
                    $level = null;
                    break;
                case 2:
                    $level = 'district';
                    break;
                case 1:
                    $level = 'city';
                    break;
                default:
                    $level = null;
            }
            $list = DistrictData::getArr();
            $district = DistrictData::getList($list, $level);
            return [
                'code' => 0,
                'msg' => '',
                'data' => [
                    'district' => $district
                ]
            ];
        }
        return [
            'code' => 1,
            'msg' => '返回错误'
        ];
    }

    public function actionTownList()
    {
        $district_id = \Yii::$app->request->get('district_id');
        $town_list = Town::find()->where(['district_id' => $district_id])->asArray()->all();
        if (count($town_list) == 0) {
            $list = DistrictData::getArr();
            $key = 'cd1c373fef29c534306b40fac4de6e59';
            foreach ($list as $item) {
                if ($item['level'] == 'district') {
                    if ($item['id'] == $district_id) {
                        $city = $item['name'];
                        $url = "https://restapi.amap.com/v3/config/district?keywords={$city}&subdistrict=1&key={$key}";
                        $curl = new Curl();
                        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
                        $curl->setOpt(CURLOPT_TIMEOUT, 500);
                        $curl->get($url);
                        $res = $curl->response;
                        $res = json_decode($res, true);
                        if ($res['status'] == 1) {
                            if (count($res['districts'])) {
                                $town_list = $res['districts'][0]['districts'];
                                foreach ($town_list as $tn) {
                                    $town = Town::findOne(['city_id' => $item['id'], 'name' => $tn['name']]);
                                    if (!$town) {
                                        $town = new Town();
                                        $town->name = $tn['name'];
                                        $town->district_id = $item['id'];
                                        $town->adcode = $tn['adcode'];
                                        $town->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $town_list = Town::find()->where(['district_id' => $district_id])->asArray()->all();
        }
        return [
            'code' => 0,
            'msg' => '',
            'list' => $town_list
        ];

    }

}