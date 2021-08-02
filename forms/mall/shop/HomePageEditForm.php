<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 首页布局新增编辑表单
 * Author: zal
 * Date: 2020-04-13
 * Time: 15:00
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class HomePageEditForm extends BaseModel
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $option = OptionLogic::set(Option::NAME_HOME_PAGE, $this->data, \Yii::$app->mall->id, Option::GROUP_APP);

            if (!$option) {
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

    public function checkData()
    {
        if (!$this->data) {
            throw new \Exception('首页布局不能为空,至少添加一个布局');
        }
    }
}
