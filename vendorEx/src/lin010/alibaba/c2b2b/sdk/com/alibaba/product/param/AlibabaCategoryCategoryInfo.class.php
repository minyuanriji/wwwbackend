<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');
include_once ('AlibabaCategoryGetParam/AlibabaChildCategoryInfo.class.php');
include_once ('AlibabaCategoryGetParam/AlibabaCategoryCategoryInfoFeatureInfo.class.php');

class AlibabaCategoryCategoryInfo extends SDKDomain {

       	
    private $categoryID;
    
        /**
    * @return 类目ID
    */
        public function getCategoryID() {
        return $this->categoryID;
    }
    
    /**
     * 设置类目ID     
     * @param Long $categoryID     
     * 参数示例：<pre>123456</pre>     
     * 此参数必填     */
    public function setCategoryID( $categoryID) {
        $this->categoryID = $categoryID;
    }
    
        	
    private $name;
    
        /**
    * @return 类目名称
    */
        public function getName() {
        return $this->name;
    }
    
    /**
     * 设置类目名称     
     * @param String $name     
     * 参数示例：<pre></pre>     
     * 此参数必填     */
    public function setName( $name) {
        $this->name = $name;
    }
    
        	
    private $level;
    
        /**
    * @return 类目层级，1688无此内容
    */
        public function getLevel() {
        return $this->level;
    }
    
    /**
     * 设置类目层级，1688无此内容     
     * @param Integer $level     
     * 参数示例：<pre></pre>     
     * 此参数必填     */
    public function setLevel( $level) {
        $this->level = $level;
    }
    
        	
    private $isLeaf;
    
        /**
    * @return 是否叶子类目（只有叶子类目才能发布商品）
    */
        public function getIsLeaf() {
        return $this->isLeaf;
    }
    
    /**
     * 设置是否叶子类目（只有叶子类目才能发布商品）     
     * @param Boolean $isLeaf     
     * 参数示例：<pre></pre>     
     * 此参数必填     */
    public function setIsLeaf( $isLeaf) {
        $this->isLeaf = $isLeaf;
    }
    
        	
    private $parentIDs;
    
        /**
    * @return 父类目ID数组,1688只返回一个父id
    */
        public function getParentIDs() {
        return $this->parentIDs;
    }
    
    /**
     * 设置父类目ID数组,1688只返回一个父id     
     * @param array include @see Long[] $parentIDs     
     * 参数示例：<pre></pre>     
     * 此参数必填     */
    public function setParentIDs( $parentIDs) {
        $this->parentIDs = $parentIDs;
    }
    
        	
    private $childIDs;
    
        /**
    * @return 子类目ID数组，1688无此内容
    */
        public function getChildIDs() {
        return $this->childIDs;
    }
    
    /**
     * 设置子类目ID数组，1688无此内容     
     * @param array include @see Long[] $childIDs     
     * 参数示例：<pre></pre>     
     * 此参数必填     */
    public function setChildIDs( $childIDs) {
        $this->childIDs = $childIDs;
    }
    
        	
    private $childCategorys;
    
        /**
    * @return 子类目信息
    */
        public function getChildCategorys() {
        return $this->childCategorys;
    }
    
    /**
     * 设置子类目信息     
     * @param array include @see AlibabaChildCategoryInfo[] $childCategorys     
     * 参数示例：<pre></pre>     
     * 此参数必填     */
    public function setChildCategorys(AlibabaChildCategoryInfo $childCategorys) {
        $this->childCategorys = $childCategorys;
    }
    
        	
    private $minOrderQuantity;
    
        /**
    * @return 最小起订量
    */
        public function getMinOrderQuantity() {
        return $this->minOrderQuantity;
    }
    
    /**
     * 设置最小起订量     
     * @param Long $minOrderQuantity     
     * 参数示例：<pre>1</pre>     
     * 此参数必填     */
    public function setMinOrderQuantity( $minOrderQuantity) {
        $this->minOrderQuantity = $minOrderQuantity;
    }
    
        	
    private $featureInfos;
    
        /**
    * @return 类目特征信息
    */
        public function getFeatureInfos() {
        return $this->featureInfos;
    }
    
    /**
     * 设置类目特征信息     
     * @param array include @see AlibabaCategoryCategoryInfoFeatureInfo[] $featureInfos     
     * 参数示例：<pre>[{}]</pre>     
     * 此参数必填     */
    public function setFeatureInfos(AlibabaCategoryCategoryInfoFeatureInfo $featureInfos) {
        $this->featureInfos = $featureInfos;
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
					    			    			if (array_key_exists ( "categoryID", $this->stdResult )) {
    				$this->categoryID = $this->stdResult->{"categoryID"};
    			}
    			    		    				    			    			if (array_key_exists ( "name", $this->stdResult )) {
    				$this->name = $this->stdResult->{"name"};
    			}
    			    		    				    			    			if (array_key_exists ( "level", $this->stdResult )) {
    				$this->level = $this->stdResult->{"level"};
    			}
    			    		    				    			    			if (array_key_exists ( "isLeaf", $this->stdResult )) {
    				$this->isLeaf = $this->stdResult->{"isLeaf"};
    			}
    			    		    				    			    			if (array_key_exists ( "parentIDs", $this->stdResult )) {
    				$this->parentIDs = $this->stdResult->{"parentIDs"};
    			}
    			    		    				    			    			if (array_key_exists ( "childIDs", $this->stdResult )) {
    				$this->childIDs = $this->stdResult->{"childIDs"};
    			}
    			    		    				    			    			if (array_key_exists ( "childCategorys", $this->stdResult )) {
    			$childCategorysResult=$this->stdResult->{"childCategorys"};
    				$object = json_decode ( json_encode ( $childCategorysResult ), true );
					$this->childCategorys = array ();
					for($i = 0; $i < count ( $object ); $i ++) {
						$arrayobject = new ArrayObject ( $object [$i] );
						$AlibabaChildCategoryInfoResult=new AlibabaChildCategoryInfo();
						$AlibabaChildCategoryInfoResult->setArrayResult($arrayobject );
						$this->childCategorys [$i] = $AlibabaChildCategoryInfoResult;
					}
    			}
    			    		    				    			    			if (array_key_exists ( "minOrderQuantity", $this->stdResult )) {
    				$this->minOrderQuantity = $this->stdResult->{"minOrderQuantity"};
    			}
    			    		    				    			    			if (array_key_exists ( "featureInfos", $this->stdResult )) {
    			$featureInfosResult=$this->stdResult->{"featureInfos"};
    				$object = json_decode ( json_encode ( $featureInfosResult ), true );
					$this->featureInfos = array ();
					for($i = 0; $i < count ( $object ); $i ++) {
						$arrayobject = new ArrayObject ( $object [$i] );
						$AlibabaCategoryCategoryInfoFeatureInfoResult=new AlibabaCategoryCategoryInfoFeatureInfo();
						$AlibabaCategoryCategoryInfoFeatureInfoResult->setArrayResult($arrayobject );
						$this->featureInfos [$i] = $AlibabaCategoryCategoryInfoFeatureInfoResult;
					}
    			}
    			    		    				    			    			if (array_key_exists ( "categoryType", $this->stdResult )) {
    				$this->categoryType = $this->stdResult->{"categoryType"};
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    			if (array_key_exists ( "categoryID", $this->arrayResult )) {
    			$this->categoryID = $arrayResult['categoryID'];
    			}
    		    	    			    		    			if (array_key_exists ( "name", $this->arrayResult )) {
    			$this->name = $arrayResult['name'];
    			}
    		    	    			    		    			if (array_key_exists ( "level", $this->arrayResult )) {
    			$this->level = $arrayResult['level'];
    			}
    		    	    			    		    			if (array_key_exists ( "isLeaf", $this->arrayResult )) {
    			$this->isLeaf = $arrayResult['isLeaf'];
    			}
    		    	    			    		    			if (array_key_exists ( "parentIDs", $this->arrayResult )) {
    			$this->parentIDs = $arrayResult['parentIDs'];
    			}
    		    	    			    		    			if (array_key_exists ( "childIDs", $this->arrayResult )) {
    			$this->childIDs = $arrayResult['childIDs'];
    			}
    		    	    			    		    		if (array_key_exists ( "childCategorys", $this->arrayResult )) {
    		$childCategorysResult=$arrayResult['childCategorys'];
    			$this->childCategorys = new AlibabaChildCategoryInfo();
    			$this->childCategorys->setStdResult ( $childCategorysResult);
    		}
    		    	    			    		    			if (array_key_exists ( "minOrderQuantity", $this->arrayResult )) {
    			$this->minOrderQuantity = $arrayResult['minOrderQuantity'];
    			}
    		    	    			    		    		if (array_key_exists ( "featureInfos", $this->arrayResult )) {
    		$featureInfosResult=$arrayResult['featureInfos'];
    			$this->featureInfos = new AlibabaCategoryCategoryInfoFeatureInfo();
    			$this->featureInfos->setStdResult ( $featureInfosResult);
    		}
    		    	    			    		    			if (array_key_exists ( "categoryType", $this->arrayResult )) {
    			$this->categoryType = $arrayResult['categoryType'];
    			}
    		    	    		}
 
   
}
?>