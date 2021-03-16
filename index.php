<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-01
 * Time: 20:34
 */
header('location:web/index.php');
header('Access-Control-Allow-Origin: *');
//允许PUT,GET,POST,DELETE访问
header('Access-Control-Allow-Methods: PUT,GET,POST,DELETE,OPTIONS');
// 允许sign,token字段方法
header('Access-Control-Allow-Headers: *');
