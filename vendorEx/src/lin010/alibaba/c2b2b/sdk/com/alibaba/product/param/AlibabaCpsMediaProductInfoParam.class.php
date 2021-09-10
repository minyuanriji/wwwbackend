<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class AlibabaCpsMediaProductInfoParam {

        
        /**
    * @return 1688商品ID，等同于productId
    */
        public function getOfferId() {
        $tempResult = $this->sdkStdResult["offerId"];
        return $tempResult;
    }
    
    /**
     * 设置1688商品ID，等同于productId     
     * @param Long $offerId     
     * 参数示例：<pre>573741401425</pre>     
     * 此参数必填     */
    public function setOfferId( $offerId) {
        $this->sdkStdResult["offerId"] = $offerId;
    }
    
        
        /**
    * @return 是否需要CPS建议价
    */
        public function getNeedCpsSuggestPrice() {
        $tempResult = $this->sdkStdResult["needCpsSuggestPrice"];
        return $tempResult;
    }
    
    /**
     * 设置是否需要CPS建议价     
     * @param Boolean $needCpsSuggestPrice     
     * 参数示例：<pre>true</pre>     
     * 此参数必填     */
    public function setNeedCpsSuggestPrice( $needCpsSuggestPrice) {
        $this->sdkStdResult["needCpsSuggestPrice"] = $needCpsSuggestPrice;
    }
    
        
        /**
    * @return 是否返回算法改写的信息，包括标题、图片和详情图片
    */
        public function getNeedIntelligentInfo() {
        $tempResult = $this->sdkStdResult["needIntelligentInfo"];
        return $tempResult;
    }
    
    /**
     * 设置是否返回算法改写的信息，包括标题、图片和详情图片     
     * @param Boolean $needIntelligentInfo     
     * 参数示例：<pre>true</pre>     
     * 此参数必填     */
    public function setNeedIntelligentInfo( $needIntelligentInfo) {
        $this->sdkStdResult["needIntelligentInfo"] = $needIntelligentInfo;
    }
    
        
    private $sdkStdResult=array();
    
    public function getSdkStdResult(){
    	return $this->sdkStdResult;
    }

}
?>