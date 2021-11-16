<style>
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
        margin-right: 20px;
        margin-bottom: 20px;
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

    .el-tooltip__popper {
        max-width: 200px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0;">
        <div slot="header">
            <div>
                <span>子账号列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加子账号</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入用户名" clearable v-model="keyword" clearable @clear='getList'>
                    <el-button slot="append" @click="search" icon="el-icon-search"></el-button>
                </el-input>
            </div>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">
                <el-table-column prop="id" label="ID" width="100px"></el-table-column>
                <el-table-column
                        prop="username"
                        label="账户"
                        width="250px"
                >
                    <template slot-scope="scope">
                        <div flex="dir:top">
                            <span>{{scope.row.username}}</span>
                        </div>
                    </template>
                </el-table-column>
                <!--<el-table-column
                        prop=""
                        label="可创建小程序数量"
                        width="150px">
                    <template slot-scope="scope">
                        <span v-if="scope.row.mall_num == -1">无限制</span>
                        <span v-else>{{scope.row.mall_num}}</span>
                    </template>
                </el-table-column>-->
               <!-- <el-table-column
                        label="已创建小程序数量"
                        width="150px"
                >
                    <template slot-scope="scope">
                        {{scope.row.create_app_count}}
                        <a href="#" style="text-decoration:none; color: #409EFF;" @click="toMallList(scope.row)">{{scope.row.create_app_count}}</a>
                    </template>
                </el-table-column>-->
                <el-table-column
                        prop="expired_at"
                        :formatter="formatExpired"
                        width="180"
                        label="有效期">
                </el-table-column>
                <el-table-column prop="created_at" label="创建时间" width="260">
                    <template slot-scope="scope">
                        <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="editPassword(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="修改密码" placement="top">
                                <img src="statics/img/mall/change.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="destroy(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div style="text-align: center;margin: 20px 0;">
                <el-pagination
                        @current-change="pagination"
                        background
                        layout="prev, pager, next"
                        :current-page="page"
                        :page-count="pageCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                keyword: '',
            };
        },
        methods: {
            search() {
                this.page = 1;
                this.getList();
            },

            formatExpired(row, column) {
                console.log()
                if (row.expired_at != 0) {
                    return row.expired_at;
                }
                return '永久'
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/mirror/son-mall-list',
                        page: self.page,
                        keyword: self.keyword,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.list = e.data.data.list;
                        self.pageCount = e.data.data.pagination.page_count;
                        self.list.forEach(function(item){
                            console.log(item)
                            if(item.adminInfo.remark.length > 16) {
                                item.remark = item.adminInfo.remark.slice(0,16) + '...'
                            }

                        })
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(id) {
                navigateTo({
                    r: 'mall/mirror/son-account-edit',
                    id: id,
                });
            },
            destroy(id) {
                let self = this;
                self.$confirm('删除该用户, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'admin/index/destroy',
                        },
                        method: 'post',
                        data: {
                            id: id,
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
            editPassword(id) {
                let self = this;
                self.$prompt('请输入新密码', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    inputPattern: /\S+/,
                    inputErrorMessage: '请输入新密码',
                    inputType: 'password',
                }).then(({value}) => {
                    request({
                        params: {
                            r: 'admin/index/edit-password',
                        },
                        method: 'post',
                        data: {
                            id: id,
                            password: value
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {

                });
            },
            toMallList(row) {
                navigateTo({
                    r: 'admin/mall/index',
                    _layout: 'admin',
                    user_id: row.id,
                });
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
