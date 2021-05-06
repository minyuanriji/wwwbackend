<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户中心新增编辑表单
 * Author: zal
 * Date: 2020-04-13
 * Time: 10:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class UserCenterEditForm extends BaseModel
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $res = OptionLogic::set(Option::NAME_USER_CENTER, $this->data, \Yii::$app->mall->id, Option::GROUP_APP);

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

    public function checkData()
    {
        if (!isset($this->data['menus'])) {
            $this->data['menus'] = [];
        }
        if (isset($this->data['account_bar'])) {
            foreach ($this->data['account_bar'] as $index => $item) {
                if (is_array($item) && mb_strlen($item['text']) > 4) {
                    throw new \Exception('我的账户--文字说明不能大于4个字');
                }
            }
        }
    }

    public function reset()
    {
        $userCenterDefault = (new UserCenterForm())->getDefault();
        $this->data = $userCenterDefault;
        return $this->save();
    }
}
