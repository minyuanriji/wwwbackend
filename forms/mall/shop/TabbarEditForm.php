<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 导航图标
 * Author: zal
 * Date: 2020-04-13
 * Time: 10:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class TabbarEditForm extends BaseModel
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $res = OptionLogic::set(Option::NAME_NAVBAR, $this->data, \Yii::$app->mall->id, Option::GROUP_APP);

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
        if (!$this->data && is_array($this->data)) {
            throw new \Exception('请检查信息是否填写完整x01');
        }

        if (!isset($this->data['navs'])) {
            throw new \Exception('至少添加一个导航菜单');
        }

        foreach ($this->data as $key => $item) {
            if ($key == 'navs') {
                foreach ($item as $item2) {
                    if (!$item2['active_color'] || !$item2['active_icon'] || !$item2['color']
                        || !$item2['text'] || !$item2['icon'] || !$item2['url']) {
                        throw new \Exception('请检查信息是否填写完整x02');
                    }
                }
            }
        }
    }
}
