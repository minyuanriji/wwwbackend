<?php
namespace app\mch\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchCommonCat;

class CommonCatForm extends BaseModel{

    public function getAll(){

        try {
            $list = MchCommonCat::find()->select(["id", "mall_id", "name"])->where([
                'mall_id'   => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->asArray()->orderBy(['sort' => SORT_ASC])->all();
            $list = $list ? $list : [];
            foreach($list as $key => $item){
                $list[$key]['pic_url'] = "http://";
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list
                ]
            ];
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