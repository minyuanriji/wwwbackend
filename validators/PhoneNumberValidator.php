<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 手机号验证
 * Author: zal
 * Date: 2020-04-27
 * Time: 10:16
 */
namespace app\validators;

use yii\validators\Validator;

class PhoneNumberValidator extends Validator
{
    public $pattern = '/^1\d{10}$/';

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $pattern = $this->pattern;
        if ($value && !preg_match($pattern, $value)) {
            $model->addError($attribute, "{$model->getAttributeLabel($attribute)}格式不正确。");
        }
    }
}
