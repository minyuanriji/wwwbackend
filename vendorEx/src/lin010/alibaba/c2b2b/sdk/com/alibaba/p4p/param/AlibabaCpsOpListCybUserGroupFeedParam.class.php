<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class AlibabaCpsOpListCybUserGroupFeedParam {

        
        /**
    * @return 选品组id，不传表示取默认选品组下商品；
    */
        public function getGroupId() {
        $tempResult = $this->sdkStdResult["groupId"];
        return $tempResult;
    }
    
    /**
     * 设置选品组id，不传表示取默认选品组下商品；     
     * @param Long $groupId     
     * 参数示例：<pre>1</pre>     
     * 此参数必填     */
    public function setGroupId( $groupId) {
        $this->sdkStdResult["groupId"] = $groupId;
    }
    
        
        /**
    * @return 页码
    */
        public function getPageNo() {
        $tempResult = $this->sdkStdResult["pageNo"];
        return $tempResult;
    }
    
    /**
     * 设置页码     
     * @param Integer $pageNo     
     * 参数示例：<pre>1</pre>     
     * 此参数必填     */
    public function setPageNo( $pageNo) {
        $this->sdkStdResult["pageNo"] = $pageNo;
    }
    
        
        /**
    * @return 每页总数
    */
        public function getPageSize() {
        $tempResult = $this->sdkStdResult["pageSize"];
        return $tempResult;
    }
    
    /**
     * 设置每页总数     
     * @param Integer $pageSize     
     * 参数示例：<pre>10</pre>     
     * 此参数必填     */
    public function setPageSize( $pageSize) {
        $this->sdkStdResult["pageSize"] = $pageSize;
    }
    
        
        /**
    * @return 商品id
    */
        public function getFeedId() {
        $tempResult = $this->sdkStdResult["feedId"];
        return $tempResult;
    }
    
    /**
     * 设置商品id     
     * @param Long $feedId     
     * 参数示例：<pre>12313</pre>     
     * 此参数必填     */
    public function setFeedId( $feedId) {
        $this->sdkStdResult["feedId"] = $feedId;
    }
    
        
        /**
    * @return 商品标题模糊搜索
    */
        public function getTitle() {
        $tempResult = $this->sdkStdResult["title"];
        return $tempResult;
    }
    
    /**
     * 设置商品标题模糊搜索     
     * @param String $title     
     * 参数示例：<pre>女装</pre>     
     * 此参数必填     */
    public function setTitle( $title) {
        $this->sdkStdResult["title"] = $title;
    }
    
        
    private $sdkStdResult=array();
    
    public function getSdkStdResult(){
    	return $this->sdkStdResult;
    }

}
?>