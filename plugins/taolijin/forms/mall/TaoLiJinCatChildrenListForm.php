<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinCats;

class TaoLiJinCatChildrenListForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id', 'mch_id', 'sort'], 'integer']
        ];
    }

    public function getChildrenList(){

        $list = TaolijinCats::find()->alias('gc')->where([
            'gc.mall_id' => \Yii::$app->mall->id,
            'gc.is_delete' => 0,
            'gc.parent_id' => $this->id,
        ])->with(['parent', 'child' => function ($query) {
            $query->alias('c')->andWhere(['c.is_delete' => 0]);
        }])->page($pagination)
            ->orderBy('sort ASC')
            ->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list'       => $list,
                'pagination' => $pagination
            ]
        ];
    }
}