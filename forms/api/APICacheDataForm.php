<?php
namespace app\forms\api;


use app\models\BaseModel;

class APICacheDataForm extends BaseModel
{
    public $sourceData;
    public $duration = null;
}