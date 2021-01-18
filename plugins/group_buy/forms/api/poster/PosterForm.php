<?php
/**
 * xuyaoxiang
 */

namespace app\plugins\group_buy\forms\api\poster;

use app\core\ApiCode;
use app\models\BaseModel;

class PosterForm extends BaseModel
{
    public function poster($method)
    {
        $p = func_get_args();
        try {
            if (method_exists($this, $method)) {
                $model             = $this->$method();
                $model->attributes = $p[1];
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => $model->get()
                ];
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

    public function goods()
    {
        return new GoodsPosterForm();
    }

    public function share()
    {
        return new SharePosterForm();
    }
}
