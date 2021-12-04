<?php
namespace lin010\taolijin\ali\taobao\tbk\material;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;
use lin010\taolijin\ali\taobao\tbk\TbkBaseResponse;

class Material extends TbkBaseHandle{

    /**
     * 淘宝客-推广者-物料精选
     * @param array $params
     * @return TbkBaseResponse
     */
    public function optimusSearch($params = []){
        $req = new TbkDgOptimusMaterialRequest();
        foreach($params as $key => $val){
            $req->$key = $val;
        }

        $result = $this->ali->getClient()->execute($req);

        return new TbkDgOptimusMaterialResponse($result);
    }

    /**
     * 淘宝客-推广者-物料搜索
     * @param array $params
     * @return TbkBaseResponse
     */
    public function search($params = []){
        return parent::client(TbkDgMaterialOptionalRequest::class, $params)->execute(TbkDgMaterialOptionalResponse::class);
    }
}