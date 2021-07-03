<?php
Yii::$app->loadComponentView('order/com-clerk-send');
?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/order-clerk/store'})">核销记录</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>{{store.name}}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="table-body">

            <com-clerk-send
                    @close="dialogClose"
                    @submit="dialogSubmit"
                    :order-detail-ids="orderDetailIds"
                    :is-show="clerkSendVisible"></com-clerk-send>

            <el-select size="small" @change="toSearch" v-model="search.order_type" placeholder="请选择" style="width:110px;">
                <el-option :label="'全部类型'" :value="''"></el-option>
                <el-option :label="'爆品'" :value="'offline_baopin'"></el-option>
                <el-option :label="'商品'" :value="'offline_normal'"></el-option>
            </el-select>

            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>

            <div class="input-item" style="float:right;text-align:right">
                <el-button @click="batchClerkSend" type="primary" size="small" >批量补货</el-button>
            </div>

            <div style="padding:10px 10px;margin-bottom:10px;background:#f7f7f7;border:1px solid #eee;">
                <span style="margin-right:5px;">商户：{{store.name}}</span>
                <span style="margin-right:5px;">手机：{{store.mobile}}</span>
                <span>地址：{{store.address}}</span>
            </div>

            <el-tabs v-model="search.express_status" @tab-click="toSearch">

                <el-tab-pane label="待补货" name="no_express">
                    <el-table @sort-change="sortReload" :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column align='center' type="selection" width="60"></el-table-column>
                    <el-table-column align="center" sortable="custom" prop="id" label="ID" width="90"></el-table-column>
                    <el-table-column label="商品" >
                        <template slot-scope="scope">
                            <div style="padding-right: 10px;">
                                <com-image mode="aspectFill" :src="scope.row.goods_info.goods_attr.cover_pic"></com-image>
                            </div>
                            <div flex="cross:top cross:center">
                                <div style="flex-grow: 3">
                                    <el-tooltip class="item" effect="dark" placement="top">
                                        <template slot="content">
                                            <div style="width: 320px;">{{scope.row.goods_info.goods_attr.name}}</div>
                                        </template>
                                        <com-ellipsis :line="2">{{scope.row.goods_info.goods_attr.name}}</com-ellipsis>
                                    </el-tooltip>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="num" label="数量" width="110"></el-table-column>
                    <el-table-column prop="stock_num" label="库存" width="110"></el-table-column>
                    <el-table-column prop="total_stock" label="固定库存" width="110"></el-table-column>
                    <el-table-column label="类型" width="110" align="center">
                        <template slot-scope="scope">
                            <div v-if="scope.row.order_type == 'offline_baopin'">爆品</div>
                            <div v-else>商品</div>
                        </template>
                    </el-table-column>
                    <el-table-column sortable="custom" prop="created_at" label="核销时间" width="110"  align="center">
                        <template slot-scope="scope">
                            {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="130" align="center">
                        <template slot-scope="scope">
                            <el-link @click="clerkSend(scope.row)" type="default" :underline="false" >
                                <el-tooltip class="item" effect="dark" content="补货" placement="top">
                                    <img class="com-order-icon" src="statics/img/mall/order/send.png" alt="">
                                </el-tooltip>
                            </el-link>
                            <el-link style="margin-left:10px;" type="default" :underline="false" :href="'?r=mall/order-clerk/detail&id='+scope.row.id" target="_blank">
                                <el-tooltip class="item" effect="dark" content="查看详情" placement="top">
                                    <img class="com-order-icon" src="statics/img/mall/order/detail.png" alt="">
                                </el-tooltip>
                            </el-link>
                        </template>
                    </el-table-column>
                </el-table>
                </el-tab-pane>

                <el-tab-pane label="已补货" name="is_express">
                    <el-table @sort-change="sortReload" :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;">
                        <el-table-column align="center" sortable="custom" prop="id" label="ID" width="90"></el-table-column>
                        <el-table-column label="商品" >
                            <template slot-scope="scope">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.goods_info.goods_attr.cover_pic"></com-image>
                                </div>
                                <div flex="cross:top cross:center">
                                    <div style="flex-grow: 3">
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{scope.row.goods_info.goods_attr.name}}</div>
                                            </template>
                                            <com-ellipsis :line="2">{{scope.row.goods_info.goods_attr.name}}</com-ellipsis>
                                        </el-tooltip>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="物流信息" >
                            <template slot-scope="scope">
                                <div v-if="scope.row.send_type == 1">
                                    <div>快递：{{scope.row.express}}</div>
                                    <div>单号：{{scope.row.express_no}}</div>
                                </div>
                                <div v-if="scope.row.send_type == 2">
                                    <div>物流内容：</div>
                                    <div>{{scope.row.express_content}}</div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="类型" width="70" align="center">
                            <template slot-scope="scope">
                                <div v-if="scope.row.order_type == 'offline_baopin'">爆品</div>
                                <div v-else>商品</div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="num" label="数量" width="110"></el-table-column>
                        <el-table-column prop="stock_num" label="库存" width="110"></el-table-column>
                        <el-table-column prop="total_stock" label="固定库存" width="110"></el-table-column>
                        <el-table-column sortable="custom" prop="created_at" label="核销时间" width="110"  align="center">
                            <template slot-scope="scope">
                                {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                            </template>
                        </el-table-column>
                    </el-table>

                </el-tab-pane>

            </el-tabs>



            <div>
                <div flex="box:last cross:center">
                    <div></div>
                    <div>
                        <el-pagination
                                v-if="list.length > 0"
                                style="display: inline-block;float: right;"
                                background :page-size="pagination.pageSize"
                                @current-change="pageChange"
                                layout="prev, pager, next" :current-page="pagination.current_page"
                                :total="pagination.total_count">
                        </el-pagination>
                    </div>
                </div>
            </div>


        </div>

    </el-card>


</div>



<script>
    const app = new Vue({
        el: '#app',
        data: {
            search: {
                order_type: '',
                express_status: getQuery('express_status'),
                keyword: '',
                page: 1,
                platform: '',
                sort_prop: '',
                sort_type: '',
            },
            loading: false,
            activeName: '-1',
            list: [],
            pagination: null,
            edit: {
                show: false,
            },
            selections: [],
            clerkSendVisible: false,
            orderDetailIds: [],
            store: {name: ''}
        },
        mounted() {
            this.loadData();
        },
        methods: {

            dialogSubmit() {
                this.toSearch();
            },

            dialogClose() {
                this.clerkSendVisible = false;
                this.orderDetailIds = [];
            },

            batchClerkSend(){
                if(this.selections.length <= 0){
                    this.$message.error("请选择要操作的订单");
                    return;
                }
                var orderDetailIds = [];
                for(var i=0; i < this.selections.length; i++){
                    orderDetailIds.push(this.selections[i].id);
                }
                this.orderDetailIds = orderDetailIds;
                this.clerkSendVisible = true;
            },

            clerkSend(row){
                this.orderDetailIds = [parseInt(row.id)];
                this.clerkSendVisible = true;
            },

            sortReload(column){
                this.search.sort_prop = column.prop;
                this.search.sort_type = column.order == "descending" ? 0 : 1;
                this.loadData();
            },

            loadData() {
                this.loading = true;
                let params = {
                    r: 'mall/order-clerk/index',
                    store_id: getQuery('store_id')
                };
                params = Object.assign(params, this.search);
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.store = e.data.data.store;
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            handleClick(tab, event) {
                this.search.page = 1;
                this.search.status = this.activeName;
                this.loadData()
            },
            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            handleSelectionChange(val) {
                this.selections = val;
            }
        }
    });
</script>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }


    .input-item {
        width: 250px;
        margin: 0 0 20px;
        display: inline-block;
    }

</style>