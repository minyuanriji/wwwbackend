<?php
namespace app\controllers;


use app\models\EfpsMerchantMcc;

class EfpsMerchantMccController extends BaseController{

    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {

            $arr = EfpsMerchantMcc::find()->asArray()->all();
            $mcc = [
                'medium'        => ['children' => [], 'value' => 'medium', 'label' => '百货、中介、培训、景区门票等'],
                'help'          => ['children' => [], 'value' => 'help', 'label' => '便民类'],
                'gov'           => ['children' => [], 'value' => 'gov', 'label' => '政府类'],
                'estate'        => ['children' => [], 'value' => 'estate', 'label' => '房产汽车类'],
                'wholesale'     => ['children' => [], 'value' => 'wholesale', 'label' => '批发类商户'],
                'basic'         => ['children' => [], 'value' => 'basic', 'label' => '餐饮、宾馆、娱乐、珠宝金饰、工艺美术品类'],
                'supermarket'   => ['children' => [], 'value' => 'supermarket', 'label' => '加油、超市类'],
                'transport'     => ['children' => [], 'value' => 'transport', 'label' => '交通运输售票'],
                'water'         => ['children' => [], 'value' => 'water', 'label' => '水电气缴费']
            ];
            foreach($arr as $item){
                $mcc[$item['type']]['children'][] = array_merge($item, [
                    'value' => $item['code'],
                    'label' => $item['name']
                ]);
            }

            $mcc = array_values($mcc);

            return [
                'code' => 0,
                'msg' => '',
                'data' => [
                    'mcc' => $mcc
                ]
            ];
        }

        return [
            'code' => 1,
            'msg' => '返回错误'
        ];
    }
}