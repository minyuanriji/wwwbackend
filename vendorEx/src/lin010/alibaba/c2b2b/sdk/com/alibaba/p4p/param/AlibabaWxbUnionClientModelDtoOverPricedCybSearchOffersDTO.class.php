<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class AlibabaWxbUnionClientModelDtoOverPricedCybSearchOffersDTO extends SDKDomain {

       	
    private $title;
    
        /**
    * @return todo
    */
        public function getTitle() {
        return $this->title;
    }
    
    /**
     * 设置todo     
     * @param String $title     
     * 参数示例：<pre>todo</pre>     
     * 此参数必填     */
    public function setTitle( $title) {
        $this->title = $title;
    }
    
        	
    private $imgUrl;
    
        /**
    * @return todo
    */
        public function getImgUrl() {
        return $this->imgUrl;
    }
    
    /**
     * 设置todo     
     * @param String $imgUrl     
     * 参数示例：<pre>todo</pre>     
     * 此参数必填     */
    public function setImgUrl( $imgUrl) {
        $this->imgUrl = $imgUrl;
    }
    
        	
    private $offerId;
    
        /**
    * @return todo
    */
        public function getOfferId() {
        return $this->offerId;
    }
    
    /**
     * 设置todo     
     * @param Long $offerId     
     * 参数示例：<pre>todo</pre>     
     * 此参数必填     */
    public function setOfferId( $offerId) {
        $this->offerId = $offerId;
    }
    
        	
    private $soldOut;
    
        /**
    * @return todo
    */
        public function getSoldOut() {
        return $this->soldOut;
    }
    
    /**
     * 设置todo     
     * @param Long $soldOut     
     * 参数示例：<pre>todo</pre>     
     * 此参数必填     */
    public function setSoldOut( $soldOut) {
        $this->soldOut = $soldOut;
    }
    
        	
    private $superBuyerPrice;
    
        /**
    * @return todo
    */
        public function getSuperBuyerPrice() {
        return $this->superBuyerPrice;
    }
    
    /**
     * 设置todo     
     * @param Double $superBuyerPrice     
     * 参数示例：<pre>todo</pre>     
     * 此参数必填     */
    public function setSuperBuyerPrice( $superBuyerPrice) {
        $this->superBuyerPrice = $superBuyerPrice;
    }
    
        	
    private $enable;
    
        /**
    * @return todo
    */
        public function getEnable() {
        return $this->enable;
    }
    
    /**
     * 设置todo     
     * @param Boolean $enable     
     * 参数示例：<pre>todo</pre>     
     * 此参数必填     */
    public function setEnable( $enable) {
        $this->enable = $enable;
    }
    
        	
    private $profit;
    
        /**
    * @return todo
    */
        public function getProfit() {
        return $this->profit;
    }
    
    /**
     * 设置todo     
     * @param String $profit     
     * 参数示例：<pre>todo</pre>     
     * 此参数必填     */
    public function setProfit( $profit) {
        $this->profit = $profit;
    }
    
        	
    private $currentPrice;
    
        /**
    * @return 分销价
    */
        public function getCurrentPrice() {
        return $this->currentPrice;
    }
    
    /**
     * 设置分销价     
     * @param Double $currentPrice     
     * 参数示例：<pre>11.2</pre>     
     * 此参数必填     */
    public function setCurrentPrice( $currentPrice) {
        $this->currentPrice = $currentPrice;
    }
    
        	
    private $offerTags;
    
        /**
    * @return 标签数组
    */
        public function getOfferTags() {
        return $this->offerTags;
    }
    
    /**
     * 设置标签数组     
     * @param array include @see String[] $offerTags     
     * 参数示例：<pre>["48小时发货", "15+天包换", "免费赊账"]</pre>     
     * 此参数必填     */
    public function setOfferTags( $offerTags) {
        $this->offerTags = $offerTags;
    }
    
        	
    private $channelPrice;
    
        /**
    * @return 渠道专属价
    */
        public function getChannelPrice() {
        return $this->channelPrice;
    }
    
    /**
     * 设置渠道专属价     
     * @param Double $channelPrice     
     * 参数示例：<pre>10.1</pre>     
     * 此参数必填     */
    public function setChannelPrice( $channelPrice) {
        $this->channelPrice = $channelPrice;
    }
    
    	
	private $stdResult;
	
	public function setStdResult($stdResult) {
		$this->stdResult = $stdResult;
					    			    			if (array_key_exists ( "title", $this->stdResult )) {
    				$this->title = $this->stdResult->{"title"};
    			}
    			    		    				    			    			if (array_key_exists ( "imgUrl", $this->stdResult )) {
    				$this->imgUrl = $this->stdResult->{"imgUrl"};
    			}
    			    		    				    			    			if (array_key_exists ( "offerId", $this->stdResult )) {
    				$this->offerId = $this->stdResult->{"offerId"};
    			}
    			    		    				    			    			if (array_key_exists ( "soldOut", $this->stdResult )) {
    				$this->soldOut = $this->stdResult->{"soldOut"};
    			}
    			    		    				    			    			if (array_key_exists ( "superBuyerPrice", $this->stdResult )) {
    				$this->superBuyerPrice = $this->stdResult->{"superBuyerPrice"};
    			}
    			    		    				    			    			if (array_key_exists ( "enable", $this->stdResult )) {
    				$this->enable = $this->stdResult->{"enable"};
    			}
    			    		    				    			    			if (array_key_exists ( "profit", $this->stdResult )) {
    				$this->profit = $this->stdResult->{"profit"};
    			}
    			    		    				    			    			if (array_key_exists ( "currentPrice", $this->stdResult )) {
    				$this->currentPrice = $this->stdResult->{"currentPrice"};
    			}
    			    		    				    			    			if (array_key_exists ( "offerTags", $this->stdResult )) {
    				$this->offerTags = $this->stdResult->{"offerTags"};
    			}
    			    		    				    			    			if (array_key_exists ( "channelPrice", $this->stdResult )) {
    				$this->channelPrice = $this->stdResult->{"channelPrice"};
    			}
    			    		    		}
	
	private $arrayResult;
	public function setArrayResult($arrayResult) {
		$this->arrayResult = $arrayResult;
				    		    			if (array_key_exists ( "title", $this->arrayResult )) {
    			$this->title = $arrayResult['title'];
    			}
    		    	    			    		    			if (array_key_exists ( "imgUrl", $this->arrayResult )) {
    			$this->imgUrl = $arrayResult['imgUrl'];
    			}
    		    	    			    		    			if (array_key_exists ( "offerId", $this->arrayResult )) {
    			$this->offerId = $arrayResult['offerId'];
    			}
    		    	    			    		    			if (array_key_exists ( "soldOut", $this->arrayResult )) {
    			$this->soldOut = $arrayResult['soldOut'];
    			}
    		    	    			    		    			if (array_key_exists ( "superBuyerPrice", $this->arrayResult )) {
    			$this->superBuyerPrice = $arrayResult['superBuyerPrice'];
    			}
    		    	    			    		    			if (array_key_exists ( "enable", $this->arrayResult )) {
    			$this->enable = $arrayResult['enable'];
    			}
    		    	    			    		    			if (array_key_exists ( "profit", $this->arrayResult )) {
    			$this->profit = $arrayResult['profit'];
    			}
    		    	    			    		    			if (array_key_exists ( "currentPrice", $this->arrayResult )) {
    			$this->currentPrice = $arrayResult['currentPrice'];
    			}
    		    	    			    		    			if (array_key_exists ( "offerTags", $this->arrayResult )) {
    			$this->offerTags = $arrayResult['offerTags'];
    			}
    		    	    			    		    			if (array_key_exists ( "channelPrice", $this->arrayResult )) {
    			$this->channelPrice = $arrayResult['channelPrice'];
    			}
    		    	    		}
 
   
}
?>