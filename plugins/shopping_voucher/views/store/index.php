<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>商户管理</span>
        </div>
        <div class="table-body">

            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入店铺名、商户ID进行搜索" v-model="search.keyword"
                          clearable @clear="toSearch" style="width: 300px">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>

            <div style="float: right">
                <el-button type="primary" size="small" style="padding: 9px 15px !important;"  @click="edit()">添加商户</el-button>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">

                <el-table @sort-change="sortReload" :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column sortable="custom" prop="id" width="90" label="ID"></el-table-column>
                    <el-table-column prop="name" label="商户" width="500">
                        <template slot-scope="scope">
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.cover_url"></com-image>
                                </div>
                                <div flex="cross:top cross:center">
                                    <div flex="dir:left">
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{scope.row.name}}(mch_id：{{scope.row.mch_id}})</div>
                                            </template>
                                            <com-ellipsis :line="2">{{scope.row.name}}(mch_id：{{scope.row.mch_id}})</com-ellipsis>
                                        </el-tooltip>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="ratio" label="比例" width="300">
                        <template slot-scope="scope">
                            {{scope.row.ratio}}%
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-link type="danger" underline="true" icon="el-icon-edit" @click="edit(scope.row.id)">编辑</el-link>&nbsp;
                            <el-link type="danger" underline="true" icon="el-icon-delete" @click="delete_bp(scope.row.id)">删除</el-link>&nbsp;
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
            dialogLoading: false,
            selections: []
        },
        mounted() {
            this.loadData();
        },
        methods: {
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'plugin/shopping_voucher/mall/store/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/shopping_voucher/mall/store/edit',
                    });
                }
            },

            sortReload(column){
                this.search.sort_prop = column.prop;
                this.search.sort_type = column.order == "descending" ? 0 : 1;
                this.loadData();
            },

            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/shopping_voucher/mall/store/list'
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

            //删除记录
            delete_bp(id){
                var self = this;
                this.$confirm('是否删除？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.loading = true;
                    request({
                        params: {
                            r: "plugin/shopping_voucher/mall/store/delete-mch"
                        },
                        method: 'post',
                        data: {
                            id: id
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