<?php
namespace app\controllers;

use app\models\EfpsRegionMcc;

class EfpsRegionController extends BaseController {

    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {

            $arr = EfpsRegionMcc::find()->asArray()->all();
            $regions = $citys = $districts = [];
            foreach($arr as $item){
                if($item['level'] == 1){
                    $regions[$item['code']] = array_merge($item, [
                        'value' => $item['code'],
                        'label' => $item['name']
                    ]);
                }elseif($item['level'] == 2){
                    $citys[$item['code']] = $item;
                }else{
                    $districts[$item['code']] = $item;
                }
            }

            /*foreach($districts as $item){
                if(isset($citys[$item['parent']])){
                    if(!isset($citys[$item['parent']]['children'])){
                        $citys[$item['parent']]['children'] = [];
                    }
                    $citys[$item['parent']]['children'][] = array_merge($item, [
                        'value' => $item['code'],
                        'label' => $item['name']
                    ]);
                }
            }*/

            foreach($citys as $item){
                if(isset($regions[$item['parent']])){
                    if(!isset($regions[$item['parent']]['children'])){
                        $regions[$item['parent']]['children'] = [];
                    }
                    $regions[$item['parent']]['children'][] = array_merge($item, [
                        'value' => $item['code'],
                        'label' => $item['name']
                    ]);
                }
            }

            $regions = array_values($regions);

            return $this->asJson([
                'code' => 0,
                'msg' => '',
                'data' => [
                    'regions' => $regions
                ]
            ]);
        }
        return $this->asJson([
            'code' => 1,
            'msg' => '返回错误'
        ]);
    }
}