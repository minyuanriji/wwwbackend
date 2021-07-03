<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 海报编辑
 * Author: zal
 * Date: 2020-06-16
 * Time: 10:52
 */

namespace app\forms\mall\poster;

use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class PosterEditForm extends BaseModel
{
    public $data;

    public function save()
    {
        try {
            $this->checkData();
            $newData = [];
            foreach ($this->data as $key => $datum) {
                $newData[$key] = (new OptionLogic())->saveEnd($datum);
            }

            $res = OptionLogic::set(Option::NAME_POSTER, $newData, \Yii::$app->mall->id, Option::GROUP_APP);

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
    }
}
