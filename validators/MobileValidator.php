<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 18:23
 */

namespace app\validators;

use yii\validators\Validator;

class MobileValidator extends Validator
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