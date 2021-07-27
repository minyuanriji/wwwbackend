<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 报名失败通知
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\forms\common\template\tplmsg;

/**
 * Class ActivityRefundTemplate
 * @package app\forms\common\template\tplmsg
 * 报名失败通知
 */
class ActivityRefundTemplate extends BaseTemplate
{
    public $activityName;
    public $name;
    public $remark;
    protected $templateTpl = 'enroll_error_tpl';

    public function msg()
    {
        return [
            'keyword1' => [
                'value' => $this->activityName,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->name,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->remark,
                'color' => '#333333',
            ],
        ];
    }

    public function test()
    {
        $this->activityName = '双十一活动';
        $this->name = '即将结束';
        $this->remark = '请及时领取';
        return $this->send();
    }

    public function setTemplateForm()
    {
        $this->templateForm = null;
    }
}
