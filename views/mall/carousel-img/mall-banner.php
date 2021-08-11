<?php defined('YII_ENV') or exit('Access Denied');
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-11
 * Time: 15:16
 */
?>
<div id="app" v-cloak>
    <com-banner url="mall/carousel-img/banner" submit_url="mall/carousel-img/mall-banner-edit"></com-banner>
</div>
<script>
const app = new Vue({
    el: '#app'
})
</script>