<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 模板核心抽象类
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\forms\common\template\tplmsg;


use app\forms\common\template\TemplateForm;
use app\forms\common\template\TemplateSend;
use app\models\BaseModel;
use app\models\User;

/**
 * @property User $user
 * @property TemplateForm $templateForm
 */
abstract class BaseTemplate extends BaseModel
{
    public $user;
    public $page;
    protected $templateTpl;
    public $templateForm;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->setTemplateForm();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    abstract public function msg();

    /**
     * @return mixed
     * @throws \Exception
     * 测试发送模板消息
     */
    abstract public function test();

    /**
     * @return mixed
     * 模板消息配置
     */
    abstract public function setTemplateForm();

    /**
     * @return array
     * 获取data的键值，目前主要是微信订阅消息用到
     */
    public function dataKey()
    {
        if ($this->templateForm && $this->templateForm instanceof TemplateForm) {
            return $this->templateForm->templateInfo;
        } else {
            return [];
        }
    }

    /**
     * @return array
     * @throws \Exception
     * 发送模板消息
     */
    public function send()
    {
        try {
            $template = new TemplateSend();
            $template->user = $this->user;
            $template->page = $this->page;
            $template->data = $this->msg();
            $template->dataKey = $this->dataKey();
            $template->templateTpl = $this->templateTpl;
            return $template->sendTemplate();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }
    }
}
