<?php
namespace app\plugins\hotel\libs\bestwehotel\client;

use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

interface IClient
{
    /**
     * 生成JSON字符串
     * @return string
     */
    public function getDataJSONString();

    /**
     * 接口地址
     * @return string
     */
    public function getUri();

    /**
     * 解析数据
     * @param $string $content
     * @return BaseReponseModel
     */
    public function parseResult($content);
}