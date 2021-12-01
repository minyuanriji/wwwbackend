<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\helpers\AliOrderHelper;
use app\plugins\taolijin\models\TaolijinAli;
use lin010\taolijin\Ali;

class TaoLiJinOrderListForm extends BaseModel{

    public $ali_id;
    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['ali_id'], 'required'],
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }


    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $aliModel = TaolijinAli::findOne($this->ali_id);
            if(!$aliModel || $aliModel->is_delete){
                throw new \Exception("联盟不存在");
            }

            AliOrderHelper::get($aliModel, (int)$this->page);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination,
                ]
            ];

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

}