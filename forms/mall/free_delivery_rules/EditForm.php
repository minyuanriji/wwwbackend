<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 18:11
 */

namespace app\forms\mall\free_delivery_rules;

namespace app\forms\mall\free_delivery_rules;
use app\core\ApiCode;
use app\models\BaseModel;
use app\models\FreeDeliveryRules;
/**
 * @property FreeDeliveryRules $model
 */
class EditForm extends BaseModel
{
    public $model;

    public $price;
    public $detail;
    public $name;

    public function rules()
    {
        return [
            ['price', 'default', 'value' => 0],
            ['price', 'number', 'min' => 0],
            ['detail', 'safe'],
            ['name', 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if ($this->model->isNewRecord) {
            $this->model->is_delete = 0;
        }
        $this->model->detail = \Yii::$app->serializer->encode($this->detail);
        $this->model->price = $this->price;
        $this->model->name = $this->name;
        if ($this->model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorMsg($this->model);
        }
    }
}