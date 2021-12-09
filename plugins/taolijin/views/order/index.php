<?php
echo $this->render("com-new-order");
echo $this->render("com-edit-order");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>订单管理</span>
            <div style="float: right;margin-top: -5px">
                <el-button @click="NewOrder.dialogVisible=true" type="primary" size="small">订单录入</el-button>
            </div>
        </div>
        <div class="table-body">

            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="淘宝联盟" name="ali"></el-tab-pane>
                <el-tab-pane label="京东联盟" name="jd"></el-tab-pane>
            </el-tabs>

            <div class="input-item" style="margin-top:5px;">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>

            <el-table @sort-change="sortReload" :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                      @selection-change="handleSelectionChange">
                <el-table-column prop="id" width="90" label="ID"></el-table-column>
                <el-table-column width="200" label="联盟信息">
                    <template slot-scope="scope">
                        <div>类型：
                            <b v-if="scope.row.ali_type == 'ali'">淘宝联盟</b>
                            <b v-if="scope.row.ali_type == 'jd'">京东联盟</b>
                        </div>
                        <div>名称：{{scope.row.ali_name}}</div>
                        <div>编号：{{scope.row.ali_id}}</div>
                    </template>
                </el-table-column>
                <el-table-column width="200" label="订单信息">
                    <template slot-scope="scope">
                        <div>单号：{{scope.row.ali_order_sn}}</div>
                        <div>日期：{{scope.row.ali_created_at}}</div>
                        <div>状态：
                            <span style="color:green" v-if="scope.row.status_i.status == 'paid' || scope.row.status_i.status == 'finished'">{{scope.row.status_i.text}}</span>
                            <span v-else>{{scope.row.status_i.text}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column width="200" label="联盟商品信息">
                    <template slot-scope="scope">
                        <div style="display: flex;align-items: center">
                            <div style="display:inline-block;width:50px;height:50px;">
                                <com-image mode="aspectFill" :src="scope.row.ali_item_pic"></com-image>
                            </div>
                            <div style="padding-left:10px;">
                                <el-tooltip class="item" effect="dark" placement="top">
                                    <template slot="content">
                                        <div style="width: 320px;">{{scope.row.ali_item_name}}</div>
                                    </template>
                                    <com-ellipsis :line="1">{{scope.row.ali_item_name}}</com-ellipsis>
                                </el-tooltip>
                                <div style="color:darkred">单价：{{scope.row.ali_item_price}}元</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column width="200" label="用户信息">
                    <template slot-scope="scope">
                        <div style="display: flex;align-items: center">
                            <div style="display:inline-block;width:50px;height:50px;">
                                <com-image mode="aspectFill" :src="scope.row.avatar_url"></com-image>
                            </div>
                            <div style="padding-left:10px;">
                                <el-tooltip class="item" effect="dark" placement="top">
                                    <template slot="content">
                                        <div style="width: 320px;">{{scope.row.nickname}}</div>
                                    </template>
                                    <com-ellipsis :line="1">{{scope.row.nickname}}</com-ellipsis>
                                </el-tooltip>
                                <div>用户ID：{{scope.row.user_id}}</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column width="200" label="支付信息">
                    <template slot-scope="scope">
                        <div v-if="scope.row.pay_status == 'refund'" style="color: gray">已退款</div>
                        <div v-if="scope.row.pay_status == 'refunding'" style="color: gray">退款中</div>
                        <div v-if="scope.row.pay_status == 'unpaid'" style="color: gray">-</div>
                        <div v-if="scope.row.pay_status == 'paid'">
                            <div>实付：{{scope.row.pay_price}}元</div>
                            <div v-if="scope.row.pay_status == 'paid'">
                                日期：{{scope.row.pay_at}}
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column width="200" label="佣金信息">
                    <template slot-scope="scope">
                        <div style="color:darkred">比例：{{scope.row.ali_commission_rate}}%</div>
                        <div style="color:darkgreen">佣金：{{scope.row.ali_commission_price}}元</div>
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="editIt(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="deleteIt(scope.row)" type="text" circle size="mini" v-if="scope.row.status_i.status == 'paid'">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/nopass.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
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

    </el-card>

    <com-new-order :visible="NewOrder.dialogVisible" @close="NewOrder.dialogVisible=false"></com-new-order>
    <com-edit-order :visible="EditOrder.dialogVisible"
                    :order-item="EditOrder.orderItem"
                    @close="EditOrder.dialogVisible=false"></com-edit-order>

</div>

<script>
    const app = new Vue({
        el: '#app',
        data: {
            loading: false,
            list: [],
            pagination: null,
            search: {
                ali_id: 0,
                ali_type: 'ali',
                keyword: '',
                page: 1,
                sort_prop: '',
                sort_type: '',
            },
            selections: [],
            activeName: 'ali',
            NewOrder: {
                dialogVisible: false
            },
            EditOrder:{
                dialogVisible: false,
                orderItem: {}
            }
        },
        mounted() {
            this.search.ali_id = getQuery("ali_id");
            this.loadData();
        },
        methods: {
            editIt(item){
                this.EditOrder.orderItem = item;
                this.EditOrder.dialogVisible = true;
            },
            deleteIt(item){
                let self = this;
                self.$confirm('确定要删除订单？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.loading = true;
                    request({
                        params: {
                            r: 'plugin/taolijin/mall/order/delete'
                        },
                        method: 'post',
                        data: {
                            order_id: item.id
                        }
                    }).then(e => {
                        self.loading = false;
                        if (e.data.code == 0) {
                            self.loadData();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.loading = false;
                        self.$message.error("请求失败");
                    });
                }).catch(() => {

                });
            },
            sortReload(column){
                this.search.sort_prop = column.prop;
                this.search.sort_type = column.order == "descending" ? 0 : 1;
                this.loadData();
            },
            handleSelectionChange(val) {
                this.selections = val;
            },
            handleClick(tab, event) {
                this.search.page = 1;
                this.search.ali_type = this.activeName;
                this.loadData()
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/taolijin/mall/order/list'
                };
                params = Object.assign(params, this.search);
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            }
        }
    });
</script>
<style>
    .el-tabs__header {
        font-size: 16px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
        display: inline-block;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .batch {
        margin: 0 0 20px;
        display: inline-block;
    }

    .batch .el-button {
        padding: 9px 15px !important;
    }
</style>