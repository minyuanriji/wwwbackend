<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 版权新增或编辑表单
 * Author: zal
 * Date: 2020-04-14
 * Time: 10:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class CopyrightEditForm extends BaseModel
{
    public $data;
    public $mall_id;

    public function rules()
    {
        return [
            [['data'], 'safe'],
            [['mall_id'], 'integer']
        ];
    }

    public function save()
    {
        try {
            if (!$this->data) {
                throw new \Exception('请输入form参数数据');
            }
            $mallId = $this->mall_id ? $this->mall_id : \Yii::$app->mall->id;
            $res = OptionLogic::set(Option::NAME_COPYRIGHT, $this->data, $mallId, Option::GROUP_APP);

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
