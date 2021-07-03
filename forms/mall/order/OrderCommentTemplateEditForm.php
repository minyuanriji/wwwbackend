<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单评价模板操作
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:11
 */

namespace app\forms\mall\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\OrderCommentsTemplates;

class OrderCommentTemplateEditForm extends BaseModel
{
    public $id;
    public $type;
    public $title;
    public $content;

    public function rules()
    {
        return [
            [['title', 'content', 'type'], 'required'],
            [['id', 'type'], 'integer'],
            [['content'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '模板类型',
            'title' => '模板标题',
            'content' => '模板内容',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = OrderCommentsTemplates::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'id' => $this->id,
            'is_delete' => 0,
        ]);

        if (!$model) {
            $model = new OrderCommentsTemplates();
            $model->mall_id = \Yii::$app->mall->id;
            $model->mch_id = \Yii::$app->admin->identity->mch_id;
        }
        $model->attributes = $this->attributes;
        $res = $model->save();
        if ($res) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorInfo($model);
        }
    }
}
