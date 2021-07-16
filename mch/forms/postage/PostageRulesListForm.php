<?php
namespace app\mch\forms\postage;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\PostageRules;

class PostageRulesListForm extends BaseModel{

    public $limit;
    public $page;
    public $keyword;
    public $mch_id;

    public function rules(){
        return [
            [['limit', 'page', 'mch_id'], 'integer'],
            ['limit', 'default', 'value' => 10],
            ['page', 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function search(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        /**
         * @var BasePagination $pagination
         */
        $pagination = null;
        $query = PostageRules::find()->where([
            'is_delete' => 0,
            'mall_id'   => \Yii::$app->mall->id,
            'mch_id'    => \Yii::$app->mchAdmin->identity->mch_id,
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }
        $list = $query->select(['id', 'name', 'status'])->page($pagination, $this->limit, $this->page)->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
                'ofs' => $pagination->offset,
                'lim' => $pagination->limit
            ]
        ];
    }

    /**
     * 取所有规则
     * @return array
     */
    public function allList(){

        $allList = PostageRules::find()->where([
            'is_delete' => 0,
            'mall_id'   => \Yii::$app->mall->id,
            'mch_id'    => $this->mch_id ?: \Yii::$app->mchAdmin->identity->mchModel->id,
        ])->select(['id', 'name', 'status'])->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg'  => '请求成功',
            'data' => [
                'list' => $allList
            ]
        ];
    }
}