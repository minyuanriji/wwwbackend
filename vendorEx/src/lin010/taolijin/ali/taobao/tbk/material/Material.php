<?php
namespace lin010\taolijin\ali\taobao\tbk\material;

use lin010\taolijin\Ali;
use lin010\taolijin\ali\taobao\tbk\TbkBaseResponse;

class Material{

    private $ali;

    public function __construct(Ali $ali){
        $this->ali = $ali;
    }

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

}