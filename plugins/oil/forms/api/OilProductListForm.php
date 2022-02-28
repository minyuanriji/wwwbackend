<?php

namespace app\plugins\oil\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;
use app\plugins\oil\models\OilSetting;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromOil;
use yii\db\Exception;

class OilProductListForm extends BaseModel{

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $platModel = OilPlateforms::find()->where(["is_delete" => 0, "is_enabled" => 1])->one();
            if(!$platModel){
                throw new Exception("暂无加油产品");
            }

            //送红包-指定产品
            $fromOil = ShoppingVoucherFromOil::findOne([
                "plat_id"   => $platModel->id,
                "is_delete" => 0
            ]);

            //无指定产品按通用配置
            if(!$fromOil){
                $fromOil = ShoppingVoucherFromOil::findOne([
                    "plat_id"   => 0,
                    "is_delete" => 0
                ]);
            }

            $list = OilProduct::find()->where([
                "plat_id"   => $platModel->id,
                "status"    => 1,
                "is_delete" => 0
            ])->asArray()->orderBy("sort DESC, price ASC")->all();
            if($list){
                foreach($list as &$item){

                    //计算送红包的数量
                    if($fromOil->first_give_type == 1){ //按比例
                        $number = floatval($item['price']) * (floatval($fromOil->first_give_value)/100);
                    }else{ //固定值
                        $number = floatval($fromOil->first_give_value);
                    }

                    //上线100红包，超过部分按50%送
                    if($number > 100){
                        $number = 100 + ($number - 100)/2;
                    }

                    $item['info'] = "送".round($number, 2)."红包";
                }
            }

            $rows = OilSetting::find()->asArray()->all();
            $settings = [];
            if($rows){
                foreach($rows as $row){
                    $settings[$row['name']] = $row['value'];
                }
            }

            $descript = isset($settings['descript']) ? $settings['descript'] : '';
            $descript = str_replace(["\n", " "], ["<br/>", "&nbsp;"], $descript);

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'', [
                'list'     => $list ? $list : [],
                'descript' => $descript
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}