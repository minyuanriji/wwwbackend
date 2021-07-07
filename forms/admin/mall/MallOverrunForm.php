<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 后台商城-商城分类页面样式配置
 * Author: zal
 * Date: 2020-04-22
 * Time: 19:10
 */

namespace app\forms\admin\mall;

use app\core\ApiCode;
use app\logic\AppConfigLogic;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class MallOverrunForm extends BaseModel
{
    public $form;

    public function rules()
    {
        return [
            [['form'], 'safe']
        ];
    }

    public function save()
    {
        try {
            $this->checkData();
            OptionLogic::set(Option::NAME_OVERRUN, \Yii::$app->request->post('form'), 0, 'admin');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function setting()
    {
        $option = $this->getSetting();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $option
            ]
        ];
    }

    /**
     * 获取设置
     * @return mixed|null
     */
    public function getSetting()
    {
        $option = OptionLogic::get(Option::NAME_OVERRUN, 0, 'admin', $this->getDefault());

        $option = AppConfigLogic::check($option, $this->getDefault());

        $option['is_img_overrun'] = $option['is_img_overrun'] == 'true' ? true : false;
        $option['is_diy_module_overrun'] = $option['is_diy_module_overrun'] == 'true' ? true : false;
        $option['is_video_overrun'] = $option['is_video_overrun'] == 'true' ? true : false;
        return $option;
    }

    /**
     * 获取默认
     * @return array
     */
    public function getDefault()
    {
        return [
            'img_overrun' => 1,
            'is_img_overrun' => false,
            'diy_module_overrun' => 20,
            'is_diy_module_overrun' => false,
            'video_overrun' => 50,
            'is_video_overrun' => false,
        ];
    }

    /**
     * 检测数据
     * @throws \Exception
     */
    private function checkData()
    {
        if ($this->form['img_overrun'] == '') {
            throw new \Exception('请输入上传图片限制');
        }

        if ($this->form['diy_module_overrun'] == '') {
            throw new \Exception('请输入diy组件限制');
        }
        if ($this->form['video_overrun'] == '') {
            throw new \Exception('请输入diy组件限制');
        }
    }
}
