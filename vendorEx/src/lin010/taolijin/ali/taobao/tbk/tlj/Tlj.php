<?php
namespace lin010\taolijin\ali\taobao\tbk\tlj;

use lin010\taolijin\Ali;

class Tlj{
    private $ali;

    public function __construct(Ali $ali){
        $this->ali = $ali;
    }


    public function vegasTljCreate($params = []){
        $req = new TbkDgVegasTljCreateRequest();
        foreach($params as $key => $val){
            $req->$key = $val;
        }

        //$result = $this->ali->getClient()->execute($req);

        $str = '<tbk_dg_vegas_tlj_create_response>
    <result>
        <model>
            <rights_id>asfasdfasd</rights_id>
            <send_url>https://www.taobao.com</send_url>
            <vegas_code>asfasdfasd</vegas_code>
            <available_fee>20.23</available_fee>
        </model>
        <msg_code>0</msg_code>
        <msg_info></msg_info>
        <success>false</success>
    </result>
</tbk_dg_vegas_tlj_create_response>';
        $result = simplexml_load_string($str);

        return new TbkDgVegasTljCreateResponse($result);
    }
}