<?php

namespace lin010\taolijin\ali\taobao\tbk\item;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;
use lin010\taolijin\ali\taobao\tbk\item\convert\TbkItemConvertRequest;
use lin010\taolijin\ali\taobao\tbk\item\convert\TbkItemConvertResponse;
use lin010\taolijin\ali\taobao\tbk\item\info\TbkItemInfoGetRequest;
use lin010\taolijin\ali\taobao\tbk\item\info\TbkItemInfoGetResponse;

class Item extends TbkBaseHandle {

    /**
     * 淘宝客-公用-淘宝客商品详情查询(简版)
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function infoGet($params = []){
        return parent::client(TbkItemInfoGetRequest::class, $params)->execute(TbkItemInfoGetResponse::class);
    }

    /**
     * 淘宝客-推广者-商品链接转换
     * @param $num_iids      商品ID串，用','分割，从taobao.tbk.item.get接口获取num_iid字段，最大40个
     * @param $adzone_id     广告位ID
     * @param $unid          自定义输入串，英文和数字组成，长度不能大于12个字符，区分不同的推广渠道
     * @param int $platform  链接形式：1：PC，2：无线，默认：１
     * @return mixed
     * @throws \Exception
     */
    public function convert($num_iids, $adzone_id, $unid, $platform = 1){
        return parent::client(TbkItemConvertRequest::class, [
            "fields" => "click_url",
            "num_iids" => $num_iids
        ])->execute(TbkItemConvertResponse::class);
    }
}