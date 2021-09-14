<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class ComAlibabaPpOpenClientDtoUnionActivityRangePriceDTO extends SDKDomain {

       	
    private $price;
    
        /**
    * @return 区间价数组，每个item为一个价格,以分为单位
    */
        public function getPrice() {
        return $this->price;
    }
    
    /**
     * 设置区间价数组，每个item为一个价格,以分为单位     
     * @param array include @see Long[] $price     
     * 参数示例：<pre>[1,2,3]</pre>     
     * 此参数必填     */
    public function setPrice( $price) {
        $this->price = $price;
    }
    
        	
    private $beginQuantity;
    
        /**
    * @return 区间对应的起批量
    */
        public function getBeginQuantity() {
        return $this->beginQuantity;
    }
    
    /**
     * 设置区间对应的起批量     
     * @param array include @see Integer[] $beginQuantity     
     * 参数示例：<pre>[3,6,9]</pre>     
     * 此参数必填     */
    public function setBeginQuantity( $beginQuantity) {
        $this->beginQuantity = $beginQuantity;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "price", $this->stdResult )) {
    				$this->price = $this->stdResult->{"price"};
    			}
    			    		    				    			    			if (array_key_exists ( "beginQuantity", $this->stdResult )) {
    				$this->beginQuantity = $this->stdResult->{"beginQuantity"};
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    			if (array_key_exists ( "price", $this->arrayResult )) {
    			$this->price = $arrayResult['price'];
    			}
    		    	    			    		    			if (array_key_exists ( "beginQuantity", $this->arrayResult )) {
    			$this->beginQuantity = $arrayResult['beginQuantity'];
    			}
    		    	    		}
 
   
}
?>