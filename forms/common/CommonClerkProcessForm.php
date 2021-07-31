<?php

namespace app\forms\common;


use app\models\BaseModel;
use app\models\clerk\ClerkData;

abstract class CommonClerkProcessForm extends BaseModel{

    public $clerk_user_id;

    public function rules(){
        return [
            [['clerk_user_id'], 'required']
        ];
    }

    /**
     * 核销处理
     * @param ClerkData $clerkData
     * @throws \Exception
     */
    abstract public function process(ClerkData $clerkData);
}