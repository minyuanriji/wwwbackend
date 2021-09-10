<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class AlibabaCpsQueryOfferDetailActivityParam {

        
        /**
    * @return 商品id
    */
        public function getOfferId() {
        $tempResult = $this->sdkStdResult["offerId"];
        return $tempResult;
    }
    
    /**
     * 设置商品id     
     * @param Long $offerId     
     * 参数示例：<pre>591047134663</pre>     
     * 此参数必填     */
    public function setOfferId( $offerId) {
        $this->sdkStdResult["offerId"] = $offerId;
    }
    
        
    private $sdkStdResult=array();
    
    public function getSdkStdResult(){
    	return $this->sdkStdResult;
    }

}
?>