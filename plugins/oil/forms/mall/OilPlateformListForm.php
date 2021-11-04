<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;

class OilPlateformListForm extends BaseModel{

    public $page;

    public function rules()
    {
        return [
            [['page'], 'integer']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = OilPlateforms::find()->orderBy("id DESC");
            $list = $query->page($pagination, 10, $this->page)->asArray()->all();
            if ($list) {
                foreach ($list as &$row) {
                    $row['is_enabled'] = $row['is_enabled'] ? "1" : "0";
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : [],
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

}