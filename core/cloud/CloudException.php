<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 云插件异常类
 * Author: zal
 * Date: 2020-04-17
 * Time: 17:22
 */
namespace app\core\cloud;

class CloudException extends \Exception
{
    public $raw;

    public function __construct($message = '', $code = 0, $previous = null, $raw)
    {
        $this->raw = $raw;
        parent::__construct($message, $code, $previous);
    }
}
