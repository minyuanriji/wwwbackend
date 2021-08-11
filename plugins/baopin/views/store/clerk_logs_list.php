<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/baopin/mall/store/list'})">
                        门店进货
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/baopin/mall/store/goods-list', store_id:store.id})">
                        {{store.name}}
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>{{goods_info.name}}</el-breadcrumb-item>
            </el-breadcrumb>
            <span></span>
        </div>
        <div class="table-body">

            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>

            <el-tabs v-model="activeName" @tab-click="handleClick">

                <el-table @sort-change="sortReload" :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column prop="id" width="90" label="编号ID"></el-table-column>
                    <el-table-column prop="order_no" width="210" label="订单号"></el-table-column>
                    <el-table-column prop="updated_at" label="核销日期" width="210"></el-table-column>
                    <el-table-column label="补货">
                        <template slot-scope="scope">
                            <div style="color:gray" v-if="scope.row.express_status==0">未补货</div>
                            <div style="color:#03C5FF" v-else>
                                <div>已补货</div>
                                <div v-if="scope.row.send_type == 1">
                                    <div>快递：{{scope.row.express}}</div>
                                    <div>单号：{{scope.row.express_no}}</div>
                                </div>
                                <div v-if="scope.row.send_type == 2">
                                    <div>物流内容：</div>
                                    <div>{{scope.row.express_content}}</div>
                                </div>
                            </div>
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
            store: {id:0, name:""},
            goods_info: {name:""},
            pagination: null,
            dialogLoading: false,
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
                    r: 'plugin/baopin/mall/store/clerk-logs-list',
                    goods_id: getQuery("goods_id"),
                    store_id: getQuery("store_id")
                };
                params = Object.assign(params, this.search);
                var self = this;
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        self.pagination = e.data.data.pagination;
                        self.store = e.data.data.store;
                        self.goods_info = e.data.data.goods_info;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.loading = false;
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
            handleSelectionChange(){}
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