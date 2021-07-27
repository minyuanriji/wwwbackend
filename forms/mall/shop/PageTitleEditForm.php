<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 页面标题操作
 * Author: zal
 * Date: 2020-04-14
 * Time: 15:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class PageTitleEditForm extends BaseModel
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $res = OptionLogic::set(Option::NAME_PAGE_TITLE, $this->data, \Yii::$app->mall->id, Option::GROUP_APP);

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
            throw new \Exception('请检查信息是否填写完整');
        }

        foreach ($this->data as &$item) {
            if (!$item['new_name']) {
                $item['new_name'] = $item['name'];
            }
            if (!$item['new_name']) {
                throw new \Exception($item['name'] . '标题不能为空');
            }
        }
        unset($item);
    }
}
