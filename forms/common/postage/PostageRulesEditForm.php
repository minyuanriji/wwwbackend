<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 10:59
 */

namespace app\forms\common\postage;
use app\core\ApiCode;
use app\models\BaseModel;
use app\models\PostageRules;

/**
 * Class PostageRulesEditForm
 * @package app\forms\common\postage
 * @Notes
 * @property PostageRules $model
 */
class PostageRulesEditForm extends BaseModel
{
    public $name;
    public $type;
    public $detail;
    public $model;
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            ['type', 'integer'],
            ['detail', 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        if (empty($this->detail)) {
            return [
                'code' => ApiCode::CODE_FAIL,
                ' msg' => '请填写运费规则'
            ];
        }
        foreach ($this->detail as &$item) {
            if (isset($item['first'])) {
                if (is_numeric($item['first'])) {
                } else {
                    return [
                        'code' => 1,
                        'msg' => '首件/首重必须是数字且不小于0'
                    ];
                }
            } else {
                $item['first'] = 0;
            }
            if (isset($item['firstPrice'])) {
                if (is_numeric($item['firstPrice']) && $item['firstPrice'] >= 0) {
                } else {
                    return [
                        'code' => 1,
                        'msg' => '运费必须是数字且不小于0'
                    ];
                }
            } else {
                $item['firstPrice'] = 0;
            }
            if (isset($item['second'])) {
                if (is_numeric($item['second'])) {
                } else {
                    return [
                        'code' => 1,
                        'msg' => '续件/续重必须是数字且不小于0'
                    ];
                }
            } else {
                $item['second'] = 0;
            }
            if (isset($item['secondPrice'])) {
                if (is_numeric($item['secondPrice']) && $item['secondPrice'] >= 0) {
                } else {
                    return [
                        'code' => 1,
                        'msg' => '运费必须是数字且不小于0'
                    ];
                }
            } else {
                $item['secondPrice'] = 0;
            }
        }

        $this->detail = \Yii::$app->serializer->encode($this->detail);
        $this->model->attributes = $this->attributes;
        if ($this->model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorInfo($this->model);
        }

    }
}