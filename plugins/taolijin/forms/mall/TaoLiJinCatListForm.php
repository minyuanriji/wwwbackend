<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinCats;

class TaoLiJinCatListForm extends BaseModel{

    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['page'], 'default', 'value' => 1],
            [['keyword',], 'trim'],
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if ($this->keyword) {
            return $this->getListByKeyword();
        } else {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $this->getDefault()
                ]
            ];
        }
    }

    public function getDefault($id = null, $isArray = true, $isParent = true){
        $query = TaolijinCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])->keyword($id, ['id' => $id])->with(['child' => function ($query) {
            $query->orderBy(['sort' => SORT_DESC, 'created_at' => SORT_DESC]);
        }, 'child.child' => function ($query) {
            $query->orderBy(['sort' => SORT_DESC, 'created_at' => SORT_DESC]);
        }]);
        if ($isParent) {
            $query->andWhere(['parent_id' => 0]);
        }
        $list = $query->orderBy(['sort' => SORT_DESC, 'created_at' => SORT_DESC])
            ->asArray($isArray)->all();

        return $list;
    }

    /**
     * 关键词查询
     * @return array
     */
    public function getListByKeyword(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $list = TaolijinCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0
        ])->with('parent')->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->page($pagination, 20, $this->page)
            ->orderBy(['sort' => SORT_DESC, 'created_at' => SORT_DESC])->all();

        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            if ($item->parent_id == 0) {
                $newItem['status_text'] = '一级分类';
            } elseif ($item->parent && $item->parent->parent_id == 0) {
                $newItem['status_text'] = '二级分类';
            } else {
                $newItem['status_text'] = '三级分类';
            }
            $newList[] = $newItem;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list'       => $newList,
                'pagination' => $pagination
            ]
        ];
    }
}