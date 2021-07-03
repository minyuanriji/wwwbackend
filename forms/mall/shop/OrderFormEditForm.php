<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 下单表单
 * Author: zal
 * Date: 2020-04-14
 * Time: 10:33
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\forms\common\FormCommon;
use app\models\BaseModel;
use app\models\Form;
use app\models\Option;

class OrderFormEditForm extends BaseModel
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $commonForm = FormCommon::getInstance();
            if ($this->data['id'] && $this->data['id'] > 0) {
                $model = $commonForm->getDetail($this->data['id']);
            } else {
                $model = new Form();
                $model->is_delete = 0;
                $model->is_default = FormCommon::FORM_NOT_DEFAULT;
                $model->mall_id = \Yii::$app->mall->id;
                $model->mch_id = 0;
            }
            $model->status = $this->data['status'];
            $model->name = $this->data['name'];
            $model->value = json_encode($this->data['value'], JSON_UNESCAPED_UNICODE);

            if (!$model->save()) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    // 检测数据
    public function checkData()
    {
        if ($this->data['status'] == 0) {
            !isset($this->data['value']) ? $this->data['value'] = [] : '';
            return;
        }

        foreach ($this->data as $key => $item) {
            if (!is_array($item) && !$item && !in_array($key, ['id'])) {
                throw new \Exception('请检查信息是否填写完整');
            }
        }
        foreach ($this->data['value'] as $item) {
            if (!$item['name']) {
                throw new \Exception('请填写 ' . $item['key_name'] . ' 名称');
            }
            if (isset($item['list'])) {
                foreach ($item['list'] as $item2) {
                    if (!$item2['label']) {
                        throw new \Exception('请填写 ' . $item['key_name'] . ' 选项名称');
                    }
                }
            }
        }
    }
}
