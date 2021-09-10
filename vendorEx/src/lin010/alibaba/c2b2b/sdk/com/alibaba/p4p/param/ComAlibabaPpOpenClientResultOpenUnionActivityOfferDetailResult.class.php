<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');
include_once ('AlibabaCpsQueryOfferDetailActivityParam/ComAlibabaPpOpenClientDtoUnionActivityOfferDetailDTO.class.php');

class ComAlibabaPpOpenClientResultOpenUnionActivityOfferDetailResult extends SDKDomain {

       	
    private $result;
    
        /**
    * @return 结果
    */
        public function getResult() {
        return $this->result;
    }
    
    /**
     * 设置结果     
     * @param ComAlibabaPpOpenClientDtoUnionActivityOfferDetailDTO $result     
     * 参数示例：<pre>{}</pre>     
     * 此参数必填     */
    public function setResult(ComAlibabaPpOpenClientDtoUnionActivityOfferDetailDTO $result) {
        $this->result = $result;
    }
    
        	
    private $success;
    
        /**
    * @return 状态
    */
        public function getSuccess() {
        return $this->success;
    }
    
    /**
     * 设置状态     
     * @param Boolean $success     
     * 参数示例：<pre>true</pre>     
     * 此参数必填     */
    public function setSuccess( $success) {
        $this->success = $success;
    }
    
        	
    private $errorCode;
    
        /**
    * @return errorCode
    */
        public function getErrorCode() {
        return $this->errorCode;
    }
    
    /**
     * 设置errorCode     
     * @param String $errorCode     
     * 参数示例：<pre>101</pre>     
     * 此参数必填     */
    public function setErrorCode( $errorCode) {
        $this->errorCode = $errorCode;
    }
    
        	
    private $errorMsg;
    
        /**
    * @return errorMsg
    */
        public function getErrorMsg() {
        return $this->errorMsg;
    }
    
    /**
     * 设置errorMsg     
     * @param String $errorMsg     
     * 参数示例：<pre>errorMsg</pre>     
     * 此参数必填     */
    public function setErrorMsg( $errorMsg) {
        $this->errorMsg = $errorMsg;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "result", $this->stdResult )) {
    				$resultResult=$this->stdResult->{"result"};
    				$this->result = new ComAlibabaPpOpenClientDtoUnionActivityOfferDetailDTO();
    				$this->result->setStdResult ( $resultResult);
    			}
    			    		    				    			    			if (array_key_exists ( "success", $this->stdResult )) {
    				$this->success = $this->stdResult->{"success"};
    			}
    			    		    				    			    			if (array_key_exists ( "errorCode", $this->stdResult )) {
    				$this->errorCode = $this->stdResult->{"errorCode"};
    			}
    			    		    				    			    			if (array_key_exists ( "errorMsg", $this->stdResult )) {
    				$this->errorMsg = $this->stdResult->{"errorMsg"};
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    		if (array_key_exists ( "result", $this->arrayResult )) {
    		$resultResult=$arrayResult['result'];
    			    			$this->result = new ComAlibabaPpOpenClientDtoUnionActivityOfferDetailDTO();
    			    			$this->result->setStdResult ( $resultResult);
    		}
    		    	    			    		    			if (array_key_exists ( "success", $this->arrayResult )) {
    			$this->success = $arrayResult['success'];
    			}
    		    	    			    		    			if (array_key_exists ( "errorCode", $this->arrayResult )) {
    			$this->errorCode = $arrayResult['errorCode'];
    			}
    		    	    			    		    			if (array_key_exists ( "errorMsg", $this->arrayResult )) {
    			$this->errorMsg = $arrayResult['errorMsg'];
    			}
    		    	    		}
 
   
}
?>