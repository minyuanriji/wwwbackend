<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-01
 * Time: 21:49
 */

namespace app\forms\common\prints\printer;


use app\forms\common\prints\config\GpConfig;

class GpPrinter extends BasePrinter
{
    public function __construct($config = array())
    {
        $this->config = new GpConfig($config);
    }

    public function getTimes()
    {
        return "";
    }

    public function getCenter($content)
    {
        return "";
    }

    public function getBold($content)
    {
        return "";
    }

    public function getCenterBold($content)
    {
        return "";
    }

    public function getBR($content)
    {
        return "{$content}<gpBr/>";
    }

    public function getTableNoAttr($data)
    {
        $content = '';
        $content .= "名称　　　　　 单价  数量   金额<gpBr/>";
        foreach ($data->goods_list as $k => $v) {
            $price = $v->unit_price;
            $arr = $this->rStrPad1($v->name, 7);
            foreach ($arr as $index => $value) {
                if ($index == 0) {
                    $content .= $value . " " . str_pad($price, 5) . " " . str_pad($v->num, 6) . " " . round($v->total_price, 2) . '<gpBr/>';
                } else {
                    $content .= $value . '<gpBr/>';
                }
            }
        }
        return $content;
    }

    public function getTableAttr($data)
    {
        $content = '';
        $content .= "名称            数量    金额    <gpBr/>";
        foreach ($data->goods_list as $k => $v) {
            $name = $v->name . '（' . $v->attr . ')';
            $nameArr = $this->rStrPad1($name, 8);
            foreach ($nameArr as $index => $value) {
                if ($index == count($nameArr) - 1) {
                    $content .= $nameArr[$index] . " " . str_pad('×' . $v->num, 7) . " " . round($v->total_price, 2) . "<gpBr/>";
                } else {
                    $content .= $nameArr[$index] . '<gpBr/>';
                }
            }
        }
        return $content;
    }
}
