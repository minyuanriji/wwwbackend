<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>核销记录</span>
        </div>
        <div class="table-body">

            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>

            <div style="float: right"></div>
            <el-tabs v-model="activeName" @tab-click="handleClick">

                <el-table @sort-change="sortReload" :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column align='center' type="selection" width="60"></el-table-column>
                    <el-table-column align="center" sortable="custom" prop="id" label="ID" width="90"></el-table-column>
                    <el-table-column align="center" label="门店" width="70">
                        <template slot-scope="scope">
                            <div v-if="scope.row.clerk_role=='store'" style="color:green">是</div>
                            <div v-else>否</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="核销员" prop="nickname"></el-table-column>
                    <el-table-column label="订单信息" width="350">
                        <template slot-scope="scope">
                            <div v-for="(orderDetail, key, index) in scope.row.orderDetail" flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="orderDetail.goods_info.goods_attr.cover_pic"></com-image>
                                </div>
                                <div flex="cross:top cross:center">
                                    <div style="flex-grow: 3">
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{orderDetail.goods_info.goods_attr.name}}</div>
                                            </template>
                                            <com-ellipsis :line="2">{{orderDetail.goods_info.goods_attr.name}}</com-ellipsis>
                                        </el-tooltip>
                                    </div>
                                    <div style="text-align:right;flex-grow: 1;font-weight:bold;color:#999">数量 x {{orderDetail.num}}</div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="类型" width="70"  align="center">
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
                            <el-link type="default" :underline="false" >
                                <el-tooltip class="item" effect="dark" content="发货" placement="top">
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
            </el-tabs>
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


</div>



<script>
    const app = new Vue({
        el: '#app',
        data: {
            search: {
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
            selections: []
        },
        mounted() {
            this.loadData();
        },
        methods: {

            sortReload(column){
                this.search.sort_prop = column.prop;
                this.search.sort_type = column.order == "descending" ? 0 : 1;
                this.loadData();
            },

            loadData() {
                this.loading = true;
                let params = {
                    r: 'mall/order-clerk/index'
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