<?php
/**
  * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
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

    .el-input-group__append .el-button {
        margin: 0;
    }

    .sort-input {
        width: 100%;
        background-color: #F3F5F6;
        height: 32px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }

    .sort-input .el-input__inner {
        height: 32px;
        line-height: 32px;
        background-color: #F3F5F6;
        float: left;
        padding: 0 10px;
        border: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>敏感词列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加敏感词</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword"clearable @clear="getList">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">
                <el-table-column
                        prop="id"
                        label="ID"
                        width="100">
                </el-table-column>
                <el-table-column
                        label="敏感词" width="350px">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.sensitive}}</com-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="created_at"
                        width="220"
                        label="添加日期">
                    <template slot-scope="scope">
                        <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="操作"
                        width="220">
                    <template slot-scope="scope">
                        <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="destroy(scope.row.id, scope.$index)" circle type="text" size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div flex="box:last cross:center" style="margin-top: 20px;">
                <div></div>
                <div>
                    <el-pagination
                            v-if="pageCount > 0"
                            @current-change="pagination"
                            background
                            layout="prev, pager, next"
                            :page-count="pageCount">
                    </el-pagination>
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
                list: [],
                listLoading: false,
                page: 1,
                keyword: '',
                pageCount: 0,
                id: null,
            };
        },
        methods: {

            quit() {
                this.id = null;
            },

            change(row) {
                let self = this;
                row.sort = self.sort;
                request({
                    params: {
                        r: 'mall/sensitive/edit'
                    },
                    method: 'post',
                    data: {
                        form: row,
                    },
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                        this.id = null;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                });
            },

            search: function() {
                this.listLoading = true;
                let keyword = this.keyword;
                request({
                    params: {
                        r: 'mall/sensitive/index',
                        keyword: keyword
                    },
                    method: 'get'
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.select = e.data.data.select;
                        this.pagination = e.data.data.pagination;
                    }else{
                        this.$alert(e.data.msg, '提示', {
                          confirmButtonText: '确定'
                        })
                }
                }).catch(e => {
                    this.listLoading = false;
                    this.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                });        
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
                        r: 'mall/sensitive/index',
                        page: self.page
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'mall/sensitive/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'mall/sensitive/edit',
                    });
                }
            },
            destroy(id, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'mall/sensitive/delete',
                        },
                        method: 'post',
                        data: {
                            id: id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
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
            switchChange(e) {
                let self = this;
                request({
                    params: {
                        r: 'mall/sensitive/switch-change',
                    },
                    method: 'post',
                    data: {
                        form: e
                    }
                }).then(e => {
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
