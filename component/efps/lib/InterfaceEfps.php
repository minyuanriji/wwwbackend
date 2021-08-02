<?php


namespace app\component\efps\lib;


interface InterfaceEfps
{
    public function getApi();
    public function getParam();
    public function build($params);
}