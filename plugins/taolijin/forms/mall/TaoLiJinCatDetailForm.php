<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinCats;

class TaoLiJinCatDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id',], 'required'],
        ];
    }

    public function getDetail(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $detail = TaolijinCats::find()->where([
                'id'      => $this->id,
                'mall_id' => \Yii::$app->mall->id
            ])->with('parent.parent')->one();

            if (!$detail) {
                throw new \Exception('分类不存在,ID:' . $this->id);
            }

            $newDetail = ArrayHelper::toArray($detail);

            $parents = [];
            if ($detail->parent) {
                $parents[] = $detail->parent;
                if ($detail->parent->parent) {
                    $parents[] = $detail->parent->parent;
                }
            }

            $newDetail['parents'] = $parents;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $newDetail,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}