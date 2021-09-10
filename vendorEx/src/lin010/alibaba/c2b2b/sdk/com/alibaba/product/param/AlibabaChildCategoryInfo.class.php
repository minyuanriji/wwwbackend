<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class AlibabaChildCategoryInfo extends SDKDomain {

       	
    private $id;
    
        /**
    * @return 子类目ID
    */
        public function getId() {
        return $this->id;
    }
    
    /**
     * 设置子类目ID     
     * @param Long $id     
     * 参数示例：<pre></pre>     
     * 此参数必填     */
    public function setId( $id) {
        $this->id = $id;
    }
    
        	
    private $name;
    
        /**
    * @return 子类目名称
    */
        public function getName() {
        return $this->name;
    }
    
    /**
     * 设置子类目名称     
     * @param String $name     
     * 参数示例：<pre></pre>     
     * 此参数必填     */
    public function setName( $name) {
        $this->name = $name;
    }
    
        	
    private $categoryType;
    
        /**
    * @return 类目的类型：1表示cbu类目，2表示gallop类目
    */
        public function getCategoryType() {
        return $this->categoryType;
    }
    
    /**
     * 设置类目的类型：1表示cbu类目，2表示gallop类目     
     * @param String $categoryType     
     * 参数示例：<pre>1</pre>     
     * 此参数必填     */
    public function setCategoryType( $categoryType) {
        $this->categoryType = $categoryType;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "id", $this->stdResult )) {
    				$this->id = $this->stdResult->{"id"};
    			}
    			    		    				    			    			if (array_key_exists ( "name", $this->stdResult )) {
    				$this->name = $this->stdResult->{"name"};
    			}
    			    		    				    			    			if (array_key_exists ( "categoryType", $this->stdResult )) {
    				$this->categoryType = $this->stdResult->{"categoryType"};
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    			if (array_key_exists ( "id", $this->arrayResult )) {
    			$this->id = $arrayResult['id'];
    			}
    		    	    			    		    			if (array_key_exists ( "name", $this->arrayResult )) {
    			$this->name = $arrayResult['name'];
    			}
    		    	    			    		    			if (array_key_exists ( "categoryType", $this->arrayResult )) {
    			$this->categoryType = $arrayResult['categoryType'];
    			}
    		    	    		}
 
   
}
?>