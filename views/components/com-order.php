<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-23
 * Time: 21:10
 */
Yii::$app->loadComponentView('order/com-search');
Yii::$app->loadComponentView('order/com-edit-address');
Yii::$app->loadComponentView('order/com-edit-seller-remark');
Yii::$app->loadComponentView('order/com-clerk');
Yii::$app->loadComponentView('order/com-send');
Yii::$app->loadComponentView('order/com-cancel');
Yii::$app->loadComponentView('order/com-edit-price');
Yii::$app->loadComponentView('order/com-city');
?>
<style>
    .com-order-list .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .com-order-list .header-box {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 10px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }

    .com-order-list .header-box .title {
        display: inline-block;
    }

    .com-order-list .addPrice {
        color: #5CB85C;
    }

    .com-order-list .com-order-item .el-button {
        padding: 0;
    }

    .com-order-list .change .el-input__inner {
        height: 22px !important;
        line-height: 22px !important;
    }

    .com-order-list .price-item {
        color: #888888;
    }

    .com-order-list .price-item .el-form-item {
        margin-bottom: 0 !important;
    }

    .com-order-list .important {
        color: #ff4544;
    }

    .com-order-list .com-order-list .goods-info {
        padding: 5px;
        font-size: 12px;
        color: #353535;
        text-align: left;
    }

    .com-order-list .com-order-list .goods-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .com-order-list .el-date-editor .el-range-separator {
        width: auto;
    }

    .com-order-list .goods-image {
        height: 90px;
        width: 90px;
        margin-right: 15px;
        float: left;
    }

    .com-order-list .com-order-item {
        margin-top: 20px;
        min-width: 750px;
    }

    .com-order-list .com-order-item:hover {
        border: 1px solid #3399FF;
    }

    .com-order-list .com-order-item:hover .com-order-del {
        display: block;
    }

    .com-order-del {
        position: absolute;
        top: 20px;
        right: 25px;
        color: #7C868D;
        font-size: 18px;
        padding: 0;
        display: none;
    }

    .com-order-list .com-order-item .el-card__header {
        padding: 0;
    }

    .com-order-list .com-order-head {
        padding: 20px;
        background-color: #F3F5F6;
        color: #303133;
        min-width: 750px;
        display: flex;
        position: relative;
    }

    .com-order-list .com-order-time {
        color: #909399;
    }

    .com-order-user {
        margin-left: 30px;
    }

    .com-order-user img {
        height: 20px;
        width: 20px;
        display: block;
        float: left;
        border-radius: 50%;
        margin-right: 10px;
    }

    .com-order-offline {
        margin-left: 30px;
        margin-top: -2px;
    }

    .com-order-offline .el-tag {
        margin-right: 5px;
    }

    .com-order-refund-status {
        position: absolute;
        bottom: 19px;
        left: 20px;
        height: 20px;
        width: 90px;
        z-index: 5;
        background-color: #FF7171;
        color: #fff;
        text-align: center;
    }

    .cancel {
        margin-left: 10px;
    }

    .com-order-list .com-order-item .cancel .el-button {
        padding: 5px;
    }

    .com-order-list .el-card__body {
        padding: 0;
    }

    .com-order-body {
        display: flex;
        flex-wrap: nowrap;
    }

    .com-order-list .goods-item {
        border-right: 1px solid #EBEEF5;
    }

    .com-order-list .goods-item .goods {
        position: relative;
        padding: 20px;
        min-height: 130px;
        border-top: 1px solid #EBEEF5;
    }

    .com-order-list .goods-item .goods:first-of-type {
        border-top: 0;
    }

    .com-order-list .goods-item .goods-info {
        width: 50%;
        margin-top: 5px;
    }

    .com-order-list .goods-item .goods-info .goods-name {
        margin-bottom: 5px;
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .com-order-list .goods-item .goods .com-order-goods-price {
        height: 24px;
        margin-top: 10px;
        position: absolute;
        bottom: 20px;
        left: 125px;
    }

    .com-order-list .com-order-info {
        display: flex;
        align-items: center;
        width: 15%;
        text-align: center;
        border-right: 1px solid #EBEEF5;
    }

    .com-order-list .com-order-info > div {
        width: 100%;
    }

    .com-order-list .express-price {
        height: 30px;
        line-height: 30px;
    }

    .com-order-title {
        background-color: #F3F5F6;
        height: 40px;
        line-height: 40px;
        display: flex;
        min-width: 750px;
    }

    .com-order-title div {
        text-align: center;
    }

    .com-order-icon {
        margin-right: 5%;
        margin-bottom: 10px;
        cursor: pointer;
    }

    .com-order-icon:last-of-type {
        margin-right: 0;
    }

    .com-order-list .remark-box {
        padding-top: 3px;
        margin-left: 7px;
    }

    /*表格底部样式 start*/
    .com-order-list .card-footer {
        background: #F3F5F6;
        padding: 10px 20px;
    }

    .com-order-list .card-footer .address-box {
        margin-right: 10px;
    }

    .com-order-list .card-footer .seller-remark {
        margin-top: 10px;
        color: #E6A23C;
    }

    /*表格底部样式 end*/

    .com-order-list .express-send-box {
        position: relative;
        overflow: hidden;
        border-radius: 4px;
        height: 24px;
    }

    .com-order-list .express-send-box .triangle {
        width: 0;
        height: 0;
        border-right: 23px solid rgba(0, 0, 0, 0);
        border-top: 23px solid red;
        position: relative;
        top: -24px;
    }

    .com-order-list .express-send-box .triangle .text {
        font-size: 10px;
        color: #ffffff;
        position: absolute;
        top: -25px;
    }

    .express-single-box {
        margin-bottom: 10px;
    }

    .express-single-box .goods-pic {
        width: 35px;
        height: 35px;
        margin: 0 4px;
    }

    .express-single-box .label {
        margin-right: 10px;
    }
</style>
<template id="com-order-list" ref="appOrder">
    <div class="com-order-list" style="margin-bottom: 20px;">
        <div class="header-box">
            <div class="title">
                <slot name="orderTitle">订单列表</slot>
            </div>
            <com-export-dialog
                    style="float: right;margin-top: -5px"
                    :field_list='export_list'
                    :action_url="'<?= Yii::$app->request->baseUrl . '/index.php?r=' ?>' + orderUrl"
                    :params="search">
            </com-export-dialog>
            <el-button
                    v-if="isShowRecycle"
                    style="float: right; margin: -5px 20px"
                    :loading="submitLoading"
                    type="primary"
                    size="small"
                    @click="toRecycleAll">清空回收站
            </el-button>
        </div>
        <div class="table-body">
            <com-search
                    @search="toSearch"
                    :plugins="plugins"
                    :tabs="tabs"
                    :active-name="activeName"
                    :is-show-order-type="isShowOrderType"
                    :is-show-order-plugin="isShowOrderPlugin"
                    :new-search="newSearch"
                    :select-list="selectList">
            </com-search>
            <com-edit-address
                    @close="dialogClose"
                    @submit="dialogSubmit"
                    :is-show="addressVisible"
                    :order="newOrder">
            </com-edit-address>
            <com-edit-seller-remark
                    :url="editRemarkUrl"
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
                    :express-id="expressId"
                    :send-type="sendType"
                    :order="newOrder">
            </com-send>
            <com-cancel
                    @close="dialogClose"
                    @submit="dialogSubmit"
                    :is-show="cancelVisible"
                    :cancel-type="cancelType"
                    :order="newOrder">
            </com-cancel>
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

            <div class="com-order-title">
                <div v-for="(item,index) in orderTitle" :key="index" :style="{width: item.width}">{{item.name}}</div>
            </div>

            <div v-loading="loading" v-if="list && list.length > 0">
                <el-card
                        v-for="item in list"
                        class="com-order-item"
                        :key="item.id"
                        shadow="never">
                    <div slot="header" class="com-order-head" flex="cross:center">
                        <div class="com-order-time">{{ item.created_at }}</div>
                        <div class="com-order-user">
                            <span class="com-order-time">订单号：</span>{{ item.order_no }}
                        </div>
                        <div class="com-order-user" flex="cross:center">
                            <img src="statics/img/mall/ali.png" v-if="item.platform == 'aliapp'" alt="">
                            <img src="statics/img/mall/wx.png" v-else-if="item.platform == 'wxapp'" alt="">
                            <img src="statics/img/mall/toutiao.png" v-else-if="item.platform == 'ttapp'" alt="">
                            <img src="statics/img/mall/baidu.png" v-else-if="item.platform == 'bdapp'" alt="">
                            <span>{{ item.nickname }}({{ item.user_id }})</span>
                        </div>
                        <div flex="cross:center" class="remark-box" v-if="item.remark != '' || item.words != ''">
                            <el-tooltip effect="dark" placement="bottom">
                                <div slot="content">
                                    <span v-if="item.remark">买家下单留言:{{item.remark}}</span>
                                    <br v-if="item.remark"/>
                                    <span v-if="item.words">商家订单留言:{{item.words}}</span>
                                    <br v-if="item.words"/>
                                    <span v-for="(deItem, deIndex) in item.detailExpress" :key="deItem.id">
                                        <span v-if="deItem.merchant_remark">
                                            卖家物流留言: {{deItem.merchant_remark}}
                                        </span>
                                        <br v-if="deItem.merchant_remark"/>
                                    </span>
                                </div>
                                <div v-if="item.remark || item.is_show_merchant_remark">
                                    <img src="statics/img/mall/order/remark.png" alt="">
                                </div>
                            </el-tooltip>
                        </div>
                        <div class="com-order-offline" flex="dir:left wrap:wrap">
                            <template v-if="item.cancel_status == 2 && item.status == 1 && isShowCancel">
                                <!-- 用户申请取消 -->
                                <div class="cancel" flex="wrap:wrap">
                                    <span style="margin-right: 5px;">用户正在申请取消该订单</span>
                                    <el-button type="success" size="mini" @click="agreeCancel(item,1)">同意</el-button>
                                    <el-button type="danger" size="mini" @click="agreeCancel(item,0)">拒绝</el-button>
                                </div>
                            </template>
                            <template v-else-if="isShowOrderStatus">
                                <div v-if="item.send_type == 0" class="express-send-box">
                                    <el-tag size="small">快递发送</el-tag>
                                    <div v-if="item.store_id > 0" class="triangle">
                                        <el-tooltip class="item" effect="dark" content="该订单由门店自提改成快递发送" placement="top">
                                            <span class="text">改</span>
                                        </el-tooltip>
                                    </div>
                                </div>
                                <el-tag size="small" v-if="item.send_type == 1">到店自提</el-tag>
                                <el-tag size="small" v-if="item.send_type == 2">同城配送</el-tag>
                                <el-tag size="small" type="warning" v-if="item.is_pay == 0">未付款</el-tag>
                                <el-tag size="small" type="warning" v-if="item.is_pay == 1 && item.is_send == 0">已付款
                                </el-tag>
                                <el-tag size="small" type="success"
                                    部分发货
                                </el-tag>
                                <el-tag size="small" type="success"
                                        v-if="item.is_send == 0 && item.is_pay == 1 && item.detailExpress && item.detailExpress.length == 0">
                                    未发货
                                </el-tag>
                                <el-tag size="small" type="success" v-if="item.is_send == 1 && item.is_confirm == 0">
                                    已发货
                                </el-tag>
                                <el-tag size="small" type="success" v-if="item.is_confirm == 0 && item.is_send == 1">
                                    未收货
                                </el-tag>
                                <el-tag size="small" type="success" v-if="item.is_sale == 1">已完成</el-tag>
                                <el-tag size="small" type="danger" v-if="item.cancel_status == 1">已取消</el-tag>
                                <el-tag size="small" type="danger" v-else-if="item.cancel_status == 2">申请取消</el-tag>
                                <slot name="orderTag" :order="item"></slot>
                            </template>
                        </div>

                        <el-button
                                v-if="isShowCancel && item.is_send == 0 && item.cancel_status == 0 && item.is_cancel_show == 1 && item.status == 1"
                                style="right: 60px"
                                class="com-order-del"
                                @click="agreeCancel(item,2)"
                                type="text">
                            <el-tooltip class="item" effect="dark" content="强制取消" placement="top">
                                <img src="statics/img/mall/order/force.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-if="isShowRecycle && item.is_recycle == 0"
                                   class="com-order-del"
                                   @click="toRecycle(item)"
                                   type="text">
                            <el-tooltip class="item" effect="dark" content="放入回收站" placement="top">
                                <img src="statics/img/mall/order/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </div>
                    <div class="com-order-body">
                        <!-- 订单信息 -->
                        <div class="goods-item" :style="{width: orderTitle[0].width}">
                            <div class="goods" v-for="goods in item.detail">
                                <img :src="goods.goods_info && goods.goods_info.goods_attr && goods.goods_info.goods_attr.pic_url ? goods.goods_info.goods_attr.pic_url : goods.goods_info.goods_attr.cover_pic"
                                     class="goods-image">
                                <span v-if="goods.refund_status == '11' || goods.refund_status == '10' || goods.refund_status == '12'"
                                      class="com-order-refund-status">售后中</span>

                                <span v-else-if="goods.refund_status == '20'" class="com-order-refund-status">已退款</span>

                                <span v-else-if="goods.refund_status == '21'" class="com-order-refund-status">已拒绝</span>

                                <div flex="dir:left">
                                    <div class="goods-info">
                                        <div class="goods-name">
                                            <com-ellipsis :line="2">
                                                <el-tag style="margin-right: 5px"
                                                        v-if="goods.plugin_name != null"
                                                        size="mini"
                                                        type="warning" hit>
                                                    {{goods.mch && goods.mch.id > 0 ?
                                                    goods.mch.store.name+'('+goods.mch.id+')'
                                                    :goods.plugin_name}}
                                                </el-tag>
                                                {{goods.goods_info && goods.goods_info.goods_attr &&
                                                goods.goods_info.goods_attr.name ?
                                                goods.goods_info.goods_attr.name : goods.goods.goodsWarehouse.name}}
                                            </com-ellipsis>
                                        </div>
                                        <div style="margin-bottom: 24px;">
                                            <span style="margin-right: 10px;">
                                                <slot name="attr" :item="item">
                                                    规格：
                                                <el-tag size="mini"
                                                        style="margin-right: 5px;"
                                                        v-for="attr in goods.attr_list"
                                                        :key="attr.id">
                                                    {{attr.attr_group_name}}:{{attr.attr_name}}
                                                </el-tag>
                                                </slot>
                                            </span>
                                        </div>
                                        <div class="com-order-goods-price">
                                            <span v-if="goods.goods_info && goods.goods_info.goods_attr &&
                                             goods.goods_info.goods_attr.no">
                                                货号：{{goods.goods_info.goods_attr.no}}
                                            </span>
                                        </div>
                                    </div>
                                    <div style="width: 250px" flex="dir:left box:mean">
                                        <div flex="cross:center main:center">
                                            <span>小计：￥{{goods.total_original_price}}</span>
                                            <el-button type="text"
                                                       style="margin-left: 3px;"
                                                       v-if="isShowEditSinglePrice && item.is_pay == 0 && item.is_send == 0 && search.status != 5"
                                                       circle
                                                       @click="changeGoods(goods)">
                                                <img src="statics/img/mall/order/edit.png" alt="">
                                            </el-button>
                                        </div>
                                        <div flex="cross:center main:center">数量：x {{goods.num}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div flex="cross:center" class="com-order-info" :style="{width:orderTitle[1].width}">
                            <div flex="dir:top">
                                <div>
                                    <span style="font-size: 16px">￥{{item.total_pay_price}}</span>
                                    <el-popover
                                            placement="bottom"
                                            width="250"
                                            trigger="hover">
                                        <el-form class="price-item" label-width="100px" :model="item">
                                            <el-form-item label="商品小计">
                                                <span>{{ item.total_goods_original_price }}元</span>
                                            </el-form-item>
                                            <el-form-item label="会员优惠" v-if="item.member_discount_price != 0.00">
                                        <span class="important" v-if="item.member_discount_price > 0">
                                            -{{ item.member_discount_price }}元
                                        </span>
                                                <span class="addPrice" v-if="item.member_discount_price < 0">
                                            +{{ -item.member_discount_price }}元
                                        </span>
                                            </el-form-item>
                                            <el-form-item label="积分抵扣" v-if="item.score_deduction_price != 0.00">
                                                <span class="important">-{{ item.score_deduction_price }}元</span>
                                            </el-form-item>
                                            <el-form-item label="优惠券抵扣" v-if="item.coupon_discount_price != 0.00">
                                                <span class="important">-{{ item.coupon_discount_price }}元</span>
                                            </el-form-item>
                                            <el-form-item label="后台改价" v-if="item.back_price != 0.00">
                                                <span class="important" v-if="item.back_price > 0">-{{ item.back_price }}元</span>
                                                <span class="addPrice" v-else>+{{ -item.back_price }}元</span>
                                            </el-form-item>
                                            <el-form-item label="运费改价" v-if="item.express_price != 0">
                                                <span class="important"
                                                      v-if="item.express_price > item.express_original_price">
                                                    +{{item.express_price - item.express_original_price}}元
                                                </span>
                                                <span class="addPrice" v-else>
                                                    -{{item.express_price - item.express_original_price }}元
                                                </span>
                                            </el-form-item>
                                            <el-form-item v-if="item.plugin_data" v-for="pluginData in item.plugin_data"
                                                          :label="pluginData.label" :key="pluginData.label">
                                                <span class="important">{{pluginData.value}}元</span>
                                            </el-form-item>
                                        </el-form>
                                        <img src="statics/img/mall/order/price.png" slot="reference" alt="">
                                    </el-popover>
                                    <slot name="other" :item="item"></slot>
                                </div>
                                <div class="express-price">
                            <span>
                                <span style="color: #909399">(含运费￥{{item.express_price}})</span>
                                <el-button type="text"
                                           v-if="isShowEditExpressPrice && item.is_pay == 0 && item.is_send == 0 && search.status != 5"
                                           circle
                                           @click="openDialog(item, changePriceVisible = true)">
                                    <img src="statics/img/mall/order/edit.png" alt="">
                                </el-button>
                            </span>
                                </div>
                                <div>
                                    <el-tag size="mini" color="#E6A23C" style="color:#fff;border:0"
                                            v-if="item.pay_type == 1">在线支付
                                    </el-tag>
                                    <el-tag size="mini" color="#E6A23C" style="color:#fff;border:0"
                                            v-if="item.pay_type == 3">余额支付
                                    </el-tag>
                                    <el-tag size="mini" color="#E6A23C" style="color:#fff;border:0"
                                            v-if="item.pay_type == 2">货到付款
                                    </el-tag>
                                </div>
                            </div>
                        </div>
                        <div v-if="isShowAction" class="com-order-info" :style="{width:orderTitle[2].width}"
                             style="padding: 10px;border-right: 0;">
                            <div flex="wrap:wrap cross:center">
                                <!-- 结束 -->
                                <el-tooltip class="item" effect="dark" content="结束订单" placement="top">
                                    <img class="com-order-icon" @click="saleOrder(item.id)"
                                         v-if="item.is_recycle == 0 && item.is_confirm == 1 && item.is_sale == 0 && isShowFinish && item.status != 0"
                                         src="statics/img/mall/order/sale.png" alt="">
                                </el-tooltip>
                                <!-- 确认收货 -->
                                <el-tooltip class="item" effect="dark" content="确认收货" placement="top">
                                    <img class="com-order-icon" src="statics/img/mall/order/confirm.png" alt=""
                                         v-if="item.is_recycle == 0 && item.is_send == 1 && item.is_confirm == 0 && isShowConfirm && item.status != 0 && item.is_confirm_show"
                                         @click="confirm(item.id)">
                                </el-tooltip>
                                <el-tooltip class="item" effect="dark" content="电子面单" placement="top">
                                    <img class="com-order-icon" src="statics/img/mall/order/express_single.png" alt=""
                                         v-if="item.new_express_single && item.new_express_single.length > 0"
                                         @click="expressSingle(item.new_express_single)">
                                </el-tooltip>
                                <!-- 核销 -->
                                <el-tooltip class="item" effect="dark" content="核销订单" placement="top">
                                    <img class="com-order-icon" @click="openDialog(item, clerkVisible = true)"
                                         v-if="item.send_type == 1 && (item.is_pay == 1 || item.pay_type == 2) && item.clerk == null && item.is_send == 0 && item.is_clerk_show && item.is_recycle == 0 && isShowClerk && item.is_recycle == 0 && item.status != 0 && item.cancel_status != 1"
                                         src="statics/img/mall/order/clerk.png" alt="">
                                </el-tooltip>

                                <template v-if="">
                                    <slot name="orderSend" :order="item"></slot>
                                    <!-- 发货 -->
                                    <el-tooltip class="item" effect="dark" content="发货" placement="top">
                                        <img class="com-order-icon" @click="openExpress(item,'send')"
                                             v-if="item.is_send_show"
                                             src="statics/img/mall/order/send.png" alt="">
                                    </el-tooltip>
                                    <!-- 同城配送发货 选择配送员 -->    <!-- 到店自提也可发货 -->
                                    <!-- <el-tooltip class="item" effect="dark" content="发货" placement="top">
                                         <img class="com-order-icon" @click="openCity(item,'send')"
                                              v-if="item.send_type == 2 && item.is_send == 0 && item.cancel_status != 1 && (item.is_pay == 1 || item.pay_type == 2) && item.is_send_show == 1 && isShowSend && item.is_recycle == 0 && item.status != 0"
                                              src="statics/img/mall/order/send.png" alt="">
                                     </el-tooltip>

                                     <el-tooltip class="item" effect="dark" content="发货" placement="top">
                                         <img class="com-order-icon" @click="storeOrderSend(item)"
                                              v-if="item.send_type == 1 && (item.is_pay == 1 || item.pay_type == 2) && item.is_send == 0 && item.cancel_status != 1 && item.is_send_show == 1 && isShowSend && item.is_recycle == 0 && item.status != 0"
                                              src="statics/img/mall/order/send.png" alt="">
                                     </el-tooltip> -->
                                </template>
                                <!-- 打印小票 -->

                                <el-tooltip class="item" effect="dark" content="打印小票" placement="top">
                                    <img class="com-order-icon"
                                         v-if="item.is_recycle == 0 && isShowPrint"
                                         @click="print(item.id)"
                                         src="statics/img/mall/order/print.png" alt="">
                                </el-tooltip>
                                <!-- 恢复订单 -->
                                <el-tooltip class="item" effect="dark" content="恢复订单" placement="top">
                                    <img class="com-order-icon" v-if="item.is_recycle == 1"
                                         @click="toRecycle(item)"
                                         src="statics/img/mall/order/renew.png" alt="">
                                </el-tooltip>
                                <!-- 删除订单 -->
                                <el-tooltip class="item" effect="dark" content="删除订单" placement="top">
                                    <img class="com-order-icon" v-if="item.is_recycle == 1"
                                         @click="toDelete(item)"
                                         src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                                <!-- 备注 -->
                                <el-tooltip class="item" effect="dark"
                                            :content="item.seller_remark != '' || item.bonus_remark ? '修改备注' : '添加备注'"
                                            placement="top">
                                    <img class="com-order-icon" @click="openDialog(item, sellerRemarkVisible = true)"
                                         v-if="item.is_recycle == 0 && isShowRemark"
                                         src="statics/img/mall/order/add_remark.png">
                                </el-tooltip>
                                <!-- 修改快递单号 -->
                                <template v-if="item.send_type != 2">
                                    <template
                                            v-if="item.detailExpress && item.detailExpress.length == 1 && item.is_send == 1 && item.cancel_status != 1 && item.is_confirm == 0 && item.is_recycle == 0 && isShowSend && item.status != 0">
                                        <el-tooltip class="item" effect="dark" content="修改快递单号" placement="top">
                                            <img class="com-order-icon"
                                                 @click="openExpress(item,'change', item.detailExpress[0].id)"
                                                 src="statics/img/mall/order/change.png" alt="">
                                        </el-tooltip>
                                    </template>
                                    <!--多个物流信息订单 需到订单详情修改 -->
                                    <template
                                            v-else-if="item.detailExpress && item.detailExpress.length >= 1 && item.cancel_status != 1 && item.is_confirm == 0 && item.is_recycle == 0 && isShowSend && item.status != 0">
                                        <el-tooltip class="item" effect="dark" content="修改快递单号" placement="top">
                                            <img class="com-order-icon"
                                                 @click="openExpressHint" src="statics/img/mall/order/change.png"
                                                 alt="">
                                        </el-tooltip>
                                    </template>
                                </template>
                                <el-tooltip class="item" effect="dark" content="修改配送员" placement="top">
                                    <img class="com-order-icon"
                                         v-if="item.send_type == 2 && item.cancel_status != 1 && item.is_confirm == 0 && item.city_info && item.is_recycle == 0 && isShowSend && item.status != 0"
                                         @click="openCity(item,'change')" src="statics/img/mall/order/change.png"
                                         alt="">
                                </el-tooltip>
                                <!-- 订单详情 -->
                                <el-tooltip class="item" effect="dark" content="查看订单详情" placement="top">
                                    <img v-if="isShowDetail" class="com-order-icon" @click="toDetail(item.id)"
                                         src="statics/img/mall/order/detail.png"
                                         alt="">
                                </el-tooltip>
                            </div>
                        </div>
                        <!--目前用于分销-->
                        <slot name="orderAction" :order="item"></slot>
                    </div>
                    <div class="card-footer">
                        <template v-if="item.send_type == 1">
                            <div flex="cross:center">
                                <el-tag style="margin-right: 10px;" size="small" hit type="warning">到店自提</el-tag>
                                <span class="address-box" v-if="item.store">门店名称：{{item.store.name}} 电话：{{item.store.mobile}} 地址：{{item.store.address}}</span>
                            </div>
                            <div style="margin: 10px 0;">收货人: {{item.name}} 电话：{{item.mobile}}</div>
                        </template>
                        <div v-else-if="(item.send_type == 0 || item.send_type == 2) && item.address">
                            <div flex="dir:left">
                                <div class="address-box">收货人: {{item.name}} 电话：{{item.mobile}} 地址：{{item.address}}</div>
                                <el-button
                                        v-if="isShowEditAddress == 1 && item.send_type != 2 && item.cancel_status == 0 && item.is_send==0"
                                        type="text"
                                        icon="el-icon-edit"
                                        circle
                                        @click="openDialog(item, addressVisible = true)">
                                </el-button>
                            </div>
                        </div>
                        <slot name="footerFirst" :item="item"></slot>
                        <div class="seller-remark" v-if="item.seller_remark">商家备注：{{item.seller_remark}}</div>
                        <slot name="footer" :item="item"></slot>
                    </div>
                </el-card>
            </div>
            <el-card v-loading="loading" shadow="never" class="com-order-item"
                     style="height: 100px;line-height: 100px;text-align: center;"
                     v-if="list && list.length == 0">
                暂无订单信息
            </el-card>
            <div style="margin-top: 15px">
                <el-pagination
                        v-if="pagination"
                        style="display: inline-block;float: right;"
                        background
                        :page-count="pagination.page_count"
                        :current-page="pagination.current_page"
                        @current-change="pageChange"
                        layout="prev, pager, next">
                </el-pagination>
            </div>
        </div>

        <el-dialog
                title="电子面单"
                :visible.sync="singleDialogVisible"
                width="30%">
            <div v-for="(expressSingle, index) in newExpressSingle"
                 :key="index"
                 class="express-single-box" flex="dir:left">
                <div>
                    <div class="label" style="background: #fffaef;color: #e6a23c;padding: 3px 0;">
                        收货信息:{{index + 1}}
                    </div>
                </div>
                <div flex="dir:top">
                    <div flex="cross:center">
                        <template v-if="expressSingle.send_type == 1">
                            <el-tag style="margin-right: 5px;" type="info" hit size="small">{{
                                expressSingle.express }}
                            </el-tag>
                            <a :href="'https://www.baidu.com/s?wd='+ expressSingle.express + expressSingle.express_no"
                               target="_blank" title='点击搜索运单号'>{{ expressSingle.express_no }}</a>
                        </template>
                        <template v-else>
                            <span>{{expressSingle.express_content}}</span>
                        </template>
                        <el-button @click="printTeplate(expressSingle.print_teplate)" v-if="expressSingle.print_teplate"
                                   style="margin-left: 10px;" size="mini" type="default">打印此面单
                        </el-button>
                    </div>
                    <div flex="dir:left" style="margin-top: 10px;">
                        <span class="label">配送商品:</span>
                        <img v-for="(goods, index) in expressSingle.goods_list"
                             :key="index"
                             class="goods-pic"
                             :src="goods.cover_pic">
                    </div>
                </div>
            </div>
        </el-dialog>

        <el-dialog width="25%" title="修改商品价格" :visible.sync="editGoodsPriceVisible">
            <el-form :model="editGoodsForm" ref="goodsValidateForm" label-width="80px" size="small">
                <el-form-item
                        label="商品价格"
                        prop="total_price"
                        :rules="[{ required: true, message: '价格不能为空'}]">
                    <el-input type="number" v-model="editGoodsForm.total_price" auto-complete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="editGoodsPriceVisible = false">取 消</el-button>
                <el-button size="small" :loading="submitLoading" type="primary"
                           @click="changePrice('goodsValidateForm')">确 定
                </el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('com-order', {
        template: '#com-order-list',
        props: {
            orderTitle: {
                type: Array,
                default: function () {
                    return [
                        {width: '60%', name: '订单信息'},
                        {width: '20%', name: '实付金额'},
                        {width: '20%', name: '操作'}
                    ]
                }
            },
            selectList: {
                type: Array,
                default: function () {
                    return [
                        {value: '1', name: '订单号'},
                        {value: '9', name: '商户单号'},
                        {value: '2', name: '用户名'},
                        {value: '4', name: '用户ID'},
                        {value: '5', name: '商品名称'},
                        {value: '3', name: '收货人'},
                        {value: '6', name: '收货人电话'},
                        {value: '7', name: '门店名称'},
                        {value: 'goods_no', name: '商品货号'},
                    ]
                }
            },
            tabs: {
                type: Array,
                default: function () {
                    return [
                        {value: '-1', name: '全部'},
                        {value: '0', name: '未付款'},
                        {value: '1', name: '待发货'},
                        {value: '2', name: '待收货'},
                        {value: '3', name: '已完成'},
                        {value: '4', name: '待处理'},
                        {value: '5', name: '已取消'},
                        {value: '7', name: '回收站'},
                    ]
                }
            },
            activeName: {
                type: String,
                default: '-1',
            },
            // 订单列表请求URL
            orderUrl: {
                type: String,
                default: 'mall/order/index',
            },
            // 删除回收站请求URl
            recycleUrl: {
                type: String,
                default: 'mall/order/destroy-all',
            },
            // 订单详情URL
            orderDetailUrl: {
                type: String,
                default: 'mall/order/detail'
            },
            // 修改备注请求URL
            editRemarkUrl: {
                type: String,
                default: 'mall/order/seller-remark'
            },
            // 订单数量总数
            orderCountUrl: {
                type: String,
                default: 'mall/order/order-count'
            },
            // 控制按钮是否显示
            // 编辑收货地址
            isShowEditAddress: {
                type: Boolean,
                default: true
            },
            // 订单取消操作
            isShowCancel: {
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
            // 清空回收站
            isShowRecycle: {
                type: Boolean,
                default: true
            },
            // 订单配送方式
            isShowOrderType: {
                type: Boolean,
                default: true
            },
            // 订单配送方式
            isShowOrderStatus: {
                type: Boolean,
                default: true
            },
            // 订单详情
            isShowDetail: {
                type: Boolean,
                default: true
            },
            // 操作按钮
            isShowAction: {
                type: Boolean,
                default: true
            },
            // 修改运费
            isShowEditExpressPrice: {
                type: Boolean,
                default: true
            },
            // 修改商品小计
            isShowEditSinglePrice: {
                type: Boolean,
                default: true
            },
            // 插件筛选
            isShowOrderPlugin: {
                type: Boolean,
                default: false
            },
            newSearch: {
                type: Object,
                default: function () {
                    return {
                        time: null,
                        keyword: '',
                        keyword_1: '1',
                        date_start: '',
                        date_end: '',
                        platform: '',
                        status: '',
                        plugin: 'all',
                        send_type: -1,
                    }
                }
            },
        },
        data() {
            return {
                search: {},
                submitLoading: false,
                // 新的
                loading: false,
                list: [],
                pagination: null,
                newOrder: {},// 传给各子组件的订单信息
                addressVisible: false,// 修改收货地址
                sellerRemarkVisible: false,// 添加商户备注
                clerkVisible: false,// 订单核销
                sendVisible: false,// 发货
                sendType: '',// 发货类型
                cancelVisible: false,// 订单取消
                cancelType: -1,// 订单取消状态 同意|拒绝
                changePriceVisible: false,// 修改订单价格
                plugins: [
                    {
                        name: '全部订单',
                        sign: 'all',
                    }
                ],// 插件筛选
                export_list: [],//导出字段数据,
                // 修改商品单价 start
                editGoodsPriceVisible: false,//修改商品单价
                editGoodsForm: {
                    total_price: '',
                    id: 0,
                },//价格
                // 修改商品单价 end
                expressId: 0,// 修改物流
                citySendVisible: false, //同城配送发货
                singleDialogVisible: false,// 电子面单弹框
                newExpressSingle: [],
            };
        },
        mounted() {
            this.search = this.newSearch;
            // 用户列表 用户订单数
            if (getQuery('user_id') > 0) {
                this.search.keyword_1 = '4';
                this.search.keyword = getQuery('user_id')
            }
            if (getQuery('clerk_id') > 0) {
                this.search.clerk_id = getQuery('clerk_id');
            }
            if(localStorage.getItem('order_page')){
                this.search.page = localStorage.getItem('order_page');
            }
            this.getList();
        },
        methods: {
            // 关闭弹出框
            closeDialog() {
                this.submitLoading = false;
                this.id = null;
            },
            // 进入商品详情
            toDetail(id) {
                var path = window.location.origin + window.location.pathname + '?r=mall%2Forder%2Fdetail&order_id=' + id;
                window.open(path,'_blank');
                // return;
                // this.$navigate({
                //     r: this.orderDetailUrl,
                //     order_id: id
                // })
            },
            // 确认收货
            confirm(id) {
                this.$confirm('是否确认收货?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order/confirm',
                        },
                        data: {
                            order_id: id
                        },
                        method: 'post',
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
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
                }).catch(e => {
                });
            },
            // 结束订单
            saleOrder(id) {
                this.$confirm('是否结束该订单?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order/order-sales',
                        },
                        data: {
                            order_id: id
                        },
                        method: 'post',
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'error'
                            });
                        }
                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
            // 打印小票
            print(id) {
                this.$confirm('是否打印小票?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then(() => {
                    this.loading = true;
                    request({
                        params: {
                            r: 'mall/order/order-print',
                            order_id: id
                        },
                        method: 'get',
                    }).then(e => {
                        this.loading = false;
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
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
            changeGoods(e) {
                this.editGoodsPriceVisible = true;
                this.editGoodsForm.total_price = e.total_original_price;
                this.editGoodsForm.id = e.id;
            },
            // 修改商品金额
            changePrice(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.submitLoading = true;
                        request({
                            params: {
                                r: 'mall/order/update-price',
                            },
                            data: {
                                order_detail_id: this.editGoodsForm.id,
                                total_price: this.editGoodsForm.total_price
                            },
                            method: 'post',
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.editGoodsPriceVisible = false
                                this.$message({
                                    message: '修改成功',
                                    type: 'success'
                                });
                                this.getList();
                            } else {
                                this.$message({
                                    message: e.data.msg,
                                    type: 'warning'
                                });
                            }
                        }).catch(e => {
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            // 回收站
            toRecycle(e) {
                let that = this;
                let text = "是否放入回收站(可在回收站中恢复)?"
                let para = {
                    order_id: e.id,
                    is_recycle: 1
                }
                if (e.is_recycle == 1) {
                    para.is_recycle = 0;
                    text = "是否移出回收站?"
                }
                this.$confirm(text, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order/recycle',
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        e.visible = false;
                        this.submitLoading = false;
                        if (e.data.code === 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
                        }
                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
            expressSingle(newExpressSingle) {
                this.singleDialogVisible = true
                this.newExpressSingle = newExpressSingle;
            },
            printTeplate(htmlData) {
                myWindow = window.open('', '_blank');
                myWindow.document.write(htmlData);
                myWindow.focus();
            },
            // 删除订单
            toDelete(e) {
                this.$confirm('是否删除订单？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'mall/order/destroy',
                        },
                        data: {
                            order_id: e.id,
                        },
                        method: 'post'
                    }).then(e => {
                        this.loading = false;
                        if (e.data.code == 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
                        }

                    }).catch(e => {
                    });
                }).catch(() => {
                });
            },
            // 获取订单列表
            getList() {
                this.loading = true;
                let params = {
                    r: this.orderUrl
                };
                Object.keys(this.search).map((key) => {
                    params[key] = this.search[key]
                });

                request({
                    params: params,
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        console.log(this.list);
                        this.export_list = e.data.data.export_list;
                        this.pagination = e.data.data.pagination;
                        this.plugins = e.data.data.plugins;
                    }
                }).catch(e => {
                });
            },
            // com-search组件 搜索事件
            toSearch(searchParams) {
                this.search = searchParams;
                this.search.page = 1;
                this.getList();
            },
            // 分页
            pageChange(page) {
                this.search.page = page;
                localStorage.setItem('order_page',page);
                this.getList();
            },
            openDialog(order) {
                this.newOrder = order;
            },
            dialogClose() {
                this.addressVisible = false;
                this.sellerRemarkVisible = false;
                this.clerkVisible = false;
                this.sendVisible = false;
                this.changePriceVisible = false;
                this.cancelVisible = false;
                this.citySendVisible = false;
            },
            dialogSubmit() {
                this.expressId = 0;
                this.getList();
            },
            // 发货
            openExpress(order, type, expressId) {
                this.newOrder = order;
                this.sendType = type;
                this.sendVisible = true;
                this.expressId = parseInt(expressId);
            },
            // 申请取消订单
            agreeCancel(row, status) {
                this.newOrder = row;
                this.cancelType = status;
                this.cancelVisible = true;
            },
            // 清空回收站
            toRecycleAll(e) {
                this.$confirm('此操作将清空回收站, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true
                }).then(() => {
                    this.submitLoading = true;
                    request({
                        params: {
                            r: this.recycleUrl,
                        },
                        data: {},
                        method: 'post',
                    }).then(e => {
                        e.visible = false;
                        this.submitLoading = false;
                        if (e.data.code === 0) {
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                            this.getList();
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'warning'
                            });
                        }
                    }).catch(e => {
                        this.submitLoading = false;
                    });
                }).catch(() => {
                });
            },
            openExpressHint() {
                this.$alert('该订单有多个物流,请到订单详情修改物流信息', '提示', {
                    confirmButtonText: '确定',
                    callback: action => {
                    }
                });
            },
            openCity(order, sendType) {
                this.newOrder = order;
                this.sendType = sendType
                this.citySendVisible = true;
            },
            storeOrderSend(order) {
                this.$alert('是否将配送方式改为快递配送？', '提示', {
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
            }
        },
    });
</script>
