<?php
namespace app\mch\forms\api;


use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\mch\models\MchCommonCat;

class CommonCatForm extends BaseModel implements ICacheForm {

    public function getCacheKey() {
        return [];
    }

    public function getSourceDataForm(){

        try {
            $list = MchCommonCat::find()->select(["id", "mall_id", "name", "pic_url"])->where([
                'mall_id'   => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->asArray()->orderBy(['sort' => SORT_ASC])->all();
            $list = $list ? $list : [];
            foreach($list as $key => $item){

            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'list' => $list
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }


}