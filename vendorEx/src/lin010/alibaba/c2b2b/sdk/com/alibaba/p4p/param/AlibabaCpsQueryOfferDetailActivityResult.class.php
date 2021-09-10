<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');
include_once ('AlibabaCpsQueryOfferDetailActivityParam/ComAlibabaPpOpenClientResultOpenUnionActivityOfferDetailResult.class.php');

class AlibabaCpsQueryOfferDetailActivityResult {

        	
    private $result;
    
        /**
    * @return 营销活动结果；历史原因，无活动时直接返回了空对象{}没有返回错误码，注意兼容
    */
        public function getResult() {
        return $this->result;
    }
    
    /**
     * 设置营销活动结果；历史原因，无活动时直接返回了空对象{}没有返回错误码，注意兼容     
     * @param ComAlibabaPpOpenClientResultOpenUnionActivityOfferDetailResult $result     
          
     * 此参数必填     */
    public function setResult(ComAlibabaPpOpenClientResultOpenUnionActivityOfferDetailResult $result) {
        $this->result = $result;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "result", $this->stdResult )) {
    				$resultResult=$this->stdResult->{"result"};
    				$this->result = new ComAlibabaPpOpenClientResultOpenUnionActivityOfferDetailResult();
    				$this->result->setStdResult ( $resultResult);
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    		if (array_key_exists ( "result", $this->arrayResult )) {
    		$resultResult=$arrayResult['result'];
    			    			$this->result = new ComAlibabaPpOpenClientResultOpenUnionActivityOfferDetailResult();
    			    			$this->result->setStdResult ( $resultResult);
    		}
    		    	    		}

}
?>