<?php

namespace app\plugins\oil\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;
use app\plugins\oil\models\OilSetting;
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

            $list = OilProduct::find()->where([
                "plat_id"   => $platModel->id,
                "status"    => 1,
                "is_delete" => 0
            ])->asArray()->orderBy("sort DESC, price ASC")->all();

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