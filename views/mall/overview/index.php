<?php
defined('YII_ENV') or exit('Access Denied');
$urlManager = Yii::$app->urlManager;
Yii::$app->loadComponentView('com-count-up');
Yii::$app->loadComponentView('overvier/com-funnel');
?>
<style>
    .app-main {
        width: 100%;
        /*height: 100%;*/
        /*background-size: cover;*/
        /*background-repeat: no-repeat;*/
    }

    .app-main .data-screen {
        width: 100%;
    }

    .data-screen .title {
        width: 100%;
        height: 86px;
        font-size: 32px;
        line-height: 86px;
        text-align: center;
        font-weight: bold;
        background-size: 100%;
        background-repeat: no-repeat;
        color: #FFFFFF;
    }

    .data-statistics {
        padding: 0 25px 25px;
        color: #FFFFFF;
    }

    .data-statistics .statistic {
        display: flex;
        justify-content: space-between;
        margin-bottom: 25px;
    }

    .data-statistics-title {
        height: 22px;
        font-size: 22px;
        font-weight: 500;
        color: #FFFFFF;
        line-height: 22px;
        text-align: center;
        margin-bottom: 30px;
    }

    .statistic .item .icon {
        width: 62px;
        height: 62px;
        background: #002390;
        border-radius: 50%;
        margin-right: 20px;
        margin-left: 44px;
        font-size: 40px;
        text-align: center;
        line-height: 62px;
    }

    .user-data-statistics .item {
        color: #FFFFFF;
        margin-bottom: 20px;
    }

    .user-data-statistics .item .text {
        font-size: 15px;
        margin-right: 8px;
    }

    .user-data-statistics .item .status {
        width: 63px;
        height: 15px;
        font-size: 10px;
        background: linear-gradient(90deg, #1D5FFF 0%, #03299B 100%);
        border-radius: 7px;
        line-height: 15px;
        text-align: center;
    }

    .user-data-statistics .item .num {
        font-size: 23px;
    }

    .user-data-statistics .item .last-day {
        color: #7D7D7D;
        font-size: 12px;
    }

    .goods-data-statistics .items {
        padding: 0 13px;
        color: #FFFFFF;
    }

    .goods-data-statistics .items .table-head {
        color: #FFFFFF;
        font-size: 18px;
        font-weight: 500;
    }
    .goods-data-statistics .items .scroll_box {
        overflow-y: auto;
        overflow-x:auto;
        height: 380px;
    }

    .goods-data-statistics .item {
        height: 65px;
        border-bottom: 1px solid #152B79;
    }

    .goods-data-statistics .item:last-child,
    .app-message .notice .item:last-child {
        border-bottom: 0;
    }

    .goods-data-statistics .item .goods-id {
        /*width: 68px;*/
        width: 38px;
        margin: 0 20px 0 12px;
        line-height: 65px;
        text-align: center;
        font-size: 18px;
    }

    .goods-data-statistics .item .goods-img {
        width: 37px;
        height: 37px;
        margin-right: 10px;
    }

    .goods-data-statistics .item .goods-name {
        font-size: 12px;
        line-height: 15px;
        max-width: 113px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        margin-right: 35px;
    }

    .goods-data-statistics .item .goods-num {
        font-size: 18px;
        line-height: 20px;
        font-weight: 500;
        text-align: center;
    }


    .map-transaction {
        padding: 30px 20px 0;
        max-width: 646px;
    }

    .map-transaction .num-text {
        font-size: 21px;
    }

    .map-transaction-sum {
        width: 100%;
        margin-bottom: 15px;
    }

    .map-transaction-item-num {
        font-size: 35px;
        line-height: 35px;
        margin-bottom: 15px;
    }

    .map-transaction-item-text {
        font-size: 16px;
    }

    .app-main .app-message {
        width: 315px;
        padding: 68px 0 45px;
    }

    .app-message .container {
        height: 100%;
        width: 100%;
        padding: 10px 25px;
        border-left: 1px solid #253986;
        color: #FFFFFF;
    }

    .app-message .phone {
        /*width:261px;*/
        height: 60px;
        border: 2px solid #14276E;
    }

    .app-message .notice {
        padding: 20px 14px;
        margin-bottom: 14px;
    }

    .app-message .notice .item {
        font-size: 15px;
        line-height: 24px;
        font-weight: 500;
        padding: 25px 0;
        border-bottom: 1px solid #162B7A;
    }

    .app-message .product-notice .item {
        border-bottom: 0;
        padding: 0;
    }

    .system-notice .item .text {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .notice .data-statistics-title {
        margin-bottom: 0;
        display: flex;
        align-items: center;
    }

    .notice .data-statistics-title>span {
        margin: 0 10px;
    }

    .notice .data-statistics-title .here {
        color: #22A3E6;
        font-size: 13px;
        line-height: 13px;
    }

    .notice .data-statistics-title .iconfont {
        font-size: 30px;
    }

    .notice .items {
        overflow-y: auto;
        overflow-x: auto;
        height: 200px;
    }

    .phone>span {
        font-size: 20px;
        font-weight: 400;
    }

    .regards {
        font-size: 12px;
    }

    .regards .text {
        max-width: 128px;
        margin-bottom: 15px;
        line-height: 21px;
    }

    .regards .btn {
        padding: 5px 0;
        border: 1px solid rgb(255, 255, 255);
    }

    .box-1 {
        padding: 20px 0;
        width: 357px;
        height: 495px;
        background-image: url("statics/img/mall/data-screen/box-1.png");
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .box-2 {
        width: 357px;
        height: 306px;
        background-image: url("statics/img/mall/data-screen/box-2.png");
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .box-3 {
        width: 642px;
        height: 306px;
        background-image: url("statics/img/mall/data-screen/box-3.png");
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .box-4 {
        width: 261px;
        height: 249px;
        background-image: url("statics/img/mall/data-screen/box-4.png");
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .goods_box {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex: 1;
    }

    .goods_box_c {
        display: flex;
        align-items: center;
    }
    .text-status{
        align-items: center;
    }
    .level_scrool_box{
        overflow-y: auto;
        overflow-x: auto;
        height: 420px;
    }
    .item_a{
        color: #FFFFFF;
        text-decoration:none;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding:0;">
        <div class="app-main" flex style="background-image: url('statics/img/mall/data-screen/data-bg.jpg')">
            <div class="data-screen">
                <div class="title" style="background-image: url('statics/img/mall/data-screen/data-title.png')">
                    <span>大数据平台</span>
                </div>
                <div class="data-statistics">
                    <div class="statistic up user-map-goods">
                        <div class="user-data-statistics box-1">
                            <div class="data-statistics-title">用户数据统计</div>
                            <div class="items">
                            <div class="level_scrool_box">
                                <div class="item"  flex="main:center">
                                    <div class="icon iconfont iconfangkeshu"></div>
                                    <!-- 访客量 -->
                                    <div class="centent" flex="dir:top main:center" flex-box="1">
                                        <div class="text-status" flex>
                                            <div class="text">访客量</div>
                                            <!-- <div class="status">{{item.type | type_status}}{{item.percent}}</div> -->
                                            <!-- v-if="detail_data.visitor_proportion != 0" -->
                                            <div style="display: flex;margin-top:4px;" v-if="detail_data.visitor_proportion != 0">
                                                <div class="status">{{detail_data.visitor_proportion | type_status}}{{detail_data.visitor_proportion}}%</div>
                                                <i class="text-status-icon iconfont" :class="detail_data.visitor_proportion>0 ? 'iconshangsheng' : 'iconxiajiang'" :style="detail_data.visitor_proportion<0 ? `color:#DC0113` : `color:#37A86D`"></i>
                                            </div>
                                        </div>
                                        <div class="num">{{detail_data.today_visitor_num}}</div>
                                        <div class="last-day">昨日全天: {{detail_data.yesterday_visitor_num}}</div>
                                    </div>
                                </div>
                                <!-- 浏览量 -->
                                <div class="item"  flex="main:center">
                                    <div class="icon iconfont iconliulanliang"></div>
                                    <div class="centent" flex="dir:top main:center" flex-box="1">
                                        <div class="text-status" flex>
                                            <div class="text">浏览量</div>
                                            <div style="display: flex;margin-top:4px;" v-if="detail_data.browse_proportion != 0">
                                                <div class="status">{{detail_data.browse_proportion | type_status}}{{detail_data.browse_proportion}}%</div>
                                                <i class="text-status-icon iconfont" :class="detail_data.browse_proportion>0 ? 'iconshangsheng' : 'iconxiajiang'" :style="detail_data.browse_proportion<0 ? `color:#DC0113` : `color:#37A86D`"></i>
                                            </div>
                                        </div>
                                        <div class="num">{{detail_data.today_browse_num}}</div>
                                        <div class="last-day">昨日全天: {{detail_data.yesterday_browse_num}}</div>
                                    </div>
                                </div>
                                <!-- 等级遍历 -->
                                    <div class="item" v-for="(item,i) in detail_data.level_list" :key="i" flex="main:center">
                                        <div class="icon iconfont iconliulanliang"></div>
                                        <div class="centent" flex="dir:top main:center" flex-box="1">
                                            <div class="text-status" flex>
                                                <div class="text">{{item.name}}</div>
                                                <div style="display: flex;margin-top:4px;" v-if="item.proportion != 0">
                                                    <div class="status">{{item.proportion | type_status}}{{item.proportion}}%</div>
                                                    <i class="text-status-icon iconfont" :class="item.proportion > 0 ? 'iconshangsheng' : 'iconxiajiang'" :style="item.proportion < 0 ? `color:#DC0113` : `color:#37A86D`"></i>
                                                </div>
                                            </div>
                                            <div class="num">{{item.today_num}}</div>
                                            <div class="last-day">昨日全天: {{item.yesterday_num}}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="item">
                                    <div class="icon"></div>
                                    <div class="centent" flex="dir:top main:center" flex-box="1">
                                        <div class="text-status" flex>
                                            <div class="text">{{item.name}}</div>
                                            <div class="status">{{item.type | type_status}}{{item.percent}}</div>
                                            <i class="text-status-icon iconfont" :class="item.type == 1 ? 'iconshangsheng' : 'iconxiajiang'" :style="item.type == 1 ? `color:#DC0113` : `color:#37A86D`"></i>
                                        </div>
                                        <div class="num">{{item.num}}</div>
                                        <div class="last-day">昨日全天: {{item.last_day_num}}</div>
                                    </div>
                                </div> -->
                            </div>
                        </div>

                        <div class="map-transaction" flex-box="1" flex="dir:top cross:center">
                            <div class="map-transaction-num" flex="cross:center" style="margin-bottom: 45px;">
                                <div class="num-text">总交易额：</div>
                                <div v-if="detail_data">
                                    <com-count-up :count="detail_data.total_transactions" :end_datetime="endTime(detail_data.update_time)"></com-count-up>
                                </div>
                            </div>
                            <div class="map-transaction-sum" flex="main:justify">
                                <div class="map-transaction-item" v-for="(item,i) in map_transaction_sum_data" :key="i" flex="dir:top cross:center">
                                    <div class="map-transaction-item-num">{{item.num}}</div>
                                    <div class="map-transaction-item-text">{{item.text}}</div>
                                </div>
                            </div>
                            <div id="map-echarts" style="width:100%;height:343px;"></div>
                        </div>

                        <div class="goods-data-statistics box-1">
                            <div class="data-statistics-title">商品热销排行榜</div>
                            <div class="items">
                                <div class="table-head" flex>
                                    <div class="label" flex-box="0" style="margin: 0 20px 0 12px;">排行</div>
                                    <div class="label" flex-box="1" style="flex-basis: 165px">商品名称</div>
                                    <div class="label" flex-box="0" style="margin: 0 12px 0 20px;">销量</div>
                                </div>
                                <div class="scroll_box">
                                    <div class="item" flex="cross:center" v-for="(item,i) in detail_data.goods_list" :key="i">
                                        <div class="goods-id">{{i + 1}}</div>
                                        <div class="goods_box">
                                            <div class="goods_box_c">
                                                <img class="goods-img" :src="item.cover_pic">
                                                <div class="goods-name">{{item.name}}</div>
                                            </div>
                                            <div class="goods-num">{{item.virtual_sales}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="statistic down user-order">
                        <div class="total-data box-2" style="padding: 20px 10px">
                            <div class="data-statistics-title">转化率统计</div>
                            <!--                            <div id="total-funnel-echarts" style="width: 100%;height:207px;"></div>-->
                            <com-funnel :data-list="funnel_total_list"></com-funnel>
                        </div>
                        <div class="user-data box-3" style="padding: 20px 0" flex>
                            <div class="user-buy" flex-box="1" style="max-width: 49%">
                                <div class="data-statistics-title">用户购买力</div>
                                <div id="user-buy-echarts" style="height: 220px;"></div>
                            </div>
                            <div class="user-source" flex-box="1" style="max-width: 49%">
                                <div class="data-statistics-title">用户来源</div>
                                <div id="user-source-echarts" style="height: 220px;"></div>
                            </div>
                        </div>
                        <div class="order-data box-2" style="padding: 20px 10px">
                            <div class="data-statistics-title">下单统计</div>
                            <!--                            <div id="order-funnel-echarts" style="width: 100%;height:207px;"></div>-->
                            <com-funnel :data-list="funnel_order_list"></com-funnel>
                        </div>
                    </div>
                </div>
            </div>
            <div class="app-message">
                <div class="container" flex="dir:top">
                    <div class="notice system-notice box-4">
                        <div class="data-statistics-title">
                            <i class="iconfont iconlaba"></i>
                            <span>系统通知</span>
                        </div>
                        <div class="items">
                            <div class="item" v-for="(item,i) in detail_data.notice">
                                <div class="text" :title="item.content">{{item.content}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="notice product-notice box-4">
                        <div class="data-statistics-title" style="align-items: baseline;margin-bottom: 25px">
                            <span>产品动态</span>
                            <a class="here" href="http://bbs.gdqijianshi.com/" style="margin: 0;text-decoration:none;">更多></a>
                        </div>
                        <div class="items">
                            <a href="http://bbs.gdqijianshi.com/" class="item item_a" v-for="(item,i) in detail_data.forum">{{fomentDate(item.dateline)}} {{item.title}}</a>
                            </div>
                        </div>
                    <div flex-box="1"></div>
                    <div v-if="is_services_show" class="phone" flex="main:center cross:center" style="margin-bottom: 15px">
                        <img class="icon" src="statics/img/mall/data-screen/msg.png" style="width: 30px;height: 28px;margin-right: 10px">
                        <span v-if="detail_data">{{detail_data.services[1].value}}</span>
                    </div>
                    <!--<div v-if="is_services_show" class="customer-service" flex="main:center cross:center" style="background-image: url('statics/img/mall/data-screen/data-kefu.png');background-size: 100%;height: 117px;padding: 0 14px">
                        <div class="div" style="margin-right: 10px;height: 83px;width: 83px"></div>
                        <div class="regards">
                            <div class="text">Hi!我是苏珊，请问您遇到了什么问题了吗？</div>
                            <div class="btn" flex="main:center cross:center">
                                <img class="icon" src="statics/img/mall/data-screen/msg.png" style="width: 18px;height: 16px;margin-right: 6px">
                                <span><a :href="services_url" target="_blank" style="color: #fff;text-decoration: none;">立即联系</a></span>
                            </div>
                        </div>
                    </div>-->
                </div>
            </div>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                services_url:'',
                // 加载动画
                loading: false,
                end_datetime: '',
                map_transaction_sum_data: [],
                detail_data: "",
                map_data_list:[],
                // 蛛网一
                spider_arr:[],
                spider_indicator:[],
                spider_max:[],
                // 蛛网二
                spider_arr2:[],
                spider_indicator2:[],
                spider_max2:[],

                is_services_show:false,

                funnel_total_list: [{
                    text: '浏览量',
                    num: 0
                }, {
                    text: '访客量',
                    num: 0
                }, {
                    text: '关注',
                    num: 0
                }],
                funnel_order_list: [{
                    text: '访问量',
                    num: 0
                }, {
                    text: '下单',
                    num: 0
                }, {
                    text: '支付',
                    num: 0
                }]
            };
        },
        mounted() {
            this.getData();
        },
        computed:{
            endTime(){
                return function(time){
                    let date = new Date(time*1000);
                    let Y = date.getFullYear();
                    let m = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
                    let d = (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate());
                    let H = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours());
                    let i = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes());
                    let s = (date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds());
                    return `${Y}-${m}-${d} ${H}:${i}:${s}`;
                }
            },
            fomentDate(){
                return function(time){
                    let date = new Date(time*1000);
                    let m = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
                    let d = (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate());
                    return `${m}-${d}`;
            }
            }
        },
        methods: {
            getData() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/overview/index',
                    },
                    method: 'post',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.detail_data = e.data.data;
                        if(this.detail_data.services.length != 0){
                            this.services_url = decodeURIComponent(this.detail_data.services[0].value);
                            this.is_services_show = true;
                        }
                        
                        // 转化率统计数据
                        this.funnel_total_list[0].num = this.detail_data.conversion_browse_num;
                        this.funnel_total_list[1].num = this.detail_data.conversion_visitor_num;
                        this.funnel_total_list[2].num = this.detail_data.follow_num;
                        // 下单统计数据
                        this.funnel_order_list[0].num = this.detail_data.order_visit_num;
                        this.funnel_order_list[1].num = this.detail_data.order_num;
                        this.funnel_order_list[2].num = this.detail_data.pay_num;
                        // 顶部数据，交易金额下面
                        this.map_transaction_sum_data = [{
                            num: this.detail_data.today_earnings,
                            text: '今日收益'
                        }, {
                            num: this.detail_data.add_user,
                            text: '新增用户'
                        }, {
                            num: this.detail_data.user_sum,
                            text: '总用户数量'
                        }];
                        // 地图数据
                        this.detail_data.province_list.forEach(item => {
                            delete item.id;
                            delete item.level;
                            delete item.parent_id;
                            item.value = item.num;
                            delete item.num;
                            item.name = item.name.split('省')[0];
                            item.name = item.name.split('市')[0];
                            item.name = item.name.split('特别行政区')[0];
                            item.name = item.name.split('壮族自治区')[0];
                            item.name = item.name.split('回族自治区')[0];
                            item.name = item.name.split('维吾尔自治区')[0];
                            item.name = item.name.split('自治区')[0];
                            this.map_data_list.push(item);
                        })
                        this.map_data_list.push({
                            name: "南海诸岛",
                            value: 0,
                            itemStyle: {
                                normal: {
                                    opacity: 0,
                                    label: {
                                        show: false
                                    }
                                }
                            }
                        })
                        // 蜘蛛网1
                        this.detail_data.purchasing_power.forEach(item=>{
                            this.spider_arr.push(item.num);
                        })
                        this.spider_max = Math.max.apply(null,this.spider_arr) ? Math.max.apply(null,this.spider_arr) : 1;//取最大值
                        this.detail_data.purchasing_power.forEach(item=>{
                            this.spider_indicator.push({
                                'text':item.name,
                                'max':this.spider_max
                            })
                        })

                        // 蜘蛛网2
                        this.detail_data.user_source.forEach(item=>{
                            this.spider_arr2.push(item.num);
                        })
                        this.spider_max2 = Math.max.apply(null,this.spider_arr2) ? Math.max.apply(null,this.spider_arr2) : 1;//取最大值
                        this.detail_data.user_source.forEach(item=>{
                            this.spider_indicator2.push({
                                'text':item.name,
                                'max':this.spider_max2
                            })
                        })

                        this.form();
                    }
                    this.load = true;
                })
            },
            // 生成图表
            form(data) {
                let that = this;
                // 地图
                var mapChart = echarts.init(document.getElementById('map-echarts'));
                mapChart.setOption({
                    tooltip: {
                        trigger: 'item',
                        formatter: '{b}: {c}',
                    },
                    visualMap: {
                        mix: 0,
                        max: 500,
                        inRange: {
                            color: ['#1e60ff', '#0540c3', '#0032c1', '#002390']
                        },
                        textStyle: {
                            color: '#ffffff'
                        }
                    },
                    series: [{
                        type: 'map',
                        mapType: 'china',
                        roam: false,
                        label: {
                            normal: {
                                show: false,
                            },
                            emphasis: {
                                show: true,
                                textStyle: {
                                    color: 'rgb(249, 249, 249)'
                                }
                            }
                        },
                        data: that.map_data_list
                    }]
                });
                // 蜘蛛网
                var userBuyEcharts = echarts.init(document.getElementById('user-buy-echarts'));
                var userSourceEcharts = echarts.init(document.getElementById('user-source-echarts'));
                let option = {
                    tooltip: {
                        trigger: 'axis'
                    },
                    radar: [{
                        name: {
                            show: true, // 是否显示工艺等文字
                            formatter: null, // 工艺等文字的显示形式
                            textStyle: {
                                color: '#ffffff' // 工艺等文字颜色
                            }
                        },
                        indicator: this.spider_indicator2,
                        // center: ['25%', '40%'],
                        radius: 80,
                        splitArea: {
                            show: true,
                            areaStyle: {
                                color: ["transparent"] // 图表背景网格的颜色
                            }
                        },
                    }],
                    series: [{
                        type: 'radar',
                        symbol: "none", // 取消拐点
                        itemStyle: {
                            normal: {
                                color: "rgba(0,0,0,0)",
                                lineStyle: {
                                    color: "#1E60FF" // 图表中各个图区域的边框线颜色
                                },
                            }
                        },
                        tooltip: {
                            trigger: 'item'
                        },
                        data: [{
                            value: this.spider_arr2,
                            name: '用户来源',
                            itemStyle: {
                                normal: {
                                    areaStyle: {
                                        type: 'default',
                                        opacity: 1, // 图表中各个图区域的透明度
                                        color: "#1E60FF" // 图表中各个图区域的颜色
                                    }
                                }
                            },
                        }]
                    }]
                };
                // let option_copy = JSON.parse(JSON.stringify(option));
                let option_copy = {
                    tooltip: {
                        trigger: 'axis'
                    },
                    radar: [{
                        name: {
                            show: true,
                            textStyle: {
                                color: '#ffffff'
                            }
                        },
                        indicator: this.spider_indicator,
                        radius: 80,
                        splitArea: {
                            show: true,
                            areaStyle: {
                                color: ["transparent"] // 图表背景网格的颜色
                            }
                        },
                    }],
                    series: [{
                        type: 'radar',
                        symbol: "none",
                        tooltip: {
                            trigger: 'item'
                        },
                        itemStyle: {
                            normal: {
                                color: "rgba(0,0,0,0)",
                                lineStyle: {
                                    color: "#1E60FF"
                                },
                            }
                        },
                        data: [{
                            value: this.spider_arr,
                            name: '用户购买力',
                            itemStyle: {
                                normal: {
                                    areaStyle: {
                                        type: 'default',
                                        opacity: 1,
                                        color: '#1E60FF'
                                    }
                                }
                            }
                        }]
                    }]
                };
                userBuyEcharts.setOption(option_copy);
                userSourceEcharts.setOption(option);
            },
        },
        filters: {
            type_status(value) {
                return value > 0 ? '上升' : '下降';
            }
        }
    })
</script>