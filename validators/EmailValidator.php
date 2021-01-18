<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 邮箱验证
 * Author: zal
 * Date: 2020-07-09
 * Time: 17:16
 */
namespace app\validators;

use yii\validators\Validator;

class EmailValidator extends Validator
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

    /**
     * 检测邮箱
     * @param $email
     * @return bool
     */
    public static function checkEmail($email){
        if (filter_var($email, FILTER_VALIDATE_EMAIL))
        {
           return true;
        }
        else
        {
           return false;
        }
    }
}
