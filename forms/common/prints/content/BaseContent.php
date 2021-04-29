<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-01
 * Time: 21:49
 */

namespace app\forms\common\prints\content;

use app\forms\common\prints\Exceptions\PrintException;

/**
 * Class BaseContent
 * @package app\forms\common\prints\content
 * @property string $attribute
 */
class BaseContent
{
    public function __construct($array = [])
    {
        foreach ($array as $key => $item) {
            if (property_exists($this, $key)) {
                $this->$key = $item;
            }
        }
    }

    /**
     * @param $name
     * @param $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            throw new PrintException(get_class($this) . '不存在属性：' . $name);
        }
    }

    public function setAttribute($array = [])
    {
        foreach ($array as $key => $item) {
            if (property_exists($this, $key)) {
                $this->$key = $item;
            }
        }
    }
}
