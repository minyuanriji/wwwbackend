<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\perform_distribution\models\Level;

class LevelListForm extends BaseModel{

    public $keyword;
    public $page;

    public function rules(){
        return [
            [['keyword'], 'string'],
            [['keyword'], 'trim'],
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $list = Level::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->keyword($this->keyword, ['or', ['like', 'name', $this->keyword],['like', 'level', $this->keyword]])
              ->page($pagination, 20, $this->page)->orderBy(['level' => SORT_ASC])->all();

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                'list'       => $list,
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }
}