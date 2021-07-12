<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-10 14:03
 */
namespace app\clouds\base\helpers;


class FuncHelper
{
    /**
     * 类路径转换
     * @param $str
     * @return string
     */
    public static function convertNamesPath($str)
    {
        $str = ltrim($str, "/");
        $str = rtrim($str, "/");
        $str = ltrim($str, "\\");
        $str = trim(rtrim($str, "\\"));

        $str = str_replace("/", "\\", $str);

        $arr1 = explode("\\", $str);
        $dir = "";
        if($arr1)
        {
            $part1 = [];
            foreach($arr1 as $str1)
            {
                $str1 = trim($str1);
                if(empty($str1))
                {
                    continue;
                }

                $arr2 = explode("-", $str1);
                $part2 = [array_shift($arr2)];
                if(!empty($arr2))
                {
                    foreach($arr2 as $str2)
                    {
                        $part2[] = ucfirst($str2);
                    }
                    $part1[] = implode("", $part2);
                }else{
                    $part1[] = $str1;
                }
            }
            $dir = implode("\\", $part1);
        }

        return $dir;
    }
}