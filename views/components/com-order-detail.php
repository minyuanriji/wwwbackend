<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-23
 * Time: 22:03
 */
Yii::$app->loadComponentView('order/com-edit-address');
Yii::$app->loadComponentView('order/com-edit-seller-remark');
Yii::$app->loadComponentView('order/com-clerk');
Yii::$app->loadComponentView('order/com-send');
Yii::$app->loadComponentView('order/com-edit-price');
Yii::$app->loadComponentView('order/com-city');
?>

<style>
    .com-order-detail .com-order-count-price {
        float: right;
        margin-right: 55px;
        font-size: 12px;
        text-align: right;
    }

    .com-order-detail .el-step__icon-inner {
        font-size: 30px;
    }

    .com-order-detail .com-order-status {
        padding: 50px 120px;
        margin-bottom: 30px;
    }

    .com-order-detail .com-order-status .el-step__icon.is-text {
        border: 0px;
        width: 40px;
    }

    .com-order-detail .com-order-count-price .el-form-item {
        margin-bottom: 5px;
    }

    .com-order-detail .el-collapse-item__header {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        border-bottom: none;
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;
    }

    .com-order-detail .el-collapse-item:last-child {
        margin-bottom: 0;
    }

    .com-order-detail .el-collapse {
        border: none;
    }

    .com-order-detail .order-status {
        display: flex;
        flex-wrap: wrap;
    }

    .com-order-detail .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .com-order-detail .order-status .el-form-item {
        width: 50%;
        min-width: 250px;
    }

    .com-order-detail .el-step.is-center .el-step__description {
        padding: 0 10%;
    }

    /*新的*/
    .com-order-detail .card-box {
        border: 1px solid #EBEEF5;
        border-radius: 3px;
        padding: 10px;
        height: 300px;
        overflow-y: scroll;
    }

    .com-order-detail .card-box .label {
        color: #999999;
        margin-right: 10px;
    }

    .com-order-detail .share-price {
        color: #EF8933;
    }

    .com-order-detail .share-price .orange-label {
        color: #EF8933;
    }

    .com-order-detail .share-title {
        font-size: 15px;
        margin: 10px 0 5px;
    }

    .com-order-detail .action-box {
        padding: 10px 20px;
    }

    .com-order-detail .item-box {
        margin-bottom: 10px;
    }

    .com-order-detail .store-address {
        margin-left: 65px;
        margin-top: 5px;
    }

    .com-order-detail .express-address {
        width: 80%;
    }

    .com-order-detail .goods-pic {
        width: 35px;
        height: 35px;
        margin: 0 4px;
    }

    .order-detail-form-list {
        margin: 0 -10px;
    }

    .order-detail-form-item {
        border-top: 1px solid #EBEEF5;
        padding: 10px;
    }
</style>

<template id="com-order-detail">
    <div class="com-order-detail">
        <com-edit-address
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="addressVisible"
                :order="newOrder">
        </com-edit-address>
        <com-edit-seller-remark
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="sellerRemarkVisible"
                :order="newOrder">
        </com-edit-seller-remark>
        <com-clerk
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="clerkVisible"
                :order="newOrder">
        </com-clerk>
        <com-send
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="sendVisible"
                :send-type="sendType"
                :express-id="expressId"
                :order="newOrder">
        </com-send>
        <com-edit-price
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="changePriceVisible"
                :order="newOrder">
        </com-edit-price>
        <com-city
                @close="dialogClose"
                @submit="dialogSubmit"
                :is-show="citySendVisible"
                :send-type="sendType"
                :order="newOrder">
        </com-city>

        <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
            <!-- 标题栏 -->
            <div slot="header">
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item>
                        <span style="color: #409EFF;cursor: pointer" @click="toList">订单列表</span>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item>订单详情</el-breadcrumb-item>
                </el-breadcrumb>
            </div>
            <!-- 订单进度 -->
            <div class="table-body" v-loading="loading">
                <el-card class="com-order-status" shadow="never">
                    <el-steps v-if="isShowSteps" :active="active" align-center>
                        <el-step title="已下单" :description="order.created_at|dateTimeFormat('Y-m-d H:i:s')">
                            <template slot="icon">
                                <img src="statics/img/mall/order/status/status_1_active.png">
                            </template>
                        </el-step>
                        <el-step :title="active > 1 && order.is_pay == 1 ? '已付款':'未付款'"
                                 v-show="order.cancel_status != 1 || order.is_pay == 1">
                            <template slot="icon">
                                <img v-if="active > 1 && order.is_pay == 1"
                                     src="statics/img/mall/order/status/status_2_active.png">
                                <img v-else src="statics/img/mall/order/status/status_2.png">
                            </template>
                            <template slot="description">
                                <div v-if="order.pay_at != '0'">{{order.pay_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                <div v-if="order.is_pay == 0 && order.pay_type != 2 && order.auto_cancel_at"
                                     style="color: #ff4544">预计 {{order.auto_cancel_at|dateTimeFormat('Y-m-d H:i:s')}}
                                    自动取消订单
                                </div>
                            </template>
                        </el-step>
                        <el-step :title="active > 2 ? '已发货':'未发货'" v-show="order.cancel_status != 1">
                            <template slot="description">
                                <div v-if="order.send_at != '0'">{{order.send_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            </template>
                            <template slot="icon">
                                <img v-if="active > 2" src="statics/img/mall/order/status/status_3_active.png">
                                <img v-else src="statics/img/mall/order/status/status_3.png">
                            </template>
                        </el-step>
                        <el-step :title="active > 3 ? '已收货':'未收货'"
                                 v-show="order.cancel_status != 1 && order.is_send == 1">
                            <template slot="icon">
                                <img v-if="active > 3" src="statics/img/mall/order/status/status_4_active.png">
                                <img v-else src="statics/img/mall/order/status/status_4.png">
                            </template>
                            <template slot="description">
                                <div v-if="order.auto_confirm_at !='0'"
                                     style="color: #ff4544">预计 {{order.auto_confirm|dateTimeFormat('Y-m-d H:i:s')}}
                                    自动确认收货
                                </div>
                            </template>
                        </el-step>
                        <el-step :title="active > 4 ? '已结束':'未结束'"
                                 v-show="order.cancel_status != 1 || order.is_confirm == 1">
                            <template slot="icon">
                                <img v-if="active > 4" src="statics/img/mall/order/status/status_5_active.png">
                                <img v-else src="statics/img/mall/order/status/status_5.png">
                            </template>
                            <template slot="description">
                                <div v-if="order.confirm_at != '0' && order.is_sale == 1">
                                    {{order.confirm_at|dateTimeFormat('Y-m-d H:i:s')}}
                                </div>
                                <div v-if="order.is_confirm == 1 && order.is_sale == 0 && order.auto_sales_at"
                                     style="color: #ff4544">预计 {{order.auto_sales_at|dateTimeFormat('Y-m-d H:i:s')}}
                                    自动结束订单
                                </div>
                            </template>
                        </el-step>
                        <el-step title="已取消"
                                 :description="order.cancel_at != '0' ? order.cancel_at : ''"
                                 v-if="order.cancel_status == 1">
                            <template slot="icon">
                                <img src="statics/img/mall/order/status/status_6_active.png">
                            </template>
                        </el-step>
                    </el-steps>
                    <slot name="steps"></slot>
                </el-card>
                <el-row :gutter="12">
                    <el-col :span="8">
                        <div class="card-box">
                            <h3>订单信息</h3>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label">订单号:</span>
                                <div>{{ order.order_no}}</div>
                            </div>
                            <div v-if="order.paymentOrder" class="item-box" flex="dir:left cross:center">
                                <span class="label">商户单号:</span>
                                <div>{{ order.paymentOrder.paymentOrderUnion.order_no }}</div>
                            </div>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label">支付方式:</span>
                                <el-tag size="small" hit type="success" v-if="order.pay_type == 1">线上支付</el-tag>
                                <el-tag size="small" hit type="success" v-if="order.pay_type == 3">余额支付</el-tag>
                                <el-tag size="small" hit type="success" v-if="order.pay_type == 2">货到付款</el-tag>
                            </div>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label">配送方式:</span>
                                <el-tag size="small" hit type="primary" v-if="order.send_type == 0">快递发送</el-tag>
                                <el-tag size="small" hit type="primary" v-if="order.send_type == 1">到店自提</el-tag>
                                <el-tag size="small" hit type="primary" v-if="order.send_type == 2">同城配送</el-tag>
                            </div>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label">用户:</span>
                                <div>{{ order.user.nickname }}</div>
                            </div>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label">{{order.send_type == 0 ? '收货人' : '联系人'}}:</span>
                                <div>{{ order.name }}</div>
                            </div>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label">电话:</span>
                                <div>{{ order.mobile }}</div>
                            </div>
                            <div class="item-box" flex="dir:left cross:center">
                                <template v-if="order.send_type == 1">
                                    <span class="label">收货地址:</span>
                                    <el-tag size="small" hit type="warning">到店自提</el-tag>
                                </template>
                                <template v-else-if="order.address">
                                    <span class="label">收货地址:</span>
                                    <div class="express-address">
                                        {{ order.address }}
                                        <el-button
                                                v-if="isShowEditAddress && order.send_type != 2 && order.is_send==0 && order.cancel_status==0"
                                                type="text"
                                                icon="el-icon-edit"
                                                circle
                                                size="small"
                                                @click="openDialog(order, addressVisible = true)">
                                        </el-button>
                                    </div>
                                </template>
                            </div>
                            <!-- 物流信息 -->
                            <template>
                                <!-- TODO 兼容 -->
                                <div v-if="order.is_send == 1 && order.detailExpress.length == 0 && order.express && order.express_no"
                                     class="item-box"
                                     flex="dir:left cross:center">
                                    <span class="label">物流信息:</span>
                                    <el-tag style="margin-right: 5px;" type="info" hit size="small">{{ order.express}}
                                    </el-tag>
                                    <a :href="'https://www.baidu.com/s?wd='+ order.express + order.express_no"
                                       target="_blank" title='点击搜索运单号'>{{ order.express_no }}</a>
                                    <el-button v-if="isShowSend && order.is_confirm == 0"
                                               type="text"
                                               icon="el-icon-edit"
                                               circle
                                               @click="openExpress(order,'send')">
                                    </el-button>
                                    <el-button v-if="order.expressSingle" size="mini"
                                               @click="printTeplate(order.expressSingle.print_teplate)">打印此面单
                                    </el-button>
                                </div>
                                <div v-else-if="order.is_send == 1 && order.detailExpress.length == 1" class="item-box"
                                     flex="dir:left cross:center">
                                    <span class="label">物流信息:</span>
                                    <template v-if="order.detailExpress[0].send_type == 1">
                                        <el-tag style="margin-right: 5px;" type="info" hit size="small">{{
                                            order.detailExpress[0].express }}
                                        </el-tag>
                                        <a :href="'https://www.baidu.com/s?wd='+ order.detailExpress[0].express + order.detailExpress[0].express_no"
                                           target="_blank" title='点击搜索运单号'>{{ order.detailExpress[0].express_no }}</a>
                                    </template>
                                    <template v-else>
                                        <span>{{order.detailExpress[0].express_content}}</span>
                                    </template>
                                    <el-button v-if="isShowSend && order.is_confirm == 0"
                                               type="text"
                                               icon="el-icon-edit"
                                               circle
                                               @click="openExpress(order,'change', order.detailExpress[0].id)">
                                    </el-button>
                                    <el-button v-if="order.detailExpress[0].expressSingle" size="mini"
                                               @click="printTeplate(order.detailExpress[0].expressSingle.print_teplate)">
                                        打印此面单
                                    </el-button>
                                </div>
                                <div v-else-if="order.detailExpress.length >= 1"
                                     v-for="(expressItem, expressIndex) in order.detailExpress" :key="expressItem.id"
                                     class="item-box" flex="dir:left">
                                    <div>
                                        <div class="label" style="background: #fffaef;color: #e6a23c;padding: 3px 0;">
                                            收货信息:{{expressIndex + 1}}
                                        </div>
                                    </div>
                                    <div flex="dir:top">
                                        <div flex="cross:center">
                                            <template v-if="expressItem.send_type == 1">
                                                <el-tag style="margin-right: 5px;" type="info" hit size="small">
                                                    {{expressItem.express}}
                                                </el-tag>
                                                <a :href="'https://www.baidu.com/s?wd='+ expressItem.express + expressItem.express_no"
                                                   target="_blank" title='点击搜索运单号'>{{ expressItem.express_no }}</a>
                                            </template>
                                            <template v-else>
                                                <span>{{expressItem.express_content}}</span>
                                            </template>
                                            <el-button v-if="isShowSend && order.is_confirm == 0"
                                                       type="text"
                                                       style="padding: 2px 12px"
                                                       icon="el-icon-edit"
                                                       circle
                                                       @click="openExpress(order,'change', expressItem.id)">
                                            </el-button>
                                            <el-button v-if="expressItem.expressSingle" size="mini"
                                                       @click="printTeplate(expressItem.expressSingle.print_teplate)">
                                                打印此面单
                                            </el-button>
                                        </div>
                                        <div flex="dir:left" style="margin-top: 10px;">
                                            <span class="label">配送商品:</span>
                                            <img v-for="erItem in expressItem.expressRelation"
                                                 :key="erItem.id"
                                                 class="goods-pic"
                                                 :src="erItem.orderDetail.goods_info.goods_attr.pic_url ? erItem.orderDetail.goods_info.goods_attr.pic_url : erItem.orderDetail.goods_info.goods_attr.cover_pic">
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div v-if="order.store_id > 0" class="item-box" flex="dir:top">
                                <div flex="dir:left cross:center">
                                    <span class="label">自提门店:</span>
                                    <el-tag type="info" hit size="small">{{ order.store.name }}</el-tag>
                                </div>
                                <div class="store-address">{{ order.store.address }}</div>
                            </div>
                            <div v-if="order.clerk != null" class="item-box" flex="dir:left cross:center">
                                <span class="label">核销人:</span>
                                <el-tag type="info" hit size="small">{{ order.clerk.nickname }}</el-tag>
                            </div>
                            <div v-if="order.orderClerk != null && order.orderClerk.clerk_remark" class="item-box"
                                 flex="dir:left cross:center">
                                <span class="label">核销备注:</span>
                                <div>{{order.orderClerk.clerk_remark}}</div>
                            </div>
                            <div v-if="order.send_type == 2 && order.is_send == 1" class="item-box" flex="dir:top">
                                <div flex="dir:left cross:center">
                                    <span class="label">配送员:</span>
                                    <span>{{order.city_name}}</span>
                                    <span style="margin: 0 10px;">{{order.city_mobile}}</span>
                                    <el-button v-if="isShowSend && order.is_confirm == 0"
                                               type="text"
                                               icon="el-icon-edit"
                                               circle
                                               @click="openCity(order,'change')">
                                    </el-button>
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="8">
                        <div flex="dir:top" class="card-box">
                            <h3>表单信息</h3>
                            <div v-if="item.value" v-for="(item, index) in order.order_form" :key="index"
                                 class="item-box" flex="dir:left cross:center">
                                <span class="label">{{item.label}}:</span>
                                <a v-if="item.key == 'img_upload'" :href="item.value" target="_blank">
                                    <img style="height: 80px;width: 80px" :src="item.value" alt=""></a>
                                <span v-else>{{item.value}}</span>
                            </div>
                            <div v-if="order.remark" class="item-box" flex="dir:left cross:center">
                                <span class="label">用户订单留言:</span>
                                <div>{{order.remark}}</div>
                            </div>
                            <div v-if="order.words" class="item-box" flex="dir:left cross:center">
                                <span class="label">商家订单留言:</span>
                                <div>{{order.words}}</div>
                            </div>
                            <div class="item-box" flex="dir:left cross:center">
                                <span class="label">商家订单备注:</span>
                                {{ order.seller_remark }}
                                <i v-if="isShowRemark"
                                   class="el-icon-edit"
                                   style="color: #409EFF;cursor: pointer;margin-left: 10px;"
                                   @click="openDialog(order, sellerRemarkVisible = true)">
                                </i>
                            </div>
                            <div class="order-detail-form-list">
                                <div class="order-detail-form-item"
                                     v-for="(orderDetail, orderDetailIndex) in order.detail"
                                     :key="orderDetailIndex"
                                     v-if="orderDetail.form_data && orderDetail.same_form !== true">
                                    <h3>商品标题</h3>
                                    <div v-for="(subOrderDetail, subOrderDetailIndex) in order.detail"
                                         :key="orderDetail.form_id + '_' + subOrderDetailIndex"
                                         v-if="orderDetail.form_id == subOrderDetail.form_id"
                                         flex="cross:center box:first" style="margin-bottom: 10px">
                                        <el-image :src="
                                        subOrderDetail.goods_info && subOrderDetail.goods_info.goods_attr && subOrderDetail.goods_info.goods_attr.pic_url ?
                                        subOrderDetail.goods_info.goods_attr.pic_url : subOrderDetail.goods.cover_pic"
                                                  style="width: 50px;height: 50px; margin-right: 10px;"></el-image>
                                        <div style="color: #999999;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
                                            {{subOrderDetail.goods.goodsWarehouse.name}}
                                        </div>
                                    </div>
                                    <h3>表单信息</h3>
                                    <div v-if="item.value" v-for="(item, index) in orderDetail.form_data" :key="index"
                                         class="item-box" flex="dir:left cross:center">
                                        <span class="label">{{item.label}}:</span>
                                        <a v-if="item.key == 'img_upload'" :href="item.value" target="_blank">
                                            <img style="height: 80px;width: 80px" :src="item.value" alt=""></a>
                                        <span v-else>{{item.value}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="8">
                        <div v-if="isShowShare" flex="dir:top" class="card-box">
                            <h3>分润信息</h3>

                            <div v-if="price_logs.length">
                                <el-table :data="price_logs" border style="width: 100%">
                                    <el-table-column label="用户">
                                        <template slot-scope="scope">
                                            <com-image style="float: left;margin-right: 5px;"
                                                       mode="aspectFill"
                                                       :src="scope.row.avatar_url"></com-image>
                                            <div>{{scope.row.nickname}}</div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="等级">
                                        <template slot-scope="scope">
                                            <div v-if="scope.row.role_type=='branch_office'">分公司</div>
                                            <div v-if="scope.row.role_type=='partner'">合伙人</div>
                                            <div v-if="scope.row.role_type=='store'">VIP会员</div>
                                            <div v-if="scope.row.role_type=='user'">用户</div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="price" label="佣金">
                                    </el-table-column>
                                    <el-table-column label="佣金类型">
                                        <template slot-scope="scope">
                                            <div>分销</div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="状态">
                                        <template slot-scope="scope">
                                            <div v-if="scope.row.status == 0">未结算</div>
                                            <div v-if="scope.row.status == 1">已结算</div>
                                        </template>
                                    </el-table-column>
                                </el-table>

                            </div>
                        </div>
                        <slot name="shareInfo"></slot>
                    </el-col>
                </el-row>
                <slot :order="order"></slot>
                <el-card shadow="never" style="margin-top: 15px;">
                    <el-table stripe border :data="order.detail" style="width: 100%;margin-bottom: 15px;">
                        <el-table-column prop="goods" label="商品标题">
                            <template slot-scope="scope">
                                <div flex="dir:left cross:center">
                                    <img :src="scope.row.goods_info && scope.row.goods_info.goods_attr && scope.row.goods_info.goods_attr.pic_url ?
                                     scope.row.goods_info.goods_attr.pic_url : scope.row.goods.cover_pic" alt=""
                                         style="height: 60px;width: 60px;margin-right: 5px">
                                    <com-ellipsis :line="1">{{scope.row.goods_info && scope.row.goods_info.goods_attr &&
                                        scope.row.goods_info.goods_attr.name ?
                                        scope.row.goods_info.goods_attr.name : scope.row.goods.goodsWarehouse.name}}
                                    </com-ellipsis>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column align="center" prop="attr" label="规格" width="220">
                            <template slot-scope="scope">
                                <el-tag size="mini" style="margin-right: 5px;" v-for="attr in scope.row.attr_list"
                                        :key="attr.id">{{attr.attr_group_name}}:{{attr.attr_name}}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column align="center" prop="unit_price" label="单价" width="120">
                            <template slot-scope="scope">
                                ￥{{scope.row.unit_price}}
                            </template>
                        </el-table-column>
                        <el-table-column align="center" prop="num" label="数量" width="80"></el-table-column>
                        <el-table-column align="center" prop="total_original_price" label="原价" width="120">
                            <template slot-scope="scope">
                                ￥{{scope.row.goods_info.goods_attr.original_price}}
                            </template>
                        </el-table-column>
                        <el-table-column align="center" prop="total_price" label="折扣后" width="120">
                            <template slot-scope="scope">
                                ￥{{scope.row.total_price}}
                            </template>
                        </el-table-column>
                        <el-table-column align="center" label="积分" width="120">
                            <template slot-scope="scope">
                                <span style="color:darkred">￥-{{scope.row.use_score_price}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column align="center" label="红包" width="120">
                            <template slot-scope="scope">
                                <span style="color:darkred">￥-{{scope.row.integral_price}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column align="center" label="购物券" width="120">
                            <template slot-scope="scope">
                                <span style="color:darkred">￥-{{scope.row.shopping_voucher_decode_price}}</span>
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-form label-width="200px" :model="order" class="com-order-count-price">
                        <el-form-item label="商品小计">
                            <span>￥{{ order.total_goods_original_price }}</span>
                        </el-form-item>
                        <el-form-item label="运费">
                            <span>￥{{ order.express_original_price }}</span>
                        </el-form-item>
                        <el-form-item label="会员折扣" v-if="order.member_discount_price != 0.00">
                            <span style="color:#ff4544;">-￥{{ order.member_discount_price }}</span>
                        </el-form-item>
                        <el-form-item label="积分抵扣" v-if="order.score_deduction_price != 0.00">
                            <span style="color:#ff4544;">-￥{{ order.score_deduction_price }}</span>
                        </el-form-item>
                        <el-form-item label="红包券抵扣" v-if="order.integral_deduction_price != 0.00">
                            <span style="color:#ff4544;">-￥{{ order.integral_deduction_price }}</span>
                        </el-form-item>
                        <el-form-item label="优惠券抵扣" v-if="order.coupon_discount_price != 0.00">
                            <span style="color:#ff4544;">-￥{{ order.coupon_discount_price }}</span>
                        </el-form-item>
                        <!--插件特殊优惠-->
                        <template v-if="order.plugin_data" v-for="pluginData in order.plugin_data">
                            <el-form-item :label="pluginData.label">
                                <span style="color:#ff4544;">-￥{{pluginData.value}}</span>
                            </el-form-item>
                        </template>

                        <el-form-item label="商品加价"
                                      v-if="(order.total_goods_original_price - order.total_goods_price) < 0">
                            <span style="color:#ff4544;">￥{{ (order.total_goods_price - order.total_goods_original_price).toFixed(2) }}</span>
                        </el-form-item>
                        <el-form-item label="运费减免" v-if="(order.express_original_price - order.express_price) > 0">
                            <span style="color:#ff4544;">￥{{ (order.express_original_price - order.express_price).toFixed(2) }}</span>
                        </el-form-item>
                        <el-form-item label="运费增加" v-if="(order.express_original_price - order.express_price) < 0">
                            <span style="color:#ff4544;">￥{{ (order.express_price - order.express_original_price).toFixed(2) }}</span>
                        </el-form-item>
                        <el-form-item label="订单改价" v-if="order.back_price != 0.00">
                            <span v-if="order.back_price > 0.00" style="color:#ff4544;">-￥{{ order.back_price }}</span>
                            <span v-if="order.back_price < 0.00" style="color:#ff4544;">￥{{ -order.back_price }}</span>
                        </el-form-item>
                        <el-form-item label="积分抵扣">
                            <span style="color:#ff4544;">-￥{{ order.score_deduction_price }}</span>
                        </el-form-item>
                        <el-form-item label="红包抵扣">
                            <span style="color:#ff4544;">-￥{{ order.integral_deduction_price }}</span>
                        </el-form-item>
                        <el-form-item label="购物券抵扣">
                            <span style="color:#ff4544;">-￥{{ order.shopping_voucher_decode_price }}</span>
                        </el-form-item>
                        <el-form-item label="实付款">
                            <span style="color:darkgreen;">￥<b>{{ order.total_pay_price }}</b></span>
                        </el-form-item>
                    </el-form>
                </el-card>
                <div class="action-box" flex="dir:right">
                    <div>
                        <!-- 结束 -->
                        <template v-if="order.sale_status == 0">
                            <el-button
                                    :loading="btnLoading"
                                    v-if="order.is_recycle == 0 && order.is_confirm == 1 && order.is_sale == 0 && isShowFinish && order.status != 0"
                                    size="small" type="primary" @click="saleOrder(order.id)">结束订单
                            </el-button>
                            <!-- 确认收货 -->
                            <el-button
                                    :loading="btnLoading"
                                    v-if="order.is_recycle == 0 && order.is_send == 1 && order.is_confirm == 0 && isShowConfirm && order.status != 0 && order.is_confirm_show == 1"
                                    size="small" type="primary" @click="confirm(order.id)">确认收货
                            </el-button>
                            <el-button v-if="order.expressSingle" size="small" type="primary"
                                       @click="expressSingle(order.expressSingle.print_teplate)">电子面单
                            </el-button>
                            <!-- 核销 -->
                            <el-button
                                    v-if="order.send_type == 1 && (order.is_pay == 1 || order.pay_type == 2) && order.clerk == null && order.is_send == 0 && order.is_clerk_show && order.is_recycle == 0 && isShowClerk && order.is_recycle == 0 && order.status != 0 && order.cancel_status != 1"
                                    size="small" type="primary" @click="openDialog(order, clerkVisible = true)">核销订单
                            </el-button>
                        </template>
                        <!-- 发货 -->
                        <template v-if="order.status == 1 && order.sale_status == 0">
                            <!-- 正常发货 -->
                            <el-button
                                    v-if="order.send_type == 0 && order.is_send == 0 && order.cancel_status != 1 && (order.is_pay == 1 || order.pay_type == 2) && order.is_send_show == 1 && isShowSend && order.is_recycle == 0 && order.status != 0"
                                    size="small" type="primary" @click="openExpress(order,'send')">发货
                            </el-button>
                            <!-- 同城配送 -->
                            <el-button
                                    v-if="order.send_type == 2 && order.is_send == 0 && order.cancel_status != 1 && (order.is_pay == 1 || order.pay_type == 2) && order.is_send_show == 1 && isShowSend && order.is_recycle == 0 && order.status != 0"
                                    size="small" type="primary" @click="openCity(order,'send')">发货
                            </el-button>
                            <!-- 到店自提发货 -->
                            <el-button
                                    v-if="order.send_type == 1 && order.is_send == 0 && order.cancel_status != 1 && (order.is_pay == 1 || order.pay_type == 2) && order.is_send_show == 1 && isShowSend && order.is_recycle == 0 && order.status != 0"
                                    size="small" @click="storeOrderSend(order)" type="primary">发货
                            </el-button>
                        </template>
                        <!-- 打印小票 -->
                        <el-button :loading="btnLoading"
                                   v-if="order.is_recycle == 0 && order.status != 0 && isShowPrint" size="small"
                                   type="primary" @click="print(order.id)">打印小票
                        </el-button>
                    </div>
                </div>
            </div>
        </el-card>
    </div>
</template>

<script>
    Vue.component('com-order-detail', {
        template: '#com-order-detail',
        props: {
            getDetailUrl: {
                type: String,
                default: 'mall/order/detail'
            },
            getOrderListUrl: {
                type: String,
                default: 'mall/order/index'
            },
            // 控制按钮是否显示
            // 编辑收货地址
            isShowEditAddress: {
                type: Boolean,
                default: true
            },
            // 编辑订单备注
            isShowRemark: {
                type: Boolean,
                default: true
            },
            // 结束订单
            isShowFinish: {
                type: Boolean,
                default: true
            },
            // 确认收货
            isShowConfirm: {
                type: Boolean,
                default: true
            },
            // 小票打印
            isShowPrint: {
                type: Boolean,
                default: true
            },
            // 订单核销
            isShowClerk: {
                type: Boolean,
                default: true
            },
            // 订单发货
            isShowSend: {
                type: Boolean,
                default: true
            },
            // 订单状态进度
            isShowSteps: {
                type: Boolean,
                default: true
            },
            // 订单状态进度
            isShowShare: {
                type: Boolean,
                default: true
            },
            // 订单数据可从父组件传入 start
            // 组件内部不请求数据
            isNewRequest: {
                type: Boolean,
                default: false
            },
            // 父组件订单数据
            orderData: {
                type: Object,
                default: function () {
                    return {}
                }
            },
            // 订单数据可从父组件传入 end
            // 订单状态
            newActive: {
                type: Number,
                default: 2,
            },
        },
        data() {
            return {
                loading: false,
                newOrder: {},// 传给各子组件的订单信息
                addressVisible: false,// 修改收货地址
                sellerRemarkVisible: false,// 添加商户备注
                clerkVisible: false,// 订单核销
                sendVisible: false,// 发货
                sendType: '',// 发货类型
                expressId: 0,// 编辑发货,物流ID
                cancelVisible: false,// 订单取消
                cancelType: -1,// 订单取消状态 同意|拒绝
                changePriceVisible: false,// 修改订单价格
                active: 1,
                order: {
                    user: {},
                    detailExpress: [],
                },
                common_order_detail_list: [],
                price_logs: [],
                btnLoading: false,
                citySendVisible: false,//选择配送员
            };
        },
        watch: {
            orderData: function (newVal) {
                this.order = newVal;
            },
            newActive: function (newVal) {
                this.active = newVal;
            }
        },
        created() {
            // 数据从父组件传入
            if (!this.isNewRequest) {
                this.getDetail();
            }
        },
        methods: {
            //获取列表
            getDetail() {
                this.loading = true;
                request({
                    params: {
                        r: this.getDetailUrl,
                        order_id: getQuery('order_id'),
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.price_logs = e.data.data.price_logs
                        this.common_order_detail_list = e.data.data.common_order_detail_list

                        this.order = e.data.data.order;
                        this.$emit('get-detail', this.order)
                        if (this.order.cancel_status == 1) {
                            this.active = 5;
                        }
                        if (this.order.is_pay == 1) {
                            this.active = 2;
                        }
                        if (this.order.is_send == 1) {
                            this.active = 3;
                        }
                        if (this.order.is_confirm == 1) {
                            this.active = 4;
                        }
                        if (this.order.is_sale == 1) {
                            this.active = 5;
                        }
                    }
                }).catch(e => {
                });
            },
            // 新的
            openDialog(order) {
                this.newOrder = order;
            },
            dialogClose() {
                this.addressVisible = false;
                this.sellerRemarkVisible = false;
                this.clerkVisible = false;
                this.sendVisible = false;
                this.changePriceVisible = false;
                this.citySendVisible = false;
            },
            dialogSubmit() {
                this.expressId = 0;
                this.getDetail()
            },
            // 发货
            openExpress(order, type, expressId = 0) {
                this.newOrder = order;
                this.sendType = type;
                this.sendVisible = true;
                this.expressId = parseInt(expressId);
            },
            // 确认收货
            confirm(id) {
                this.$confirm('是否确认收货?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    this.btnLoading = true;
                    request({
                        params: {
                            r: 'mall/order/confirm',
                        },
                        data: {
                            order_id: id
                        },
                        method: 'post',
                    }).then(e => {
                        this.btnLoading = false;
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getDetail();
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'error'
                            });
                        }
                    }).catch(e => {
                        this.$message({
                            message: e.data.msg,
                            type: 'error'
                        });
                    });
                }).catch(() => {
                    this.$message({
                        message: e.data.msg,
                        type: 'error'
                    });
                });
            },
            // 结束订单
            saleOrder(id) {
                this.$confirm('是否结束该订单?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    this.btnLoading = true;
                    request({
                        params: {
                            r: 'mall/order/order-sales',
                        },
                        data: {
                            order_id: id
                        },
                        method: 'post',
                    }).then(e => {
                        this.btnLoading = false;
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getDetail();
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'error'
                            });
                        }
                    }).catch(e => {
                        this.$message({
                            message: e.data.msg,
                            type: 'error'
                        });
                    });
                }).catch(() => {
                    this.$message({
                        message: e.data.msg,
                        type: 'error'
                    });
                });
            },
            // 打印小票
            print(id) {
                this.$confirm('是否打印小票?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    this.btnLoading = true;
                    request({
                        params: {
                            r: 'mall/order/order-print',
                            order_id: id
                        },
                        method: 'get',
                    }).then(e => {
                        this.btnLoading = false;
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getDetail();
                        }
                        this.$message({
                            message: e.data.msg,
                            type: 'warning'
                        });
                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
            expressSingle(htmlData) {
                myWindow = window.open('', '_blank');
                myWindow.document.write(htmlData);
                myWindow.focus();
            },
            openCity(order, sendType) {
                this.newOrder = order;
                this.sendType = sendType
                this.citySendVisible = true;
            },
            toList() {
                this.$navigate({
                    r: this.getOrderListUrl,
                })
            },
            storeOrderSend(order) {
                this.$alert('是否将配送方式改为快递配送?', '提示', {
                    confirmButtonText: '确定',
                    showCancelButton: true,
                    type: 'warning',
                    callback: action => {
                        if (action == 'confirm') {
                            this.openDialog(order)
                            this.addressVisible = true;
                        }
                    }
                });
            },
            printTeplate(htmlData) {
                myWindow = window.open('', '_blank');
                myWindow.document.write(htmlData);
                myWindow.focus();
            },
        }
    })
</script>