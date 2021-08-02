<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-01
 * Time: 21:49
 */

namespace app\forms\common\prints\templates;

use app\forms\common\prints\content\OrderContent;
use app\forms\common\prints\printer\BasePrinter;

/**
 * Class BaseTemplate
 * @package app\forms\common\prints\templates
 * @property OrderContent $data
 * @property BasePrinter $printer
 */
abstract class BaseTemplate
{
    public $data;
    public $printer;

    public function __construct($array = [])
    {
        foreach ($array as $key => $item) {
            if (property_exists($this, $key)) {
                $this->$key = $item;
            }
        }
    }

    abstract public function getContent();

    /**
     * 返回数组 键值说明
     * handle 表示操作方法名
     * content 表示操作方法的参数
     * show 表示是否执行操作
     * children 表示需要先执行的操作数组
     */
    abstract public function getContentByArray();
}
