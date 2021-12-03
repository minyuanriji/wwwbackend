<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinCats;

class TaoLiJinCatSortForm extends BaseModel{

    public $id;
    public $sort;

    public $first_list;
    public $second_list;
    public $third_list;

    public function rules()
    {
        return [
            [['id',  'sort'], 'integer'],
            [['first_list', 'second_list', 'third_list'], 'string']
        ];
    }


    public function storeSort()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            try {
                $firstList = \Yii::$app->serializer->decode($this->first_list);
                $secondList = \Yii::$app->serializer->decode($this->second_list);
                $thirdList = \Yii::$app->serializer->decode($this->third_list);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            $count = is_array($firstList) ? count($firstList) : 0;
            foreach ($firstList as $index => $item) {
                $goodsCats = TaolijinCats::findOne($item['id']);
                if (!$goodsCats) {
                    throw new \Exception('分类不存在');
                }
                $goodsCats->sort = $count--;
                if (!$goodsCats->save()) {
                    throw new \Exception($this->responseErrorMsg($goodsCats));
                }
            }

            $count = is_array($secondList) ? count($secondList) : 0;
            foreach ($secondList as $index => $item) {
                $goodsCats = TaolijinCats::findOne($item['id']);
                if (!$goodsCats) {
                    throw new \Exception('分类不存在');
                }
                $goodsCats->sort = $count--;
                if (!$goodsCats->save()) {
                    throw new \Exception($this->responseErrorMsg($goodsCats));
                }
            }

            $count = is_array($thirdList) ? count($thirdList) : 0;
            foreach ($thirdList as $index => $item) {
                $goodsCats = TaolijinCats::findOne($item['id']);
                if (!$goodsCats) {
                    throw new \Exception('分类不存在');
                }
                $goodsCats->sort = $count--;
                if (!$goodsCats->save()) {
                    throw new \Exception($this->responseErrorMsg($goodsCats));
                }
            }

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }
}