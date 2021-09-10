<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class ComAlibabaPpOpenClientDtoUnionActivityPromotionItemDTO extends SDKDomain {

       	
    private $skuId;
    
        /**
    * @return sku优惠结果时有意义；对于区间价的优惠结果，此字段无意义，可能为null
    */
        public function getSkuId() {
        return $this->skuId;
    }
    
    /**
     * 设置sku优惠结果时有意义；对于区间价的优惠结果，此字段无意义，可能为null     
     * @param Long $skuId     
     * 参数示例：<pre>111</pre>     
     * 此参数必填     */
    public function setSkuId( $skuId) {
        $this->skuId = $skuId;
    }
    
        	
    private $originalPrice;
    
        /**
    * @return 原价，单位分
    */
        public function getOriginalPrice() {
        return $this->originalPrice;
    }
    
    /**
     * 设置原价，单位分     
     * @param Long $originalPrice     
     * 参数示例：<pre>111</pre>     
     * 此参数必填     */
    public function setOriginalPrice( $originalPrice) {
        $this->originalPrice = $originalPrice;
    }
    
        	
    private $promotionPrice;
    
        /**
    * @return 优惠价，单位分
    */
        public function getPromotionPrice() {
        return $this->promotionPrice;
    }
    
    /**
     * 设置优惠价，单位分     
     * @param Long $promotionPrice     
     * 参数示例：<pre>222</pre>     
     * 此参数必填     */
    public function setPromotionPrice( $promotionPrice) {
        $this->promotionPrice = $promotionPrice;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "skuId", $this->stdResult )) {
    				$this->skuId = $this->stdResult->{"skuId"};
    			}
    			    		    				    			    			if (array_key_exists ( "originalPrice", $this->stdResult )) {
    				$this->originalPrice = $this->stdResult->{"originalPrice"};
    			}
    			    		    				    			    			if (array_key_exists ( "promotionPrice", $this->stdResult )) {
    				$this->promotionPrice = $this->stdResult->{"promotionPrice"};
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    			if (array_key_exists ( "skuId", $this->arrayResult )) {
    			$this->skuId = $arrayResult['skuId'];
    			}
    		    	    			    		    			if (array_key_exists ( "originalPrice", $this->arrayResult )) {
    			$this->originalPrice = $arrayResult['originalPrice'];
    			}
    		    	    			    		    			if (array_key_exists ( "promotionPrice", $this->arrayResult )) {
    			$this->promotionPrice = $arrayResult['promotionPrice'];
    			}
    		    	    		}
 
   
}
?>