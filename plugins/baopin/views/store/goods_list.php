<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/baopin/mall/store/list'})">
                        门店进货
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>{{store.name}}</el-breadcrumb-item>
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
                    <el-table-column sortable="custom" prop="id" width="90" label="ID"></el-table-column>
                    <el-table-column prop="name" label="商品">
                        <template slot-scope="scope">
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.cover_pic"></com-image>
                                </div>
                                <div flex="cross:top cross:center">
                                    <div flex="dir:left">
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{scope.row.name}}</div>
                                            </template>
                                            <com-ellipsis :line="2">{{scope.row.name}}</com-ellipsis>
                                        </el-tooltip>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="库存（件）">
                        <template scope="scope">
                            <el-row>
                                <el-col :span="scope.row.id != edit_id ? 24 : 16">
                                    <el-input type="number" :disabled="scope.row.id != edit_id"  placeholder="请输入内容" v-model="scope.row.stock_num" >
                                        <el-button @click="editStock(scope.row)" v-if="scope.row.id != edit_id" slot="append" icon="el-icon-edit-outline"></el-button>
                                    </el-input>
                                </el-col>
                                <el-col :span="8" v-if="scope.row.id == edit_id">
                                    <el-button type="text" @click="saveStore(scope.row)"><img src="statics/img/mall/pass.png" alt=""></el-button>
                                    <el-button type="text" @click="edit_id=0"><img src="statics/img/mall/nopass.png" alt=""></el-button>
                                 </el-col>
                            </el-row>

                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="clerkLogsLink(scope.row)">
                                <el-tooltip class="item" effect="dark" content="核销记录" placement="top">
                                    <img src="statics/img/mall/icon-show.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="deleteGoods(scope.row)">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>

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
            store: {},
            pagination: null,
            dialogLoading: false,
            edit_id:0,
            selections: []
        },
        mounted() {
            this.loadData();
        },
        methods: {

            clerkLogsLink(row){
                navigateTo({
                    r: 'plugin/baopin/mall/store/clerk-logs-list',
                    goods_id: row.id,
                    store_id: getQuery("store_id")
                })
            },

            editStock(row){
                this.edit_id = row.id;
            },

            deleteGoods(row){
                var self = this;
                this.$confirm('你确定要删除商户爆品吗？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.loading = true;
                    request({
                        params: {
                            r: "plugin/baopin/mall/store/delete-goods"
                        },
                        method: 'post',
                        data: {
                            goods_id: row.id,
                            store_id: getQuery("store_id"),
                        }
                    }).then(e => {
                        self.loading = false;
                        if (e.data.code === 0) {
                            self.loadData();
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.loading = false;
                        self.$message.error("request fail");
                    });
                });
            },

            saveStore(row){
                if(row.stock_num < 0){
                    this.$message.error("库存不能小于0");
                    return;
                }
                this.loading = true;
                var self = this;
                request({
                    params: {
                        r: 'plugin/baopin/mall/store/save-stock'
                    },
                    data: {
                        goods_id: row.id,
                        store_id: getQuery("store_id"),
                        stock_num: row.stock_num
                    },
                    method: 'post',
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.edit_id = 0;
                    }else{
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error('request fail');
                });
            },

            sortReload(column){
                this.search.sort_prop = column.prop;
                this.search.sort_type = column.order == "descending" ? 0 : 1;
                this.loadData();
            },

            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/baopin/mall/store/goods-list',
                    store_id: getQuery("store_id")
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
                        this.store = e.data.data.store;
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