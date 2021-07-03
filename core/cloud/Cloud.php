<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 云插件
 * Author: zal
 * Date: 2020-04-17
 * Time: 17:22
 */

namespace app\core\cloud;

use yii\base\Component;

/**
 * @property CloudCollect $collect
 * @property CloudTemplate $template
 */
class Cloud extends Component
{
    public $classVersion = '4.2.31';

    /** @var CloudBase $auth */
    public $base;

    /** @var CloudAuth $auth */
    public $auth;

    /** @var CloudPlugin $plugin */
    public $plugin;

    /** @var CloudUpdate $update */
    public $update;

    /** @var CloudWxapp $wxapp */
    public $wxapp;

    /** @var CloudCollect $collect */
    public $collect;

    /** @var CloudTemplate $template */
    public $template;

    public function init()
    {
        parent::init();
        $this->base = new CloudBase();
        $this->auth = new CloudAuth();
        $this->plugin = new CloudPlugin();
        $this->update = new CloudUpdate();
        $this->wxapp = new CloudWxapp();
        $this->collect = new CloudCollect();
        $this->template = new CloudTemplate();
    }
}
