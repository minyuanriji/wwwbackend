<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 云插件
 * Author: zal
 * Date: 2020-04-17
 * Time: 17:22
 */

namespace app\core\cloud;

class CloudPlugin extends CloudBase
{
    public $classVersion = '4.2.31';

    /**
     * 获取列表
     * @param array $args
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     */
    public function getList($args = [])
    {
        return $this->httpGet('/mall/plugin/index');
    }

    /**
     * 详情
     * @param $args
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     */
    public function getDetail($args)
    {

     
        return $this->httpGet('/mall/plugin/detail', $args);
    }

    /**
     * 创建订单
     * @param $id
     * @return mixed
     * @throws CloudException
     */
    public function createOrder($id)
    {
        return $this->httpPost('/mall/plugin/create-order', [], [
            'id' => $id,
        ]);
    }

    /**
     * 安装
     * @param $id
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     */
    public function install($id)
    {
        return $this->httpGet('/mall/plugin/install', ['id' => $id]);
    }
}
