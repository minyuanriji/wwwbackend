<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\perform_distribution\models\AwardOrder;

class AwardListForm extends BaseModel{

    public $keyword;
    public $limit = 10;
    public $page = 1;

    public function rules(){
        return [
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
            [['limit', 'page'], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $query = AwardOrder::find()->alias('ao')
                ->where(['ao.is_delete' => 0, 'ao.mall_id' => \Yii::$app->mall->id]);

            if ($this->keyword) {

            }

            $list = $query->select('ao.*')
                ->page($pagination, $this->limit, $this->page)
                ->orderBy("pdg.id DESC")->asArray()->all();

            foreach ($list as $key => $item) {

                $list[$key] = $item;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                'list'       => $list,
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}