<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-10
 * Time: 12:36
 */

defined('YII_ENV') or exit('Access Denied');
$urlManager = Yii::$app->urlManager;
Yii::$app->loadComponentView('statistics/com-search');
?>
<style>
    .el-tabs__nav-wrap::after {
        height: 1px;
    }

    .table-body {
        background-color: #fff;
        position: relative;
        padding-bottom: 50px;
        margin-bottom: 10px;
        border: 1px solid #EBEEF5;
    }

    .table-body .el-tabs {
        margin-left: 10px;
    }

    .table-body .el-tabs__nav-scroll {
        width: 120px;
        margin-left: 30px;
    }

    .table-body .el-tabs__item {
        height: 32px;
        line-height: 32px;
    }

    .table-body .clean {
        color: #92959B;
        margin-left: 20px;
        cursor: pointer;
        font-size: 15px;
    }

    .num-info {
        display: flex;
        width: 100%;
        height: 60px;
        font-size: 28px;
        color: #303133;
    }
    .num-info .num-info-item {
        text-align: center;
        width: 20%;
        border-left: 1px dashed #EFF1F7;
    }
    .num-info .num-info-item:first-of-type {
        border-left: 0;
    }
    .info-item-name {
        font-size: 16px;
        color: #92959B;
    }

    .echarts-title {
        color: #92959B;
        display: flex;
        font-size: 16px;
        margin-left: 45px;
    }

    .echarts-title-item {
        margin-right: 45px;
        display: flex;
        align-items: center;
    }

    .echarts-title-item .echarts-title-icon {
        height: 16px;
        width: 16px;
        margin-right: 10px;
        background-color: #3399ff;
    }

    .table-area {
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
    }

    .table-area .el-card {
        width: 49.5%;
        color: #303133;
    }

    .el-tabs__header {
        margin-bottom: 0 !important;
    }

    .el-card__header {
        position: relative;
    }

    .sort-active {
        color: #3399ff;
    }

    .select-item {
        border: 1px solid #3399ff;
        margin-top: -1px !important;
    }

    .el-popper .popper__arrow, .el-popper .popper__arrow::after {
        display: none;
    }

    .el-select-dropdown__item.hover, .el-select-dropdown__item:hover {
        background-color: #3399ff;
        color: #fff;
    }

    .table-area .el-card__header {
        padding: 14px 20px;
    }

    .text-omit {
        width: 380px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .data-panels-item {
        padding: 20px;
        margin-right: 30px;
        max-width: 422px;
        height: 180px;
        border-radius: 10px;
        border: 1px solid #26C9FF;
    }

    .data-panels-item:last-child {
        margin-right: 0;
    }

    .data-panels-item .data-panels-name {
        color: #313131;
        font-size: 18px;
        line-height: 36px;
    }

    .data-panels-item .data-panels-num {
        color: #26C9FF;
        font-size: 43px;
        font-weight: bold;
        line-height: 36px;
        padding: 18px 0;
        border-bottom: 2px solid #F1F1F1;
    }

    .data-panels-compare {
        font-size: 15px;
        line-height: 50px;
    }

    .data-panels-compare .num-text {
        display: flex;
        align-items: center;
    }

    .core-itme, .goods-itme {
        line-height: 30px;
    }

    .core-itme .core-itme-name, .goods-itme .goods-itme-name {
        font-size: 15px;
        color: #6E7070;
    }

    .core-itme .core-itme-num, .goods-itme .goods-itme-num {
        font-size: 20px;
        color: #6E7070;
    }

    .num-text .text {
        color: #9C9C9C;
    }

    .num-text .num {
        color: #4BC282;
    }

    .num-text .num.up {
        color: #FF7161;
    }

    .num-text .icon {
        font-size: 24px;
        color: #45BE89;
    }

    .num-text .icon.up {
        color: #FF8585;
    }

    .pay-goods-data-panel .item{
        font-size: 15px;
        line-height: 25px;
        margin-bottom: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height:83px;
        background-size: 100%;
        background-repeat: no-repeat;
    }

    .el-table td, .el-table th{
        padding: 8px 0;
    }

</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>数据概况</span>
        </div>
        <div class="table-body">
            <com-search
                    @to-search="toSearch"
                    @search="searchList"
                    :new-search="search"
                    :is-show-keyword="false"
                    :day-data="{'today':today, 'weekDay': weekDay, 'monthDay': monthDay}">
                <template slot="select">
                    <div>
                        <el-select size="small" popper-class="select-item" @change="tabMch" style="width: 160px"
                                   filterable
                                   v-model="search.mch" placeholder="请输入搜索内容">
                            <el-option label="全部" value="0"></el-option>
                            <el-option v-for="item in mch_list" :key="item.id" :label="item.name"
                                       :value="item.id"></el-option>
                        </el-select>
                    </div>
                </template>
            </com-search>
            <!-- 流量来源数据 -->
            <div class="num-info">
                <div class="num-info-item">
                    <div>{{all_data.user_count}}</div>
                    <div class="info-item-name">
                        <span>用户数</span>
                        <el-tooltip class="item" effect="dark" content="统计全平台用户数，不随店铺的更改而更改" placement="bottom">
                            <i class="el-icon-question"></i>
                        </el-tooltip>
                    </div>
                </div>
                <div class="num-info-item">
                    <div>{{all_data.goods_num}}</div>
                    <div class="info-item-name">商品数
                        <el-tooltip class="item" effect="dark" content="统计某时间段内添加的商品总数" placement="bottom">
                            <i class="el-icon-question"></i>
                        </el-tooltip>
                    </div>
                </div>
                <div class="num-info-item">
                    <div>{{all_data.order_num}}</div>
                    <div class="info-item-name">订单数</div>
                </div>
                <div class="num-info-item">
                    <div>{{all_data.wait_send_num}}</div>
                    <div class="info-item-name">待发货订单数</div>
                </div>
                <div class="num-info-item">
                    <div>{{all_data.pro_order}}</div>
                    <div class="info-item-name">维权订单数</div>
                </div>
            </div>
        </div>

        <el-card shadow="never" style="margin-bottom: 10px;">
            <div slot="header">
                <span>流量来源数据</span>
                <div style="float: right">
                    <!--                    <span style="color: #959595">{{temp_date | dateTimeFormat('Y-m-d H:i:s')}}</span>-->
                </div>
            </div>
            <div class="data-panels" flex="main:center">
                <div class="data-panels-item" flex-box="1" flex="dir:top main:center" v-for="(item,i) in flow_data">
                    <div class="data-panels-name">{{item.name}}</div>
                    <div class="data-panels-num">{{item.num}}</div>
                    <div class="data-panels-compare" flex="cross:center">
                        <div class="last_day num-text" flex-box="1">
                            <div class="text">较昨日</div>
                            <div class="icon"
                                 :class="item.last_day.type == 0 ? 'el-icon-caret-bottom' : 'el-icon-caret-top up'"></div>
                            <div class="num" :class="item.last_day.type == 0 ? '' : 'up'">{{item.last_day.num}}</div>
                        </div>
                        <div class="last_week num-text" flex-box="1">
                            <div class="text">较七日</div>
                            <div class="icon"
                                 :class="item.last_week.type == 0 ? 'el-icon-caret-bottom' : 'el-icon-caret-top up'"></div>
                            <div class="num" :class="item.last_week.type == 0 ? '' : 'up'">{{item.last_week.num}}</div>
                        </div>
                        <div class="last_month num-text" flex-box="1">
                            <div class="text">较上月</div>
                            <div class="icon"
                                 :class="item.last_month.type == 0 ? 'el-icon-caret-bottom' : 'el-icon-caret-top up'"></div>
                            <div class="num" :class="item.last_month.type == 0 ? '' : 'up'">{{item.last_month.num}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </el-card>

        <el-card shadow="never" style="margin-bottom: 10px;">
            <div slot="header">
                <span>核心数据指标</span>
            </div>
            <div class="core-echarts-title">
                <div class="core-items" flex style="width: 100%">
                    <div class="core-itme" flex="dir:top cross:center" flex-box="1" v-for="(item,i) in core_data_list"
                         :key="i">
                        <div class="core-itme-name">{{item.name}}</div>
                        <div class="core-itme-num">{{item.num}}</div>
                        <div class="last_day num-text" flex="cross:center">
                            <div class="text">较昨日</div>
                            <div class="icon"
                                 :class="item.last_day.type == 0 ? 'el-icon-caret-bottom' : 'el-icon-caret-top up'"></div>
                            <div class="num" :class="item.last_day.type == 0 ? '' : 'up'">{{item.last_day.num}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="core-data-echarts" style="width:100%;height:18rem;"></div>
        </el-card>

        <el-card shadow="never" style="margin-bottom: 10px;">
            <div slot="header">
                <span>流量转化指标</span>
                <div style="float: right">
                    <!--                    <span style="color: #959595">{{temp_date | dateTimeFormat('Y-m-d H:i:s')}}</span>-->
                </div>
            </div>
            <div class="data-panels" flex="main:center">
                <div class="data-panels-item" flex-box="1" flex="dir:top main:center" v-for="(item,i) in core_data">
                    <div class="data-panels-name">{{item.name}}</div>
                    <div class="data-panels-num">{{item.num}}</div>
                    <div class="data-panels-compare" flex="cross:center">
                        <div class="last_day num-text" flex-box="1">
                            <div class="text">较昨日</div>
                            <div class="icon"
                                 :class="item.last_day.type == 0 ? 'el-icon-caret-bottom' : 'el-icon-caret-top up'"></div>
                            <div class="num" :class="item.last_day.type == 0 ? '' : 'up'">{{item.last_day.num}}</div>
                        </div>
                        <div class="last_week num-text" flex-box="1">
                            <div class="text">较七日</div>
                            <div class="icon"
                                 :class="item.last_week.type == 0 ? 'el-icon-caret-bottom' : 'el-icon-caret-top up'"></div>
                            <div class="num" :class="item.last_week.type == 0 ? '' : 'up'">{{item.last_week.num}}</div>
                        </div>
                        <div class="last_month num-text" flex-box="1">
                            <div class="text">较上月</div>
                            <div class="icon"
                                 :class="item.last_month.type == 0 ? 'el-icon-caret-bottom' : 'el-icon-caret-top up'"></div>
                            <div class="num" :class="item.last_month.type == 0 ? '' : 'up'">{{item.last_month.num}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="flow-conversion-echarts" style="width:100%;height:18rem;"></div>
        </el-card>

        <el-card shadow="never" style="margin-bottom: 10px;">
            <div slot="header">
                <span>商品数据看板</span>
            </div>
            <div class="goods-echarts-title" style="margin-bottom: 20px">
                <div class="goods-items" flex style="width: 100%">
                    <div class="goods-itme" flex="dir:top cross:center" flex-box="1" v-for="(item,i) in goods_data_list"
                         :key="i">
                        <div class="goods-itme-name">{{item.name}}</div>
                        <div class="goods-itme-num">{{item.num}}</div>
                        <div class="last_day num-text" flex="cross:center">
                            <div class="text">较昨日</div>
                            <div class="icon"
                                 :class="item.last_day.type == 0 ? 'el-icon-caret-bottom' : 'el-icon-caret-top up'"></div>
                            <div class="num" :class="item.last_day.type == 0 ? '' : 'up'">{{item.last_day.num}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <!--            <div id="core-data-echarts" style="width:100%;height:18rem;"></div>-->
            <div class="pay-goods-data-panel" flex="main:justify">
                <el-card shadow="never" style="width: 49.5%">
                    <div slot="header">
                        <span>访问支付转化</span>
                    </div>
                    <div class="funnel"flex="dir:top cross:center">
                        <div class="item" style="background-image: url('statics/img/mall/data-screen/panel-1.png');width:257px;">
                            <div>访问人数</div>
                            <div>2354</div>
                        </div>
                        <div class="item" style="background-image: url('statics/img/mall/data-screen/panel-2.png');width:204px;">
                            <div>下单人数</div>
                            <div>335</div>
                        </div>
                        <div class="item" style="background-image: url('statics/img/mall/data-screen/panel-3.png');width:149px;">
                            <div>支付人数</div>
                            <div>125</div>
                        </div>
                    </div>
                </el-card>
                <el-card shadow="never" style="width: 49.5%">
                    <div slot="header">
                        <span>TOP5商品热销榜单</span>
                    </div>
                    <el-table
                            :data="goods_top_list"
                            :header-cell-style="{color: '#313131'}"
                            style="width: 100%;color: #313131">
                        <el-table-column prop="ranking" label="排名" width="60">
                            <template slot-scope="scope">
                                <span>TOP{{scope.row.ranking}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="goods_name" label="商品" width="345">
                            <template slot-scope="scope">
                                <div flex>
                                    <com-image style="margin-right: 10px;float: left;" :src="scope.row.pic_url" width="28px"
                                               height="28px">
                                    </com-image>
                                    <span class="text-omit"
                                          style="height: 32px;line-height: 32px;display: inline-block;width: 245px">{{scope.row.goods_name}}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="visitor" label="访客数" cell-class-name="visitor-column">
                        </el-table-column>
                        <el-table-column prop="amount" label="支付金额">
                            <template slot-scope="scope">
                                <div style="text-align: center;">¥{{scope.row.visitor}}</div>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-card>
            </div>
        </el-card>

        <el-card shadow="never" style="margin-bottom: 10px;">
            <div slot="header">
                <span>访客画像</span>
                <div style="float: right">
                    <!--                      <span style="color: #959595">{{temp_date | dateTimeFormat('Y-m-d H:i:s')}}</span>-->
                </div>
            </div>
            <div class="proportion-charts" flex="main:justify" style="margin-bottom: 20px">

                <el-card shadow="never" style="width: 49.5%">
                    <div slot="header">
                        <span>性别比例</span>
                    </div>
                    <div id="sex-proportion-echarts" style="width: 100%;height: 225px;"></div>
                </el-card>

                <el-card shadow="never" style="width: 49.5%">
                    <div slot="header">
                        <span>会员粉丝比例</span>
                    </div>
                    <div id="member-proportion-echarts" style="width: 100%;height: 225px;"></div>
                </el-card>

            </div>
            <el-card shadow="never">
                <div slot="header">
                    <span>客户注册区域分布</span>
                </div>
                <div id="map-echarts" style="width: 100%;height: 487px"></div>
            </el-card>

        </el-card>

    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                // 今天
                today: '',
                // 七天前
                weekDay: '',
                // 30天前
                monthDay: '',
                // 加载动画
                loading: false,
                // 搜索内容
                search: {
                    mch: null,
                    time: null,
                    date_start: null,
                    date_end: null,
                    platform: '',
                },
                // 总体数据
                all_data: {
                    goods_num: "0",
                    order_num: "0",
                    pro_order: "0",
                    user_count: "0",
                    wait_send_num: "0"
                },
                // 店铺列表
                mch_list: [],
                temp_date: new Date().getTime(),
                flow_data: [
                    {
                        name: '全部访客数',
                        num: '661,311',
                        last_day: {
                            type: 1,
                            num: '200'
                        },
                        last_week: {
                            type: 0,
                            num: '23'
                        },
                        last_month: {
                            type: 1,
                            num: '500'
                        }
                    },
                    {
                        name: '访客浏览量',
                        num: '54,641,146',
                        last_day: {
                            type: 1,
                            num: '200'
                        },
                        last_week: {
                            type: 0,
                            num: '23'
                        },
                        last_month: {
                            type: 1,
                            num: '500'
                        }
                    },
                    {
                        name: '已支付金额',
                        num: '6,687,164.57',
                        last_day: {
                            type: 1,
                            num: '200'
                        },
                        last_week: {
                            type: 0,
                            num: '23'
                        },
                        last_month: {
                            type: 1,
                            num: '500'
                        }
                    }
                ],
                core_data: [
                    {
                        name: '平均停留时长',
                        num: '53s',
                        last_day: {
                            type: 1,
                            num: '20%'
                        },
                        last_week: {
                            type: 0,
                            num: '2%'
                        },
                        last_month: {
                            type: 1,
                            num: '5%'
                        }
                    },
                    {
                        name: '商品访问转化率',
                        num: '20.56%',
                        last_day: {
                            type: 1,
                            num: '20%'
                        },
                        last_week: {
                            type: 0,
                            num: '2%'
                        },
                        last_month: {
                            type: 1,
                            num: '5%'
                        }
                    },
                    {
                        name: '商品跳失率',
                        num: '5.56%',
                        last_day: {
                            type: 1,
                            num: '20%'
                        },
                        last_week: {
                            type: 0,
                            num: '2%'
                        },
                        last_month: {
                            type: 1,
                            num: '5%'
                        }
                    }
                ],
                core_data_list: [
                    {
                        name: '今日浏览量',
                        num: '25,465',
                        last_day: {
                            type: 1,
                            num: '30%'
                        }
                    },
                    {
                        name: '今日访客量',
                        num: '452,489',
                        last_day: {
                            type: 1,
                            num: '30%'
                        }
                    },
                    {
                        name: '支付人数',
                        num: '45,621',
                        last_day: {
                            type: 1,
                            num: '30%'
                        }
                    },
                    {
                        name: '支付金额',
                        num: '54,621',
                        last_day: {
                            type: 1,
                            num: '30%'
                        }
                    },
                    {
                        name: '待发货订单',
                        num: '456',
                        last_day: {
                            type: 0,
                            num: '23.1%'
                        }
                    },
                    {
                        name: '待处理退款订单',
                        num: '45',
                        last_day: {
                            type: 0,
                            num: '23.1%'
                        }
                    }
                ],
                goods_data_list: [
                    {
                        name: '上架商品数量',
                        num: '120',
                        last_day: {
                            type: 1,
                            num: '2%'
                        }
                    },
                    {
                        name: '动销产品数量',
                        num: '13',
                        last_day: {
                            type: 1,
                            num: '30%'
                        }
                    },
                    {
                        name: '平均客单价',
                        num: '234',
                        last_day: {
                            type: 1,
                            num: '30%'
                        }
                    },
                    {
                        name: '支付金额',
                        num: '54,621',
                        last_day: {
                            type: 1,
                            num: '30%'
                        }
                    },
                    {
                        name: '商品转化率',
                        num: '45%',
                        last_day: {
                            type: 0,
                            num: '23.1%'
                        }
                    },
                    {
                        name: '商品动销率',
                        num: '45%',
                        last_day: {
                            type: 0,
                            num: '23.1%'
                        }
                    }
                ],
                map_data_list: [
                    {
                        name:"南海诸岛",value:0,
                        itemStyle:{
                            normal:{opacity:0,label:{show:false}}
                        }
                    },
                    {name: '北京',value: Math.round(Math.random()*1000)},
                    {name: '天津',value: Math.round(Math.random()*1000)},
                    {name: '上海',value: Math.round(Math.random()*1000)},
                    {name: '重庆',value: Math.round(Math.random()*1000)},
                    {name: '河北',value: Math.round(Math.random()*1000)},
                    {name: '河南',value: Math.round(Math.random()*1000)},
                    {name: '云南',value: Math.round(Math.random()*1000)},
                    {name: '辽宁',value: Math.round(Math.random()*1000)},
                    {name: '黑龙江',value: Math.round(Math.random()*1000)},
                    {name: '湖南',value: Math.round(Math.random()*1000)},
                    {name: '安徽',value: Math.round(Math.random()*1000)},
                    {name: '山东',value: Math.round(Math.random()*1000)},
                    {name: '新疆',value: Math.round(Math.random()*1000)},
                    {name: '江苏',value: Math.round(Math.random()*1000)},
                    {name: '浙江',value: Math.round(Math.random()*1000)},
                    {name: '江西',value: Math.round(Math.random()*1000)},
                    {name: '湖北',value: Math.round(Math.random()*1000)},
                    {name: '广西',value: Math.round(Math.random()*1000)},
                    {name: '甘肃',value: Math.round(Math.random()*1000)},
                    {name: '山西',value: Math.round(Math.random()*1000)},
                    {name: '内蒙古',value: Math.round(Math.random()*1000)},
                    {name: '陕西',value: Math.round(Math.random()*1000)},
                    {name: '吉林',value: Math.round(Math.random()*1000)},
                    {name: '福建',value: Math.round(Math.random()*1000)},
                    {name: '贵州',value: Math.round(Math.random()*1000)},
                    {name: '广东',value: Math.round(Math.random()*1000)},
                    {name: '青海',value: Math.round(Math.random()*1000)},
                    {name: '西藏',value: Math.round(Math.random()*1000)},
                    {name: '四川',value: Math.round(Math.random()*1000)},
                    {name: '宁夏',value: Math.round(Math.random()*1000)},
                    {name: '海南',value: Math.round(Math.random()*1000)},
                    {name: '台湾',value: Math.round(Math.random()*1000)},
                    {name: '香港',value: Math.round(Math.random()*1000)},
                    {name: '澳门',value: Math.round(Math.random()*1000)}
                ],
                echarts_month_list: [
                    '2020-06-01',
                    '2020-06-04',
                    '2020-06-07',
                    '2020-06-10',
                    '2020-06-13',
                    '2020-06-16',
                    '2020-06-19',
                    '2020-06-22',
                    '2020-06-25',
                    '2020-06-28',
                    '2020-06-30',
                ],
                goods_top_list: []
            };
        },
        methods: {
            // 获取数据
            getList() {
                this.loading = true;
                let self = this;
                request({
                    params: {
                        r: 'mall/data-statistics/index'
                    },
                    method: 'get',
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.loading = false;
                });
            },
            // 生成图表
            form() {
                let that = this;
                // $('#core-data-echarts').css("width",document.body.clientWidth-342);
                var myChart = echarts.init(document.getElementById('core-data-echarts'), 'macarons');
                myChart.setOption({
                    grid: {
                        left: '0%',
                        right: '0%',  //距离右侧边距
                        bottom: '9%',
                        // show:true,
                        containLabel: true
                    },
                    // 默认色板
                    color: ['#36cbfd'],
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: '#fff',
                        textStyle: {color: '#6E7070'},
                        padding: 20,
                        extraCssText: 'box-shadow: 2px 6px 14px 1px rgba(27,98,130,0.2);',
                        formatter: function (params) {
                            return `时间：${params[0].name} <br/> 浏览量：${params[0].value}`
                        }
                    },
                    xAxis: {
                        type: 'category',
                        boundaryGap: true,
                        data: that.echarts_month_list,
                        // x轴的颜色和宽度
                        axisLine: {
                            lineStyle: {
                                color: '#DEDEDE',
                            }
                        },
                        splitLine: {show: false},
                        axisLabel: {
                            interval: 0,
                            // 显示最小值
                            showMinLabel: true,
                            // 显示最大值
                            showMaxLabel: true,
                            textStyle: {
                                // 更改坐标轴文字颜色
                                color: '#6E7070',
                                // 更改坐标轴文字大小
                                fontSize: 10
                            },
                            // formatter: function(value){
                            //     let values = value.split("-");
                            //     return `${values[0]}\n${values[1]}-${values[2]}`;
                            // }
                        },
                        axisTick: {
                            show: true,
                            alignWithLabel: true,
                            lineStyle: {
                                type: 'solid'
                            }
                        }
                    },
                    yAxis: {
                        show: true,
                        type: 'value',
                        // 分割线
                        splitLine: {
                            show: true,
                            lineStyle: {
                                type: 'dashed'
                            }
                        },
                        // y轴的字体样式
                        axisLabel: {
                            show: true,
                            textStyle: {
                                color: '#6E7070'
                            }
                        },
                        axisTick: { //y轴刻度线
                            show: false
                        },
                        axisLine: { //y轴
                            show: false
                        },
                    },
                    series: [{
                        name: '浏览量',
                        data: [820, 932, 901, 934, 1290, 1330, 1320, 1555, 3999, 1000, 6000],
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ //折线图颜色渐变
                                    offset: 0,
                                    color: '#e6f9ff'
                                }, {
                                    offset: 1,
                                    color: '#f7fdff'
                                }])
                            }
                        },
                    }],
                });
                myChart.showLoading({text: '正在加载数据'});

                var sexChartPie = echarts.init(document.getElementById('sex-proportion-echarts'), 'macarons');
                var memberChartPie = echarts.init(document.getElementById('member-proportion-echarts'), 'macarons');
                let option = {
                    tooltip: {
                        trigger: 'item',
                        // formatter: '{a} <br/>{b} : {c} ({d}%)'
                        formatter: '{d}%'
                    },
                    grid: {
                        left: '0%',
                        right: '0%',  //距离右侧边距
                        bottom: '9%',
                        // show:true,
                        containLabel: true
                    },
                    legend: {
                        orient: 'vertical',
                        right: 'right',
                        data: ['女性', '男性', '未识别']
                    },
                    color: ['#FF7161','#46C18B','#FCCE14'],
                    series: [
                        {
                            name: '性别',
                            type: 'pie',
                            // radius: '55%', // 大小
                            // center: ['50%', '60%'], // 位置宽度
                            data: [
                                {value: 700, name: '女性'},
                                {value: 200, name: '男性'},
                                {value: 100, name: '未识别'},
                            ],
                            label: {            //饼图图形上的文本标签
                                normal: {
                                    show: true,
                                    //position:'inner', //标签的位置
                                    textStyle: {
                                        fontWeight: 300,
                                        fontSize: 16    //文字的字体大小
                                    },
                                    formatter: '{d}%'
                                }
                            },
                            itemStyle: { // 此配置
                                normal: {
                                    borderWidth: 8,
                                    borderColor: '#ffffff',
                                },
                                emphasis: {
                                    borderWidth: 0,
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                                }
                            },
                            emphasis: {
                                itemStyle: {
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                                }
                            }
                        }
                    ]
                };
                let option_copy = JSON.parse(JSON.stringify(option));
                option_copy.legend= {
                    orient: 'vertical',
                    right: 'right',
                    data: ['粉丝', '分销商', '经销商']
                };
                option_copy.series = [{
                    name: '用户',
                    type: 'pie',
                    // radius: '55%', // 大小
                    // center: ['50%', '60%'], // 位置宽度
                    data: [
                        {value: 700, name: '粉丝'},
                        {value: 200, name: '分销商'},
                        {value: 100, name: '经销商'},
                    ],
                    label: {            //饼图图形上的文本标签
                        normal: {
                            show: true,
                            //position:'inner', //标签的位置
                            textStyle: {
                                fontWeight: 300,
                                fontSize: 16    //文字的字体大小
                            },
                            formatter: '{d}%'
                        }
                    },
                    itemStyle: { // 此配置
                        normal: {
                            borderWidth: 8,
                            borderColor: '#ffffff',
                        },
                        emphasis: {
                            borderWidth: 0,
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    },
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }];
                sexChartPie.setOption(option);
                memberChartPie.setOption(option_copy);

                var mapChart = echarts.init(document.getElementById('map-echarts'));
                mapChart.setOption({
                    tooltip : {
                        trigger: 'item',
                        formatter: '{b}: {c}',
                    },
                    visualMap: {
                        min: 0,
                        max: 100,
                        left: 'left',
                        top: 'bottom',
                        text:['高','低'],           // 文本，默认为数值文本
                        calculable : true,
                        inRange: {
                            color: ['#E3F1FF','#BDF6FF','#64E2F5','#00BCFF','#2589FF','#035DC9']
                        }
                    },
                    toolbox: {
                        show: true,
                        orient : 'vertical',
                        left: 'right',
                        top: 'center',
                        feature : {
                            mark : {show: true},
                            dataView : {show: true, readOnly: false},
                            restore : {show: true},
                            saveAsImage : {show: true}
                        }
                    },
                    series : [
                        {
                            type: 'map',
                            mapType: 'china',
                            roam: false,
                            label: {
                                normal: {
                                    show: false
                                },
                                emphasis: {
                                    show: true,
                                    textStyle: {
                                        color: 'rgb(249, 249, 249)'
                                    }
                                }
                            },
                            data: that.map_data_list
                        }
                    ]
                })
            },
            form1() {
                let that = this;
                // $('#core-data-echarts').css("width",document.body.clientWidth-342);
                var myChart = echarts.init(document.getElementById('flow-conversion-echarts'), 'macarons');
                myChart.setOption({
                    grid: {
                        left: '0%',
                        right: '0%',  //距离右侧边距
                        bottom: '9%',
                        // show:true,
                        containLabel: true
                    },
                    // 默认色板
                    color: ['#36cbfd'],
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: '#fff',
                        textStyle: {color: '#6E7070'},
                        padding: 20,
                        extraCssText: 'box-shadow: 2px 6px 14px 1px rgba(27,98,130,0.2);',
                        formatter: function (params) {
                            return `时间：${params[0].name} <br/> 浏览量：${params[0].value}`
                        }
                    },
                    xAxis: {
                        type: 'category',
                        boundaryGap: true,
                        data: that.echarts_month_list,
                        // x轴的颜色和宽度
                        axisLine: {
                            lineStyle: {
                                color: '#DEDEDE',
                            }
                        },
                        splitLine: {show: false},
                        axisLabel: {
                            interval: 0,
                            // 显示最小值
                            showMinLabel: true,
                            // 显示最大值
                            showMaxLabel: true,
                            textStyle: {
                                // 更改坐标轴文字颜色
                                color: '#6E7070',
                                // 更改坐标轴文字大小
                                fontSize: 10
                            },
                            // formatter: function(value){
                            //     let values = value.split("-");
                            //     return `${values[0]}\n${values[1]}-${values[2]}`;
                            // }
                        },
                        axisTick: {
                            show: true,
                            alignWithLabel: true,
                            lineStyle: {
                                type: 'solid'
                            }
                        }
                    },
                    yAxis: {
                        show: true,
                        type: 'value',
                        // 分割线
                        splitLine: {
                            show: true,
                            lineStyle: {
                                type: 'dashed'
                            }
                        },
                        // y轴的字体样式
                        axisLabel: {
                            show: true,
                            textStyle: {
                                color: '#6E7070'
                            }
                        },
                        axisTick: { //y轴刻度线
                            show: false
                        },
                        axisLine: { //y轴
                            show: false
                        },
                    },
                    series: [{
                        name: '浏览量',
                        data: [820, 932, 901, 934, 1290, 1330, 1320, 1555, 3999, 1000, 6000],
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ //折线图颜色渐变
                                    offset: 0,
                                    color: '#e6f9ff'
                                }, {
                                    offset: 1,
                                    color: '#f7fdff'
                                }])
                            }
                        },
                    }],
                });
                // myChart.showLoading({text: '正在加载数据'});
            },
            toSearch(searchData) {
                this.search = searchData;
                this.getList();
                this.tab_pay();
                this.changeUser();
            },
            searchList(searchData) {
                this.search = searchData;
                this.page = 1;
                this.getList();
            },
            tabMch() {
                this.getList();
            },
        },
        mounted() {
            this.getList();
            setTimeout(() => {
                this.form();
                var myChart = echarts.init(document.getElementById('core-data-echarts'));
                myChart.hideLoading();
                this.form1();
            }, 1000)


        },
        created() {
            let temp_goods_top_list = [];
            for (let i = 1;i <= 5;i++){
                temp_goods_top_list.push({
                    id: i,
                    ranking: i,
                    pic_url: 'http://jxmall.sinbel.top/web//uploads/images/original/20200608/bbe6ddad872e2eb47b78adf895df98c6.jpg',
                    goods_name: '【官方正品】NARS流光美肌轻透粉光亮的一批墙裂推荐',
                    visitor: 545,
                    amount: 5641
                })
            }
            this.goods_top_list = temp_goods_top_list;

            let date = new Date();
            let timestamp = date.getTime();
            let seperator1 = "-";
            let year = date.getFullYear();
            let nowMonth = date.getMonth() + 1;
            let strDate = date.getDate();
            if (nowMonth >= 1 && nowMonth <= 9) {
                nowMonth = "0" + nowMonth;
            }
            if (strDate >= 0 && strDate <= 9) {
                strDate = "0" + strDate;
            }
            this.today = year + seperator1 + nowMonth + seperator1 + strDate;
            let week = new Date(timestamp - 7 * 24 * 3600 * 1000)
            let weekYear = week.getFullYear();
            let weekMonth = week.getMonth() + 1;
            let weekStrDate = week.getDate();
            if (weekMonth >= 1 && weekMonth <= 9) {
                weekMonth = "0" + weekMonth;
            }
            if (weekStrDate >= 0 && weekStrDate <= 9) {
                weekStrDate = "0" + weekStrDate;
            }
            this.weekDay = weekYear + seperator1 + weekMonth + seperator1 + weekStrDate;
            let month = new Date(timestamp - 30 * 24 * 3600 * 1000);
            let monthYear = month.getFullYear();
            let monthMonth = month.getMonth() + 1;
            let monthStrDate = month.getDate();
            if (monthMonth >= 1 && monthMonth <= 9) {
                monthMonth = "0" + monthMonth;
            }
            if (monthStrDate >= 0 && monthStrDate <= 9) {
                monthStrDate = "0" + monthStrDate;
            }
            this.monthDay = monthYear + seperator1 + monthMonth + seperator1 + monthStrDate;
        },
    })
</script>