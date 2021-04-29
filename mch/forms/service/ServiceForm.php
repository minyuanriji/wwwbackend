<?php
namespace app\mch\forms\service;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\GoodsService;


class ServiceForm extends BaseModel{

    public $id;
    public $page;
    public $is_default;
    public $keyword;
    public $mch_id;


    public function rules(){
        return [
            [['id', 'is_default', 'mch_id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '角色ID',
        ];
    }

    /**
     * 获取商品服务列表，可带查询
     * @return array
     */
    public function search(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = GoodsService::find()->where([
            'mall_id'   => \Yii::$app->mall->id,
            'mch_id'    => \Yii::$app->mchAdmin->identity->mchModel->id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }
        /**
         * @var BasePagination $pagination
         */
        $list = $query->page($pagination)
                      ->orderBy(['sort' => SORT_ASC])
                      ->asArray()->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list'       => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getOptionList(){
        $list = $this->getAllServices();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
            ]
        ];
    }

    public function getAllServices(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $list = GoodsService::find()->where([
            'mall_id'   => \Yii::$app->mall->id,
            'mch_id'    => $this->mch_id ?: \Yii::$app->mchAdmin->identity->mchModel->id,
            'is_delete' => 0,
        ])->orderBy(['sort' => SORT_ASC])->all();

        $newList = [];
        /** @var GoodsService $item */
        foreach ($list as $item) {
            $newList[] = [
                'id'         => $item->id,
                'name'       => $item->name,
                'is_default' => $item->is_default,
            ];
        }

        return $newList;
    }

    public function getDetail(){
        $detail = GoodsService::find()->where([
            'id'      => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id'  => \Yii::$app->mchAdmin->identity->mchModel->id,
        ])->asArray()->one();

        if ($detail) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }
        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '请求失败',
        ];
    }

    public function delete(){
        $services = GoodsService::find()->where([
            'id'      => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id'  => \Yii::$app->mchAdmin->identity->mchModel->id,
        ])->one();

        if (!$services) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据异常,该条数据不存在',
            ];
        }

        try {
            $services->is_delete = 1;
            $res = $services->save();

            if (!$res) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $this->responseErrorMsg($services),
                ];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
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

    public function switchChange(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $services = GoodsService::find()->where([
            'id'      => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id'  => \Yii::$app->mchAdmin->identity->mchModel->id,
        ])->one();

        if (!$services) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据异常,该条数据不存在',
            ];
        }

        $services->is_default = $this->is_default;
        $res = $services->save();

        if (!$res) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $this->responseErrorMsg($services)
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功'
        ];
    }
}