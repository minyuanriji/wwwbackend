<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 全局颜色设置
 * Author: zal
 * Date: 2020-09-14
 * Time: 14:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class ColorEditForm extends BaseModel
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $res = OptionLogic::set(Option::NAME_COLOR, $this->data, \Yii::$app->mall->id, Option::GROUP_APP);

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

    // 检测数据
    public function checkData()
    {
    }
}
