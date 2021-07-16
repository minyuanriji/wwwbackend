<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-07 15:54
 */
namespace app\clouds\errors;


use app\clouds\consts\Code;
use Throwable;

class CloudException extends BaseException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if($code == 0)
        {
            $code = Code::ERROR;
        }

        parent::__construct($message, $code, $previous);
    }
}