<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 自定义海报新增或编辑表单
 * Author: zal
 * Date: 2020-04-14
 * Time: 10:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\logic\AppConfigLogic;
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
                $newData[$key] = (new AppConfigLogic())->saveEnd($datum);
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

    /**
     * 检测数据是否完整
     * @throws \Exception
     */
    public function checkData()
    {
        if (!$this->data && is_array($this->data)) {
            throw new \Exception('请检查信息是否填写完整');
        }
    }
}
