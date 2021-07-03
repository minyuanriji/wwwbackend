<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 表单公共类
 * Author: zal
 * Date: 2020-04-14
 * Time: 10:18
 */

namespace app\forms\common;

use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Form;
use app\models\Mall;
use app\models\Option;

/**
 * Class FormCommon
 * @package app\forms\common\form
 * @property Mall $mall
 */
class FormCommon extends BaseModel
{
    private static $instance;
    public $mall;

    const FORM_DEFAULT = 1; // 默认
    const FORM_NOT_DEFAULT = 0; // 不默认
    const FORM_OPEN = 1; // 状态开启
    const FORM_CLOSE = 0; // 状态关闭

    public static function getInstance($mall = null)
    {
        if (!self::$instance) {
            self::$instance = new self();
            if (!$mall) {
                $mall = \Yii::$app->mall;
            }
            self::$instance->mall = $mall;
        }
        return self::$instance;
    }

    /**
     * 设置旧版的表单数据到新表中
     * @return null|array
     *
     */
    public function setOldData()
    {
        $option = OptionLogic::get(Option::NAME_ORDER_FORM, \Yii::$app->mall->id, Option::GROUP_APP);
        if (!$option) {
            return null;
        }
        $model = new Form();
        $model->mall_id = $this->mall->id;
        $model->mch_id = 0;
        $model->is_delete = 0;
        $model->status = $option['status'];
        $model->is_default = FormCommon::FORM_DEFAULT;
        $model->name = $option['name'];
        $model->value = json_encode($option['value'], JSON_UNESCAPED_UNICODE);
        $model->save();
        return [$model];
    }

    /**
     * @param $id
     * @return Form|null
     * @throws \Exception
     */
    public function getDetail($id)
    {
        $form = Form::findOne([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $id
        ]);
        if (!$form) {
            throw new \Exception('内容不存在');
        }
        $form->value = json_decode($form->value, true);
        return $form;
    }
}
