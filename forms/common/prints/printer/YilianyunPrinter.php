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


use app\forms\common\prints\config\YilianyunConfig;

class YilianyunPrinter extends BasePrinter
{
    public function __construct($config = array())
    {
        $this->config = new YilianyunConfig($config);
    }

    public function getTimes()
    {
        return "<MN>{$this->config->time}</MN>";
    }

    public function getCenter($content)
    {
        return "<center>{$content}</center>";
    }

    public function getBold($content)
    {
        return "<FB>{$content}</FB>";
    }

    public function getCenterBold($content)
    {
        return "<FB><center>{$content}</center></FB>\n";
    }

    public function getBR($content)
    {
        return "{$content}\n";
    }

    public function getTableNoAttr($data)
    {
        $content = '';
        $content .= "<table><tr><td>名称</td><td>数量</td><td>单价</td></tr>";
        foreach ($data->goods_list as $k => $v) {
            $price = $v->unit_price;
            $v->name = str_replace('，', ',', $v->name);
            $arr = $this->rStrPad1($v->name, 8);
            foreach ($arr as $index => $value) {
                if ($index == 0) {
                    $content .= "<tr><td>" . $value . "</td><td>" . $v->num . "</td><td>" . $price . "</td></tr>";
                } else {
                    $content .= "<tr><td>" . $value . "</td></tr>";
                }
            }
        }
        $content .= "</table>";
        return $content;
    }

    public function getTableAttr($data)
    {
        $content = '';
        $content .= "<table><tr><td>名称</td><td>数量</td><td>总价</td></tr>";
        foreach ($data->goods_list as $k => $v) {
            $name = $v->name . '（' . $v->attr . ')';
            $nameArr = $this->rStrPad1($name, 6);
            foreach ($nameArr as $index => $value) {
                if ($index == count($nameArr) - 1) {
                    $content .= "<tr><td>" . $nameArr[$index] . "</td><td>" . '×' . $v->num . "</td><td>" . round($v->total_price, 2) . "</td></tr>";
                } else {
                    $content .= "<tr><td>" . $nameArr[$index] . "</td><td></td><td></td></tr>";
                }
            }
        }
        $content .= "</table>";
        return $content;
    }
}
