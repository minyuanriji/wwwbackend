<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class ComAlibabaPpOpenClientDtoUnionActivitySkuStockDTO extends SDKDomain {

       	
    private $stock;
    
        /**
    * @return 库存
    */
        public function getStock() {
        return $this->stock;
    }
    
    /**
     * 设置库存     
     * @param Integer $stock     
     * 参数示例：<pre>1</pre>     
     * 此参数必填     */
    public function setStock( $stock) {
        $this->stock = $stock;
    }
    
        	
    private $skuId;
    
        /**
    * @return skuId
    */
        public function getSkuId() {
        return $this->skuId;
    }
    
    /**
     * 设置skuId     
     * @param Long $skuId     
     * 参数示例：<pre>11111</pre>     
     * 此参数必填     */
    public function setSkuId( $skuId) {
        $this->skuId = $skuId;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "stock", $this->stdResult )) {
    				$this->stock = $this->stdResult->{"stock"};
    			}
    			    		    				    			    			if (array_key_exists ( "skuId", $this->stdResult )) {
    				$this->skuId = $this->stdResult->{"skuId"};
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    			if (array_key_exists ( "stock", $this->arrayResult )) {
    			$this->stock = $arrayResult['stock'];
    			}
    		    	    			    		    			if (array_key_exists ( "skuId", $this->arrayResult )) {
    			$this->skuId = $arrayResult['skuId'];
    			}
    		    	    		}
 
   
}
?>