<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 云插件模板
 * Author: zal
 * Date: 2020-04-17
 * Time: 17:22
 */

namespace app\core\cloud;

class CloudTemplate extends CloudBase
{

    /**
     * 获取列表
     * @param array $args
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     */
    public function getList($args = [])
    {
        return $this->httpGet('/mall/template/index', $args);
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
        return $this->httpGet('/mall/template/detail', $args);
    }

    /**
     * 创建订单
     * @param $id
     * @return mixed
     * @throws CloudException
     */
    public function createOrder($id)
    {
        return $this->httpPost('/mall/template/create-order', [], [
            'id' => $id,
        ]);
    }

    /**
     * 订单详情
     * @param $id
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     */
    public function orderDetail($id)
    {
        return $this->httpGet('/mall/template/order-detail', [
            'id' => $id
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
        return $this->httpGet('/mall/template/package', [
            'id' => $id
        ]);
    }

    /**
     * 所有模板id
     * @param $params
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     */
    public function allId($params)
    {
        return $this->httpGet('/mall/template/all-id', $params);
    }
}
