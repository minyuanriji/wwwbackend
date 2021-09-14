<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class AlibabaCpsOpSaveMediaUserBehaviourParam {

        
        /**
    * @return 代表唯一一条日志记录
    */
        public function getUuid() {
        $tempResult = $this->sdkStdResult["uuid"];
        return $tempResult;
    }
    
    /**
     * 设置代表唯一一条日志记录     
     * @param String $uuid     
     * 参数示例：<pre>safafwfweqr2313fsafa</pre>     
     * 此参数必填     */
    public function setUuid( $uuid) {
        $this->sdkStdResult["uuid"] = $uuid;
    }
    
        
        /**
    * @return 设备id求md5(32位小写)(手机号与设备号至少一个)
    */
        public function getDeviceIdMd5() {
        $tempResult = $this->sdkStdResult["deviceIdMd5"];
        return $tempResult;
    }
    
    /**
     * 设置设备id求md5(32位小写)(手机号与设备号至少一个)     
     * @param String $deviceIdMd5     
     * 参数示例：<pre>xxxxxxx</pre>     
     * 此参数必填     */
    public function setDeviceIdMd5( $deviceIdMd5) {
        $this->sdkStdResult["deviceIdMd5"] = $deviceIdMd5;
    }
    
        
        /**
    * @return 手机号求md5(32位小写)(手机号与设备号至少一个)
    */
        public function getPhoneMd5() {
        $tempResult = $this->sdkStdResult["phoneMd5"];
        return $tempResult;
    }
    
    /**
     * 设置手机号求md5(32位小写)(手机号与设备号至少一个)     
     * @param String $phoneMd5     
     * 参数示例：<pre>xxxxxxxx</pre>     
     * 此参数必填     */
    public function setPhoneMd5( $phoneMd5) {
        $this->sdkStdResult["phoneMd5"] = $phoneMd5;
    }
    
        
        /**
    * @return 行为时间，13位时间戳精确到毫秒
    */
        public function getActionTime() {
        $tempResult = $this->sdkStdResult["actionTime"];
        return $tempResult;
    }
    
    /**
     * 设置行为时间，13位时间戳精确到毫秒     
     * @param Long $actionTime     
     * 参数示例：<pre>1564653506976</pre>     
     * 此参数必填     */
    public function setActionTime( $actionTime) {
        $this->sdkStdResult["actionTime"] = $actionTime;
    }
    
        
        /**
    * @return 行为类型，1688定义枚举值： CLICK(点击商品详情),COMMENT(评论),STORE(收藏),CART(加购物车),SEARCH(关键词搜索，配合actionDetail),曝光(VIEW)
    */
        public function getActionType() {
        $tempResult = $this->sdkStdResult["actionType"];
        return $tempResult;
    }
    
    /**
     * 设置行为类型，1688定义枚举值： CLICK(点击商品详情),COMMENT(评论),STORE(收藏),CART(加购物车),SEARCH(关键词搜索，配合actionDetail),曝光(VIEW)     
     * @param String $actionType     
     * 参数示例：<pre>COMMENT</pre>     
     * 此参数必填     */
    public function setActionType( $actionType) {
        $this->sdkStdResult["actionType"] = $actionType;
    }
    
        
        /**
    * @return 行为类型补充字段，部分类型配合使用，如搜索内容
    */
        public function getActionDetail() {
        $tempResult = $this->sdkStdResult["actionDetail"];
        return $tempResult;
    }
    
    /**
     * 设置行为类型补充字段，部分类型配合使用，如搜索内容     
     * @param String $actionDetail     
     * 参数示例：<pre>女装</pre>     
     * 此参数必填     */
    public function setActionDetail( $actionDetail) {
        $this->sdkStdResult["actionDetail"] = $actionDetail;
    }
    
        
        /**
    * @return 商品类型,1:1688商品;2 机构商品;
    */
        public function getFeedType() {
        $tempResult = $this->sdkStdResult["feedType"];
        return $tempResult;
    }
    
    /**
     * 设置商品类型,1:1688商品;2 机构商品;     
     * @param Integer $feedType     
     * 参数示例：<pre>1</pre>     
     * 此参数必填     */
    public function setFeedType( $feedType) {
        $this->sdkStdResult["feedType"] = $feedType;
    }
    
        
        /**
    * @return 商品id,1688商品必须传商品id
    */
        public function getFeedId() {
        $tempResult = $this->sdkStdResult["feedId"];
        return $tempResult;
    }
    
    /**
     * 设置商品id,1688商品必须传商品id     
     * @param Long $feedId     
     * 参数示例：<pre>12314</pre>     
     * 此参数必填     */
    public function setFeedId( $feedId) {
        $this->sdkStdResult["feedId"] = $feedId;
    }
    
        
        /**
    * @return 商品标题
    */
        public function getFeedTitle() {
        $tempResult = $this->sdkStdResult["feedTitle"];
        return $tempResult;
    }
    
    /**
     * 设置商品标题     
     * @param String $feedTitle     
     * 参数示例：<pre>一件商品</pre>     
     * 此参数必填     */
    public function setFeedTitle( $feedTitle) {
        $this->sdkStdResult["feedTitle"] = $feedTitle;
    }
    
        
        /**
    * @return 售卖价格，单位元，两位小数
    */
        public function getFeedPrice() {
        $tempResult = $this->sdkStdResult["feedPrice"];
        return $tempResult;
    }
    
    /**
     * 设置售卖价格，单位元，两位小数     
     * @param Double $feedPrice     
     * 参数示例：<pre>11.11</pre>     
     * 此参数必填     */
    public function setFeedPrice( $feedPrice) {
        $this->sdkStdResult["feedPrice"] = $feedPrice;
    }
    
        
        /**
    * @return 商品类目，多级类目英文分号分割，按一级类目;二级类目;叶子类目格式传
    */
        public function getFeedCategory() {
        $tempResult = $this->sdkStdResult["feedCategory"];
        return $tempResult;
    }
    
    /**
     * 设置商品类目，多级类目英文分号分割，按一级类目;二级类目;叶子类目格式传     
     * @param String $feedCategory     
     * 参数示例：<pre>类目1;类目2;类目3</pre>     
     * 此参数必填     */
    public function setFeedCategory( $feedCategory) {
        $this->sdkStdResult["feedCategory"] = $feedCategory;
    }
    
        
    private $sdkStdResult=array();
    
    public function getSdkStdResult(){
    	return $this->sdkStdResult;
    }

}
?>