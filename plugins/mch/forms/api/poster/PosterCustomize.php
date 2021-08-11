<?php

namespace app\plugins\mch\forms\api\poster;

use app\forms\api\poster\common\BaseConst;
use app\models\BaseModel;

class PosterCustomize extends BaseModel implements BaseConst
{
    public function traitQrcode($class)
    {
        return [
            ['id' => $class->goods->id, 'user_id' => \Yii::$app->user->id, 'mch_id' => $class->goods->mch_id],
            240,
            'plugins/mch/goods/goods',
        ];
    }
}