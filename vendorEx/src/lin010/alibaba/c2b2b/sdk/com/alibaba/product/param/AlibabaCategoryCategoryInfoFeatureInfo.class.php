<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class AlibabaCategoryCategoryInfoFeatureInfo extends SDKDomain {

       	
    private $key;
    
        /**
    * @return 名称
    */
        public function getKey() {
        return $this->key;
    }
    
    /**
     * 设置名称     
     * @param String $key     
     * 参数示例：<pre>name</pre>     
     * 此参数必填     */
    public function setKey( $key) {
        $this->key = $key;
    }
    
        	
    private $value;
    
        /**
    * @return 值
    */
        public function getValue() {
        return $this->value;
    }
    
    /**
     * 设置值     
     * @param String $value     
     * 参数示例：<pre>jiagong</pre>     
     * 此参数必填     */
    public function setValue( $value) {
        $this->value = $value;
    }
    
        	
    private $status;
    
        /**
    * @return 状态
    */
        public function getStatus() {
        return $this->status;
    }
    
    /**
     * 设置状态     
     * @param Integer $status     
     * 参数示例：<pre>0</pre>     
     * 此参数必填     */
    public function setStatus( $status) {
        $this->status = $status;
    }
    
        	
    private $hierarchy;
    
        /**
    * @return 是否继承到子元素上
    */
        public function getHierarchy() {
        return $this->hierarchy;
    }
    
    /**
     * 设置是否继承到子元素上     
     * @param Boolean $hierarchy     
     * 参数示例：<pre>true</pre>     
     * 此参数必填     */
    public function setHierarchy( $hierarchy) {
        $this->hierarchy = $hierarchy;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "key", $this->stdResult )) {
    				$this->key = $this->stdResult->{"key"};
    			}
    			    		    				    			    			if (array_key_exists ( "value", $this->stdResult )) {
    				$this->value = $this->stdResult->{"value"};
    			}
    			    		    				    			    			if (array_key_exists ( "status", $this->stdResult )) {
    				$this->status = $this->stdResult->{"status"};
    			}
    			    		    				    			    			if (array_key_exists ( "hierarchy", $this->stdResult )) {
    				$this->hierarchy = $this->stdResult->{"hierarchy"};
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    			if (array_key_exists ( "key", $this->arrayResult )) {
    			$this->key = $arrayResult['key'];
    			}
    		    	    			    		    			if (array_key_exists ( "value", $this->arrayResult )) {
    			$this->value = $arrayResult['value'];
    			}
    		    	    			    		    			if (array_key_exists ( "status", $this->arrayResult )) {
    			$this->status = $arrayResult['status'];
    			}
    		    	    			    		    			if (array_key_exists ( "hierarchy", $this->arrayResult )) {
    			$this->hierarchy = $arrayResult['hierarchy'];
    			}
    		    	    		}
 
   
}
?>