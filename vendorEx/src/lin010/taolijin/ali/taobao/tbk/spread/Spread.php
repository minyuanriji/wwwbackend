<?php

namespace lin010\taolijin\ali\taobao\tbk\spread;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;

class Spread extends TbkBaseHandle{

    /**
     * 淘宝客-公用-长链转短链
     * @param string $origin_url 原始链接
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function toShort($origin_url){
        if(substr($origin_url, 0, 2) == "//"){
            $origin_url = "http:" . $origin_url;
        }
        $params = [
            "requests" => json_encode([
                ["url" => $origin_url]
            ])
        ];
        $response = parent::client(TbkSpreadGetRequest::class, $params)->execute(TbkSpreadGetResponse::class);
        return $response->getContent();
    }
}
