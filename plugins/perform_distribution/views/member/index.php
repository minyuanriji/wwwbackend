<?php
Yii::$app->loadPluginComponentView('com-user-edit');
Yii::$app->loadComponentView('com-dialog-select');
?>
<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>人员设置</span>
                <div style="float: right; margin: -5px 0">
                    <com-dialog-select @close="editDialogVisible = false" :visible="editDialogVisible"
                            url="mall/user/index" :list-key="'nickname'"
                            :columns="[{key: 'mobile', label: '手机号'}]"
                            :multiple="false" @selected="userSelect" title="用户选择">
                        <el-button @click="editDialogVisible = true" type="primary" size="small">添加人员</el-button>
                    </com-dialog-select>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small"  placeholder="请输入关键词搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="listLoading" :data="list" border style="width: 100%">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="level" label="用户名" width="260">
                    <template slot-scope="scope">
                        <div style="display: flex;align-items: center">
                            <com-image :src="scope.row.avatar_url" style="flex-shrink: 0"></com-image>
                            <div style="margin-left:10px;display: flex;flex-direction: column;justify-content: space-between">
                                <span>{{scope.row.nickname}}</span>
                                <span>ID：{{scope.row.user_id}}</span>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="mobile" label="手机" width="120" align="center"></el-table-column>
                <el-table-column prop="level_name" label="等级" width="120" align="center"></el-table-column>
                <el-table-column  label="上级" align="center">
                    <template slot-scope="scope">
                        <el-table :show-header="false" :data="cParentInfo(scope.row)" border size="small">
                            <el-table-column prop="label" width="100" align="right"></el-table-column>
                            <el-table-column prop="value"></el-table-column>
                        </el-table>
                    </template>
                </el-table-column>
                <el-table-column label="日期" width="200" align="center">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="200">
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text" @click="edit(scope.row)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" @click="userDelete(scope.row, scope.$index)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                        @current-change="pagination"
                        background
                        layout="prev, pager, next"
                        :page-count="pageCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
    <com-user-edit v-model="isEdit" @on-save="getList" :data="editForm"></com-user-edit>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                editDialogVisible: false,
                isEdit: false,
                editForm: {
                    id: 0,
                    user_id: 0,
                    level_id: '',
                    user: {nickname: '', mobile: '', avatar_url: ''}
                },
                list: [],
                keyword: '',
                listLoading: false,
                page: 1,
                pageCount: 0
            };
        },
        computed:{
            cParentInfo(item){
                return function(item){
                    let infos = [];
                    if(item.parent){
                        infos.push({label: '编号', value: item.parent.id});
                        infos.push({label: '昵称', value: item.parent.nickname});
                        infos.push({label: '手机号', value: item.parent.mobile});
                        infos.push({label: '等级', value: item.parent.level_name ? item.parent.level_name : '-'});
                    }
                    return infos;
                }
            }
        },
        mounted: function () {
            this.getList();
        },
        methods: {
            search() {
                this.page = 1;
                this.getList();
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
                        r: 'plugin/perform_distribution/mall/member/index',
                        page: self.page,
                        keyword: this.keyword
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
            userSelect(e){
                let item = {
                    id: 0,
                    user_id: e.id,
                    level_id:'',
                    user: {nickname: e.nickname, mobile: e.mobile, avatar_url: e.avatar_url}
                };
                this.editDialogVisible = false;
                this.edit(item);
            },
            edit(item){
                item.level_id = item.level_id + '';
                this.editForm = item;
                this.isEdit = true;
            },
            userDelete(row, index) {
                let self = this;
                self.$confirm('删除该商品, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/perform_distribution/mall/member/delete',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.list.splice(index, 1);
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
        }
    });
</script>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px;
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

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>