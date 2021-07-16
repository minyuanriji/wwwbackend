<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/29
 * Time: 15:07
 */

namespace app\validators;


use app\models\ValidateCode;
use yii\validators\Validator;

class ValidateCodeValidator extends Validator
{
    public $validateCodeIdAttribute;
    public $mobileAttribute;

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $validateCodeIdAttribute = $this->validateCodeIdAttribute;
        $mobileAttribute = $this->mobileAttribute;
        $validateCodeId = $model->$validateCodeIdAttribute;
        $mobile = $model->$mobileAttribute;
        $ValidateCode = ValidateCode::findOne([
            'id' => $validateCodeId,
            'target' => $mobile,
            'code' => $value,
            'is_validated' => ValidateCode::IS_VALIDATED_FALSE,
        ]);
        if (!$ValidateCode) {
            $model->addError($attribute, "{$model->getAttributeLabel($attribute)}错误。");
        } else {
            $ValidateCode->is_validated = ValidateCode::IS_VALIDATED_TRUE;
            $ValidateCode->save();
        }
    }
}
