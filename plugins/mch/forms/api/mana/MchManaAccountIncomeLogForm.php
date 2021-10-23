<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\MchPriceLog;

class MchManaAccountIncomeLogForm extends BaseModel {

    public $page;
    public $status;

    public function rules(){
        return [
            [['status'], 'required'],
            [['page'], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = MchPriceLog::find()->where([
                "status" => $this->status,
                "mch_id" => MchAdminController::$adminUser['mch_id']
            ])->orderBy("id DESC");

            $selects = ["price", "created_at", "status", "source_type", "content"];
            $query->select($selects);
            $list = $query->asArray()->page($pagination, 10, $this->page)->all();
            if ($list) {
                foreach ($list as &$item) {
                    $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            ];
        }
    }
}