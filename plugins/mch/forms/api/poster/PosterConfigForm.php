<?php

namespace app\plugins\mch\forms\api\poster;

use app\core\ApiCode;
use app\forms\common\poster\PosterConfigTrait;
use app\models\BaseModel;
use app\plugins\mch\Plugin;

class PosterConfigForm extends BaseModel
{
    use PosterConfigTrait;

    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id'], 'integer'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'config' => $this->getConfig(),
                    'info' => $this->getAll(),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getPlugin(): array
    {
        return [
            'sign' => (new Plugin())->getName(),
        ];
    }
}