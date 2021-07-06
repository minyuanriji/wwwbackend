<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 云项目接口
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-06-26 16:15
 */

namespace app\clouds;

interface IProject
{
    /**
     * 项目初始化
     * @return void
     */
    public function init();

    /**
     * 获取项目名称
     * @return string 例如智慧社区
     */
    public function getName();

    /**
     * 获取项目版本
     * @return string 例如v1.0.0
     */
    public function getVersion();
}