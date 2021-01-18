<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-29
 * Time: 19:07
 */

namespace app\forms\mall\goods;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Label;

class LabelEditForm extends BaseModel
{
    public $id;
    public $mall_id;
    public $title;
    public $sub_title;
    public $sort;

    public function rules()
    {
        return [
            [['sub_title', 'title'], 'required'],
            [['id', 'sort'], 'integer'],
            [['sub_title', 'title'], 'string']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $label = Label::findOne(['id' => $this->id, 'is_delete' => 0]);
        if (!$label) {
            $label = new Label();
        }
        $label->attributes = $this->attributes;
        $label->mall_id = \Yii::$app->mall->id;
        if (!$label->save()) {
            return ['code' => ApiCode::CODE_FAIL, 'msg' => '保存失败','error'=>$label->getErrors()];

        }
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功'];
    }

}