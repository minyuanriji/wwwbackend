<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');
include_once ('AlibabaCpsOpListCybUserGroupFeedParam/ComAlibabaPpOpenClientResultOpenUnionOpGroupFeedListResult.class.php');

class AlibabaCpsOpListCybUserGroupFeedResult {

        	
    private $result;
    
        /**
    * @return 结果
    */
        public function getResult() {
        return $this->result;
    }
    
    /**
     * 设置结果     
     * @param ComAlibabaPpOpenClientResultOpenUnionOpGroupFeedListResult $result     
          
     * 此参数必填     */
    public function setResult(ComAlibabaPpOpenClientResultOpenUnionOpGroupFeedListResult $result) {
        $this->result = $result;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "result", $this->stdResult )) {
    				$resultResult=$this->stdResult->{"result"};
    				$this->result = new ComAlibabaPpOpenClientResultOpenUnionOpGroupFeedListResult();
    				$this->result->setStdResult ( $resultResult);
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    		if (array_key_exists ( "result", $this->arrayResult )) {
    		$resultResult=$arrayResult['result'];
    			    			$this->result = new ComAlibabaPpOpenClientResultOpenUnionOpGroupFeedListResult();
    			    			$this->result->setStdResult ( $resultResult);
    		}
    		    	    		}

}
?>