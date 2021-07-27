<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 图片魔方新增编辑表单
 * Author: zal
 * Date: 2020-04-13
 * Time: 15:00
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\ImgMagic;

class ImgMagicEditForm extends BaseModel
{
    public $name;
    public $type;
    public $value;
    public $id;

    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['name',], 'string'],
            [['type', 'id'], 'integer'],
            [['value'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'type' => '样式类型',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            if (!$this->type) {
                throw new \Exception('请选择魔方样式');
            }

            if ($this->id) {
                $imgMagic = ImgMagic::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$imgMagic) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $imgMagic = new ImgMagic();
            }

            $imgMagic->name = $this->name;
            $imgMagic->type = $this->type;
            $imgMagic->mall_id = \Yii::$app->mall->id;
            $imgMagic->value = json_encode($this->value);
            $res = $imgMagic->save();

            if (!$res) {
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
}
