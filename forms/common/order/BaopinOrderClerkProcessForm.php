<?php


namespace app\forms\common\order;


use app\forms\common\CommonClerkProcessForm;
use app\models\clerk\ClerkData;

class BaopinOrderClerkProcessForm extends CommonClerkProcessForm {


    /**
     * 核销处理
     * @param ClerkData $clerkData
     * @throws \Exception
     */
    public function process(ClerkData $clerkData)
    {
        throw new \Exception('功能开发中~');
    }
}