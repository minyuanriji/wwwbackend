<?php

namespace app\plugins\hotel\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\models\HotelPics;

class HotelDiagramForm extends BaseModel
{
    public $hotel_id;
    public $page;

    public function rules()
    {
        return [
            [['hotel_id'], 'required'],
            [['page', 'hotel_id'], 'integer'],
        ];
    }

    public function getDiagram()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $list = HotelPics::find()->where(['hotel_id' => $this->hotel_id])->page($pagination)->asArray()->all();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '',  [
                'list' => $list ?: [],
                'pagination' => $pagination,
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}