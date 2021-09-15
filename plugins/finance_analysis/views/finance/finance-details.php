<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadComponentView('statistics/com-search');
//Yii::$app->loadComponentView('statistics/com-header');
?>
<style>
    .table-body {
        background-color: #fff;
        position: relative;
        margin-bottom: 10px;
        border: 1px solid #EBEEF5;
    }

    .num-info {
        display: flex;
        width: 100%;
        height: 60px;
        color: #303133;
        margin: 20px 0;
    }

    .num-info .num-info-item {
        text-align: center;
        flex-grow: 1;
        border-left: 1px solid black;
    }

    .num-info .num-info-item:first-of-type {
        border-left: 0;
    }

    .info-item-name {
        color: #92959B;
    }

    #assets {
        font-size: 18px;
        color: #1ed0ff;
        margin-left: 10px
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <el-card shadow="never">
            <div slot="header">
                <div>
                    <com-image mode="aspectFill" style="float: left;margin-right: 8px;width: 35px;height: 35px" :src="countUser.avatar_url"></com-image>
                    <div style="font-size: 22px">{{countUser.nickname}}(ID:{{countUser.id}})的总资产</div>
                    <div>注册时间：{{countUser.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                </div>
            </div>
            <div class="num-info">
                <div class="num-info-item">
                    <div class="info-item-name">余额</div>
                    <div style="display: flex;justify-content: space-evenly">
                        <div>
                            <div>累计余额</div>
                            <div id="assets">{{countUser.total_balance}}元</div>
                        </div>
                        <div>
                            <div>当前余额</div>
                            <div id="assets">{{countUser.balance}}元</div>
                        </div>
                    </div>
                </div>
                <div class="num-info-item">
                    <div class="info-item-name">总收益（<span style="color: #1ed0ff;font-size: 18px">{{countUser.total_income}}</span>）</div>
                    <div style="display: flex;justify-content: space-evenly">
                        <div>
                            <div>已收益</div>
                            <div id="assets">{{countUser.income}}元</div>
                        </div>
                        <div>
                            <div>冻结收益</div>
                            <div id="assets">{{countUser.income_frozen}}元</div>
                        </div>
                    </div>
                </div>
                <div class="num-info-item">
                    <div class="info-item-name">红包</div>
                    <div id="assets" style="margin-top: 17px">{{countUser.static_integral}}</div>
                </div>
                <div class="num-info-item">
                    <div class="info-item-name">总积分（<span style="color: #1ed0ff;font-size: 18px">{{countUser.total_score}}</span>）</div>
                    <div style="display: flex;justify-content: space-evenly">
                        <div>
                            <div>永久积分</div>
                            <div id="assets">{{countUser.static_score}}</div>
                        </div>
                        <div>
                            <div>有效积分</div>
                            <div id="assets">{{countUser.score}}</div>
                        </div>
                    </div>
                </div>
                <div class="num-info-item">
                    <div class="info-item-name">购物券</div>
                    <div id="assets" style="margin-top: 17px">{{countUser.money ?? 0}}</div>
                </div>
            </div>
        </el-card>
        <div class="table-body">
            <com-search
                    @to-search="toSearch"
                    @search="searchList"
                    :new-search="search"
                    :is-show-platform="false"
                    :is-show-keyword="false"
                    :day-data="{'today':today, 'weekDay': weekDay, 'monthDay': monthDay}">
            </com-search>
        </div>
            <el-card shadow="never">
                <div slot="header">
                    <span>实时资产
                    <el-tooltip class="item" effect="dark"
                        content="根据时间、关键词变化,默认当天"
                        placement="top">
                        <i class="el-icon-info"></i>
                    </el-tooltip>
                    </span>
                </div>
                <div class="num-info">
                    <div class="num-info-item">
                        <div class="info-item-name">余额</div>
                        <div style="display: flex;justify-content: space-evenly">
                            <div>
                                <div>收入</div>
                                <div id="assets">{{balance.incomeBalance}}</div>
                            </div>
                            <div>
                                <div>支出</div>
                                <div id="assets">{{balance.expenditureBalance}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="num-info-item">
                        <div class="info-item-name">收益</div>
                        <div style="display: flex;justify-content: space-evenly">
                            <div>
                                <div>冻结</div>
                                <div id="assets">{{income.frozenBalance}}元</div>
                            </div>
                            <div>
                                <div>结算</div>
                                <div id="assets">{{income.settlementBalance}}元</div>
                            </div>
                            <div>
                                <div>收入</div>
                                <div id="assets">{{income.incomeBalance}}元</div>
                            </div>
                            <div>
                                <div>支出</div>
                                <div id="assets">{{income.expenditureBalance}}元</div>
                            </div>
                        </div>
                    </div>
                    <div class="num-info-item">
                        <div class="info-item-name">红包</div>
                        <div style="display: flex;justify-content: space-evenly">
                            <div>
                                <div>收入</div>
                                <div id="assets">{{RedPacket.incomeRedPacket}}</div>
                            </div>
                            <div>
                                <div>支出</div>
                                <div id="assets">{{RedPacket.expenditureRedPacket}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="num-info-item">
                        <div style="display: flex;justify-content: space-evenly">
                            <div style="width: 50%">
                                <div style="color: #92959B">永久积分</div>
                                <div style="display: flex;justify-content: space-between">
                                    <div style="width: 50%">
                                        <div>收入</div>
                                        <div id="assets">{{Integral.incomePermanentIntegral}}</div>
                                    </div>
                                    <div style="width: 50%">
                                        <div>支出</div>
                                        <div id="assets">{{Integral.expenditurePermanentIntegral}}</div>
                                    </div>
                                </div>
                            </div>
                            <div style="width: 50%">
                                <div style="color: #92959B">限时积分</div>
                                <div style="display: flex;justify-content: space-between">
                                    <div style="width: 50%">
                                        <div>收入</div>
                                        <div id="assets">{{Integral.incomeDynamicIntegral}}</div>
                                    </div>
                                    <div style="width: 50%">
                                        <div>支出</div>
                                        <div id="assets">{{Integral.expenditureDynamicDisIntegral}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="num-info-item">
                        <div class="info-item-name">购物券</div>
                        <div style="display: flex;justify-content: space-evenly">
                            <div>
                                <div>收入</div>
                                <div id="assets">{{ShoppingVoucher.incomeShoppingVoucher}}</div>
                            </div>
                            <div>
                                <div>支出</div>
                                <div id="assets">{{ShoppingVoucher.expenditureShoppingVoucher}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </el-card>
        <div class="table-body" style="padding: 20px">

            <el-tabs v-model="search.type" @tab-click="tab_assets">
                <el-tab-pane label="收益" name="income"></el-tab-pane>
                <el-tab-pane label="红包" name="RedPacket"></el-tab-pane>
                <el-tab-pane label="积分" name="Integral"></el-tab-pane>
                <el-tab-pane label="购物券" name="ShoppingVoucher"></el-tab-pane>
                <el-tab-pane label="余额" name="balance"></el-tab-pane>
            </el-tabs>

            <div v-if="tab_index == 0" style="display: flex;justify-content: space-evenly">
                <div style="width: 40%">
                    <div style="text-align: center;font-size: 18px!important;color: #1ed0ff">收入</div>
                    <el-table v-if="tab_index == 0"
                              v-loading="list_loading"
                              :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}"
                              :data="income.incomeList">
                        <el-table-column prop="id" label="id"></el-table-column>
                        <el-table-column prop="income" label="收益"></el-table-column>
                        <el-table-column prop="money" label="总收益"></el-table-column>
                        <el-table-column prop="desc" label="说明">
                            <template slot-scope="scope">
                                <div v-if="scope.row.source_type == 'goods'">
                                    {{scope.row.desc}}--<div @click="$navigate({r: 'mall/order/index', order_no:scope.row.order_no, keyword_1:1})" style="color: red">跳转</div>
                                </div>
                                <div v-else>{{scope.row.desc}}</div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="created_at" label="时间">
                            <template slot-scope="scope">
                                <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="margin-top: 10px;" flex="box:last cross:center">
                        <div>
                            <el-pagination
                                    v-if="income.incomePagination"
                                    style="display: inline-block;float: right;"
                                    background
                                    :page-size="income.incomePagination.pageSize"
                                    @current-change="incomePagination_pageChange"
                                    layout="prev, pager, next"
                                    :current-page="income.incomePagination.current_page"
                                    :total="income.incomePagination.total_count">
                            </el-pagination>
                        </div>
                    </div>
                </div>

                <div style="width: 40%">
                    <div  style="text-align: center;font-size: 18px!important;color: #1ed0ff">支出</div>
                    <el-table v-if="tab_index == 0" v-loading="list_loading" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}" :data="income.expenditureList">
                        <el-table-column prop="id" label="id"></el-table-column>
                        <el-table-column prop="income" label="收益"></el-table-column>
                        <el-table-column prop="money" label="总收益"></el-table-column>
                        <el-table-column prop="desc" label="说明"></el-table-column>
                        <el-table-column prop="created_at" label="时间">
                            <template slot-scope="scope">
                                <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="margin-top: 10px;" flex="box:last cross:center">
                        <div>
                            <el-pagination
                                    v-if="income.expenditurePagination"
                                    style="display: inline-block;float: right;"
                                    background
                                    :page-size="income.expenditurePagination.pageSize"
                                    @current-change="expenditurePagination_pageChange"
                                    layout="prev, pager, next"
                                    :current-page="income.expenditurePagination.current_page"
                                    :total="income.expenditurePagination.total_count">
                            </el-pagination>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="tab_index == 1" style="display: flex;justify-content: space-evenly">
                <div style="width: 40%">
                    <div style="text-align: center;font-size: 18px!important;color: #1ed0ff">收入</div>
                    <el-table v-if="tab_index == 1"
                              v-loading="list_loading"
                              :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}"
                              :data="RedPacket.incomeList">
                        <el-table-column prop="id" label="id"></el-table-column>
                        <el-table-column prop="integral" label="收益"></el-table-column>
                        <el-table-column prop="current_integral" label="总收益"></el-table-column>
                        <el-table-column prop="desc" label="说明"></el-table-column>
                        <el-table-column prop="created_at" label="时间">
                            <template slot-scope="scope">
                                <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="margin-top: 10px;" flex="box:last cross:center">
                        <div>
                            <el-pagination
                                    v-if="RedPacket.incomePagination"
                                    style="display: inline-block;float: right;"
                                    background
                                    :page-size="RedPacket.incomePagination.pageSize"
                                    @current-change="RedPacket_incomePagination_pageChange"
                                    layout="prev, pager, next"
                                    :current-page="RedPacket.incomePagination.current_page"
                                    :total="RedPacket.incomePagination.total_count">
                            </el-pagination>
                        </div>
                    </div>
                </div>

                <div style="width: 40%">
                    <div  style="text-align: center;font-size: 18px!important;color: #1ed0ff">支出</div>
                    <el-table v-if="tab_index == 1" v-loading="list_loading" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}" :data="RedPacket.expenditureList">
                        <el-table-column prop="id" label="id"></el-table-column>
                        <el-table-column prop="integral" label="收益"></el-table-column>
                        <el-table-column prop="current_integral" label="总收益"></el-table-column>
                        <el-table-column prop="desc" label="说明"></el-table-column>
                        <el-table-column prop="created_at" label="时间">
                            <template slot-scope="scope">
                                <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="margin-top: 10px;" flex="box:last cross:center">
                        <div>
                            <el-pagination
                                    v-if="RedPacket.expenditurePagination"
                                    style="display: inline-block;float: right;"
                                    background
                                    :page-size="RedPacket.expenditurePagination.pageSize"
                                    @current-change="RedPacket_expenditurePagination_pageChange"
                                    layout="prev, pager, next"
                                    :current-page="RedPacket.expenditurePagination.current_page"
                                    :total="RedPacket.expenditurePagination.total_count">
                            </el-pagination>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="tab_index == 2" style="display: flex;justify-content: space-evenly">
                <div style="width: 48%">
                    <div style="color: #92959B;text-align: center">永久积分</div>
                    <div style="display: flex;justify-content: space-between">
                        <div style="width: 48%">
                            <div style="text-align: center;font-size: 18px!important;color: #1ed0ff">收入</div>
                            <el-table v-if="tab_index == 2"
                                      v-loading="list_loading"
                                      :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}"
                                      :data="Integral.incomeList">
                                <el-table-column prop="id" label="id"></el-table-column>
                                <el-table-column prop="score" label="收益"></el-table-column>
                                <el-table-column prop="current_score" label="总收益"></el-table-column>
                                <el-table-column prop="desc" label="说明"></el-table-column>
                                <el-table-column prop="created_at" label="时间">
                                    <template slot-scope="scope">
                                        <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div style="margin-top: 10px;" flex="box:last cross:center">
                                <div>
                                    <el-pagination
                                            v-if="Integral.incomePagination"
                                            style="display: inline-block;float: right;"
                                            background
                                            :page-size="Integral.incomePagination.pageSize"
                                            @current-change="Integral_incomePagination_pageChange"
                                            layout="prev, pager, next"
                                            :current-page="Integral.incomePagination.current_page"
                                            :total="Integral.incomePagination.total_count">
                                    </el-pagination>
                                </div>
                            </div>
                        </div>
                        <div style="width: 48%">
                            <div  style="text-align: center;font-size: 18px!important;color: #1ed0ff">支出</div>
                            <el-table v-if="tab_index == 2" v-loading="list_loading" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}" :data="Integral.expenditureList">
                                <el-table-column prop="id" label="id"></el-table-column>
                                <el-table-column prop="score" label="收益"></el-table-column>
                                <el-table-column prop="current_score" label="总收益"></el-table-column>
                                <el-table-column prop="desc" label="说明"></el-table-column>
                                <el-table-column prop="created_at" label="时间">
                                    <template slot-scope="scope">
                                        <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div style="margin-top: 10px;" flex="box:last cross:center">
                                <div>
                                    <el-pagination
                                            v-if="Integral.expenditurePagination"
                                            style="display: inline-block;float: right;"
                                            background
                                            :page-size="Integral.expenditurePagination.pageSize"
                                            @current-change="Integral_expenditurePagination_pageChange"
                                            layout="prev, pager, next"
                                            :current-page="Integral.expenditurePagination.current_page"
                                            :total="Integral.expenditurePagination.total_count">
                                    </el-pagination>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="width: 48%">
                    <div style="color: #92959B;text-align: center">限时积分</div>
                    <div style="display: flex;justify-content: space-between">
                        <div style="width: 48%">
                            <div style="text-align: center;font-size: 18px!important;color: #1ed0ff">收入</div>
                            <el-table v-if="tab_index == 2"
                                      v-loading="list_loading"
                                      :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}"
                                      :data="Integral.incomeDynamicList">
                                <el-table-column prop="id" label="id"></el-table-column>
                                <el-table-column prop="money" label="收益"></el-table-column>
                                <el-table-column prop="before_money" label="总收益"></el-table-column>
                                <el-table-column prop="desc" label="说明"></el-table-column>
                                <el-table-column prop="created_at" label="时间">
                                    <template slot-scope="scope">
                                        <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div style="margin-top: 10px;" flex="box:last cross:center">
                                <div>
                                    <el-pagination
                                            v-if="Integral.incomeDynamicPagination"
                                            style="display: inline-block;float: right;"
                                            background
                                            :page-size="Integral.incomeDynamicPagination.pageSize"
                                            @current-change="Integral_incomeDynamicPagination_pageChange"
                                            layout="prev, pager, next"
                                            :current-page="Integral.incomeDynamicPagination.current_page"
                                            :total="Integral.incomeDynamicPagination.total_count">
                                    </el-pagination>
                                </div>
                            </div>
                        </div>
                        <div style="width: 48%">
                            <div  style="text-align: center;font-size: 18px!important;color: #1ed0ff">支出</div>
                            <el-table v-if="tab_index == 2" v-loading="list_loading" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}" :data="Integral.expenditureDynamicList">
                                <el-table-column prop="id" label="id"></el-table-column>
                                <el-table-column prop="money" label="收益"></el-table-column>
                                <el-table-column prop="before_money" label="总收益"></el-table-column>
                                <el-table-column prop="desc" label="说明"></el-table-column>
                                <el-table-column prop="created_at" label="时间">
                                    <template slot-scope="scope">
                                        <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div style="margin-top: 10px;" flex="box:last cross:center">
                                <div>
                                    <el-pagination
                                            v-if="Integral.expenditureDynamicPagination"
                                            style="display: inline-block;float: right;"
                                            background
                                            :page-size="Integral.expenditureDynamicPagination.pageSize"
                                            @current-change="Integral_expenditureDynamicPagination_pageChange"
                                            layout="prev, pager, next"
                                            :current-page="Integral.expenditureDynamicPagination.current_page"
                                            :total="Integral.expenditureDynamicPagination.total_count">
                                    </el-pagination>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="tab_index == 3" style="display: flex;justify-content: space-evenly">
                <div style="width: 40%">
                    <div style="text-align: center;font-size: 18px!important;color: #1ed0ff">收入</div>
                    <el-table v-if="tab_index == 3"
                              v-loading="list_loading"
                              :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}"
                              :data="ShoppingVoucher.incomeList">
                        <el-table-column prop="id" label="id"></el-table-column>
                        <el-table-column prop="money" label="收益"></el-table-column>
                        <el-table-column prop="current_money" label="总收益"></el-table-column>
                        <el-table-column prop="desc" label="说明"></el-table-column>
                        <el-table-column prop="created_at" label="时间">
                            <template slot-scope="scope">
                                <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="margin-top: 10px;" flex="box:last cross:center">
                        <div>
                            <el-pagination
                                    v-if="ShoppingVoucher.incomePagination"
                                    style="display: inline-block;float: right;"
                                    background
                                    :page-size="ShoppingVoucher.incomePagination.pageSize"
                                    @current-change="ShoppingVoucher_incomePagination_pageChange"
                                    layout="prev, pager, next"
                                    :current-page="ShoppingVoucher.incomePagination.current_page"
                                    :total="ShoppingVoucher.incomePagination.total_count">
                            </el-pagination>
                        </div>
                    </div>
                </div>

                <div style="width: 40%">
                    <div  style="text-align: center;font-size: 18px!important;color: #1ed0ff">支出</div>
                    <el-table v-if="tab_index == 3" v-loading="list_loading" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}" :data="ShoppingVoucher.expenditureList">
                        <el-table-column prop="id" label="id"></el-table-column>
                        <el-table-column prop="money" label="收益"></el-table-column>
                        <el-table-column prop="current_money" label="总收益"></el-table-column>
                        <el-table-column prop="desc" label="说明"></el-table-column>
                        <el-table-column prop="created_at" label="时间">
                            <template slot-scope="scope">
                                <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="margin-top: 10px;" flex="box:last cross:center">
                        <div>
                            <el-pagination
                                    v-if="ShoppingVoucher.expenditurePagination"
                                    style="display: inline-block;float: right;"
                                    background
                                    :page-size="ShoppingVoucher.expenditurePagination.pageSize"
                                    @current-change="ShoppingVoucher_expenditurePagination_pageChange"
                                    layout="prev, pager, next"
                                    :current-page="ShoppingVoucher.expenditurePagination.current_page"
                                    :total="ShoppingVoucher.expenditurePagination.total_count">
                            </el-pagination>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="tab_index == 4" style="display: flex;justify-content: space-evenly">
                <div style="width: 40%">
                    <div style="text-align: center;font-size: 18px!important;color: #1ed0ff">收入</div>
                    <el-table v-if="tab_index == 4"
                              v-loading="list_loading"
                              :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}"
                              :data="balance.incomeList">
                        <el-table-column prop="id" label="id"></el-table-column>
                        <el-table-column prop="money" label="变动余额"></el-table-column>
                        <el-table-column prop="balance" label="当前余额"></el-table-column>
                        <el-table-column prop="desc" label="说明"></el-table-column>
                        <el-table-column prop="created_at" label="时间">
                            <template slot-scope="scope">
                                <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="margin-top: 10px;" flex="box:last cross:center">
                        <div>
                            <el-pagination
                                    v-if="balance.incomePagination"
                                    style="display: inline-block;float: right;"
                                    background
                                    :page-size="balance.incomePagination.pageSize"
                                    @current-change="balance_incomePagination_pageChange"
                                    layout="prev, pager, next"
                                    :current-page="balance.incomePagination.current_page"
                                    :total="balance.incomePagination.total_count">
                            </el-pagination>
                        </div>
                    </div>
                </div>

                <div style="width: 40%">
                    <div  style="text-align: center;font-size: 18px!important;color: #1ed0ff">支出</div>
                    <el-table v-if="tab_index == 4" v-loading="list_loading" :header-cell-style="{background:'#F3F5F6','color':'#303133',padding: '6px 0',fontWeight: '400'}" :data="balance.expenditureList">
                        <el-table-column prop="id" label="id"></el-table-column>
                        <el-table-column prop="money" label="变动余额"></el-table-column>
                        <el-table-column prop="balance" label="当前余额"></el-table-column>
                        <el-table-column prop="desc" label="说明"></el-table-column>
                        <el-table-column prop="created_at" label="时间">
                            <template slot-scope="scope">
                                <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div style="margin-top: 10px;" flex="box:last cross:center">
                        <div>
                            <el-pagination
                                    v-if="balance.expenditurePagination"
                                    style="display: inline-block;float: right;"
                                    background
                                    :page-size="balance.expenditurePagination.pageSize"
                                    @current-change="balance_expenditurePagination_pageChange"
                                    layout="prev, pager, next"
                                    :current-page="balance.expenditurePagination.current_page"
                                    :total="balance.expenditurePagination.total_count">
                            </el-pagination>
                        </div>
                    </div>
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
                isMessage: false,
                loading: false,
                list_loading: false,
                // 今天
                today: '',
                // 七天前
                weekDay: '',
                // 30天前
                monthDay: '',
                // 搜索内容
                search: {
                    time: null,
                    type: 'income',
                },
                balance_incomePagination_page:1,
                balance_expenditurePagination_page:1,
                incomePagination_page:1,
                expenditurePagination_page:1,
                RedPacket_incomePagination_page:1,
                RedPacket_expenditurePagination_page:1,
                Integral_incomePagination_page:1,
                Integral_expenditurePagination_page:1,
                Integral_incomeDynamicPagination_page:1,
                Integral_expenditureDynamicPagination_page:1,
                ShoppingVoucher_incomePagination_page:1,
                ShoppingVoucher_expenditurePagination_page:1,
                limit:10,
                countUser: "",
                balance: "",
                income: "",
                RedPacket: "",
                Integral: "",
                ShoppingVoucher: "",
                tab_index:0,
                user_id:0,
            };
        },
        methods: {
            // 获取数据
            getList() {
                this.loading = true;
                if (this.search.date_start) {
                    start_time = this.search.date_start;
                } else {
                    var startDate = new Date();
                    var sy = startDate.getFullYear();
                    var sm = startDate.getMonth()+1;
                    var sd = startDate.getDate();
                    start_time = sy+"-"+sm+"-"+sd+" 00:00:00";
                }

                if (this.search.date_start) {
                    end_time = this.search.date_end;
                } else {
                    var endDate = new Date();
                    var ey = endDate.getFullYear();
                    var em = endDate.getMonth()+1;
                    var ed = endDate.getDate();
                    var eh = endDate.getHours();
                    var emm = endDate.getMinutes();
                    var es = endDate.getSeconds();
                    end_time = ey+"-"+em+"-"+ed+" "+eh+":"+emm+":"+es;
                }
                request({
                    params: {
                        r: 'plugin/finance_analysis/mall/finance/finance-details',
                        user_id: getQuery('user_id') ? getQuery('user_id') : this.user_id,
                        type: this.search.type,
                        limit: this.limit,
                        start_time: start_time,
                        end_time: end_time,
                        balance_incomePagination_page: this.balance_incomePagination_page,
                        balance_expenditurePagination_page: this.balance_expenditurePagination_page,
                        incomePagination_page: this.incomePagination_page,
                        expenditurePagination_page: this.expenditurePagination_page,
                        RedPacket_incomePagination_page: this.RedPacket_incomePagination_page,
                        RedPacket_expenditurePagination_page: this.RedPacket_expenditurePagination_page,
                        Integral_incomePagination_page: this.Integral_incomePagination_page,
                        Integral_expenditurePagination_page: this.Integral_expenditurePagination_page,
                        Integral_incomeDynamicPagination_page: this.Integral_incomeDynamicPagination_page,
                        Integral_expenditureDynamicPagination_page: this.Integral_expenditureDynamicPagination_page,
                        ShoppingVoucher_incomePagination_page: this.ShoppingVoucher_incomePagination_page,
                        ShoppingVoucher_expenditurePagination_page: this.ShoppingVoucher_expenditurePagination_page,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.countUser = e.data.data.countUser;
                        this.balance = e.data.data.balance;
                        this.income = e.data.data.income;
                        this.RedPacket = e.data.data.RedPacket;
                        this.Integral = e.data.data.Integral;
                        this.ShoppingVoucher = e.data.data.ShoppingVoucher;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            // 切换
            tab_assets(e) {
                this.tab_index = e.index;
                this.getList();
            },

            toSearch(searchData) {
                this.search = searchData;
                this.page = 1;
                this.getList();
                this.tab_assets();
            },

            searchList(searchData) {
                this.search = searchData;
                this.page = 1;
                this.getList();
            },

            getTime(){
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

            open() {
                this.$prompt('请输入用户ID查询', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    /*inputPattern: /[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?/,
                    inputErrorMessage: '邮箱格式不正确'*/
                }).then(({ value }) => {
                    this.$message({
                        type: 'success',
                        message: '成功，将为您查询ID：' + value
                    });
                    this.user_id=value
                    this.getList()
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '取消输入及不能正常查询，如需再次查询，请刷新！'
                    });
                });
            },

            <!-- --------------------余额分页------------------ -->
            balance_incomePagination_pageChange(currentPage) {
                this.balance_incomePagination_page = currentPage;
                this.getList();
            },
            balance_expenditurePagination_pageChange(currentPage) {
                this.balance_expenditurePagination_page = currentPage;
                this.getList();
            },

            <!-- --------------------收益分页------------------ -->
            incomePagination_pageChange(currentPage) {
                this.incomePagination_page = currentPage;
                this.getList();
            },
            expenditurePagination_pageChange(currentPage) {
                this.e_expenditurePagination_page = currentPage;
                this.getList();
            },

            <!-- --------------------红包分页------------------ -->
            RedPacket_incomePagination_pageChange(currentPage) {
                this.RedPacket_incomePagination_page = currentPage;
                this.getList();
            },
            RedPacket_expenditurePagination_pageChange(currentPage) {
                this.RedPacket_expenditurePagination_page = currentPage;
                this.getList();
            },

            <!-- --------------------积分分页------------------ -->
            Integral_incomePagination_pageChange(currentPage) {
                this.Integral_incomePagination_page = currentPage;
                this.getList();
            },
            Integral_expenditurePagination_pageChange(currentPage) {
                this.Integral_expenditurePagination_page = currentPage;
                this.getList();
            },
            Integral_incomeDynamicPagination_pageChange(currentPage) {
                this.Integral_incomeDynamicPagination_page = currentPage;
                this.getList();
            },
            Integral_expenditureDynamicPagination_pageChange(currentPage) {
                this.Integral_expenditureDynamicPagination_page = currentPage;
                this.getList();
            },

            <!-- --------------------购物券分页------------------ -->
            ShoppingVoucher_incomePagination_pageChange(currentPage) {
                this.ShoppingVoucher_incomePagination_page = currentPage;
                this.getList();
            },
            ShoppingVoucher_expenditurePagination_pageChange(currentPage) {
                this.ShoppingVoucher_expenditurePagination_page = currentPage;
                this.getList();
            },
        },
        created() {
            this.getTime();
            if (this.user_id || getQuery('user_id')) {
                this.getList();
            } else {
                this.open();
            }
        }
    })
</script>