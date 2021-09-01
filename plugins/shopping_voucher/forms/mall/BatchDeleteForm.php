<?php

namespace app\plugins\shopping_voucher\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;

class BatchDeleteForm extends BaseModel
{

    public $id_str;

    public function rules()
    {
        return [
            [['id_str'], 'required'],
            [['id_str'], 'string']
        ];
    }

    public function deleteMuti()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $IdArray = explode(",", $this->id_str);
            foreach ($IdArray as $Id) {
                $form = new DeleteGoodsForm();
                $form->id = (int)$Id;
                $res = $form->delete();
                if ($res['code'] != ApiCode::CODE_SUCCESS) {
                    throw new \Exception($res['msg']);
                }
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '批量删除成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}