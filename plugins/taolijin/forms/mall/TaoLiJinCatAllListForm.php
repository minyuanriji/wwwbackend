<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinCats;

class TaoLiJinCatAllListForm extends BaseModel{

    public $keyword;

    public function rules(){
        return [
            [['keyword',], 'trim'],
        ];
    }

    public function getAllList(){

        try {

            $query = TaolijinCats::find()->where([
                'mall_id'   => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);

            if ($this->keyword) {
                $query->andWhere(['like', 'name', $this->keyword]);
            }

            $list = $query->andWhere(['parent_id' => 0])->with('child.child')->all();
            $newList = [];

            foreach ($list as $item) {
                $newItem = [];
                $newItem['value'] = $item->id;
                $newItem['label'] = $item->name;

                if (isset($item->child) && count($item->child)) {
                    $newChildrenList = [];
                    foreach ($item->child as $children) {
                        $newChildren = [];
                        $newChildren['value'] = $children->id;
                        $newChildren['label'] = $children->name;
                        $newChildrenList[] = $newChildren;
                    }
                    $newItem['children'] = $newChildrenList;
                } else {
                    $newItem['children'] = null;
                }
                $newList[] = $newItem;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newList,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}