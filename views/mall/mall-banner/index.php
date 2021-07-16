<?php defined('YII_ENV') or exit('Access Denied');
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-01
 * Time: 21:49
 */

?>
<div id="app" v-cloak>
    <com-banner url="mall/mall-banner/index" submit_url="mall/mall-banner/edit"></com-banner>
</div>
<script>
const app = new Vue({
    el: '#app'
})
</script>