<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:48
 */
Yii::$app->loadPluginComponentView('area-batch');
Yii::$app->loadPluginComponentView('area-edit');
Yii::$app->loadPluginComponentView('area-level');
?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>区域代理列表</span>
            <el-form size="small" :inline="true" :model="search" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <com-export-dialog :field_list='exportList' :params="search" @selected="confirmSubmit">
                    </com-export-dialog>
                </el-form-item>
            </el-form>
        </div>
        <div class="table-body">

            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>

            <div style="float: right">
                <el-button type="primary" size="small" style="padding: 9px 15px !important;" @click="editClick">添加区域代理
                </el-button>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column align='center' type="selection" width="60"></el-table-column>
                    <el-table-column prop="user_id" width="80" label="用户ID"></el-table-column>
                    <el-table-column label="基本信息" width="200">
                        <template slot-scope="scope">
                            <com-image style="float: left;margin-right: 5px;" mode="aspectFill"
                                       :src="scope.row.avatar_url"></com-image>
                            <div>{{scope.row.nickname}}</div>

                        </template>
                    </el-table-column>

                    <el-table-column label="手机号" prop="mobile">
                        <template slot-scope="scope">
                            <div>{{scope.row.userInfo.mobile}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="累计佣金" prop="total_price">
                        <template slot-scope="scope">
                            <div>{{scope.row.total_price}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="推荐人" prop="parent_name"></el-table-column>

                    <el-table-column label="区域代理等级" width="120" prop="level_name">
                    </el-table-column>
                    <el-table-column label="代理区域" width="120" prop="address">
                    </el-table-column>
                    </el-table-column>
                    <el-table-column label="时间" width="200">
                        <template slot-scope="scope">
                            <div>成为区域代理时间：<br>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                        </template>
                    </el-table-column>
                    </el-table-column>
                    <el-table-column label="备注信息" prop="remarks"></el-table-column>
                    <el-table-column label="操作" width="300px">
                        <template slot-scope="scope">
                            <el-button type="text" size="mini" circle style="margin-top: 10px"
                                       @click.native="order(scope.row.user_id)">
                                <el-tooltip class="item" effect="dark" content="查看订单" placement="top">
                                    <img src="statics/img/mall/area/order.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="cash(scope.row.user_id)">
                                <el-tooltip class="item" effect="dark" content="提现详情" placement="top">
                                    <img src="statics/img/mall/area/detail.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="remarks(scope.row)">
                                <el-tooltip class="item" effect="dark" content="添加备注" placement="top">
                                    <img src="statics/img/mall/order/add_remark.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="editLevel(scope.row)">
                                <el-tooltip class="item" effect="dark" content="修改区域代理等级" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
                                </el-tooltip>
                            </el-button>

                            <el-button circle size="mini" type="text" @click="agentDelete(scope.row, scope.$index)">
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

    <el-dialog title="添加备注" :visible.sync="dialogContent">
        <el-form :model="remarksForm">
            <el-form-item label="备注">
                <el-input type="textarea" v-model="remarksForm.remarks" autocomplete="off"></el-input>
                <el-input style="display: none" :readonly="true" v-model="remarksForm.id"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogContent = false">取 消</el-button>
            <el-button type="primary" @click="remarksSubmit" :loading="remarksLoading">确 定</el-button>
        </div>
    </el-dialog>
    <area-edit v-model="edit.show"></area-edit>
    <area-level v-model="level.show" :area="current_area" @success="levelSuccess"></area-level>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data: {
            qrimg: '',
            showqr: false,
            avatar: '',
            nickname: '',
            current_area:null,
            search: {
                keyword: '',
                status: -1,
                page: 1,
                platform: '',
                level: ''
            },
            loading: false,
            activeName: '-1',
            list: [],
            pagination: null,
            dialogChild: false,
            dialogLoading: false,
            childList: [],

            select: {
                nickname: '',
                status: 'first',
            },
            dialogContent: false,
            remarksForm: {
                remarks: '',
                id: ''
            },
            remarksLoading: false,
            exportList: [],
            edit: {
                show: false,
            },
            level: {
                show: false,
                area: null,
            },
            areaLevelList: [],
            choose_list: [],
        },
        mounted() {
            this.loadData();
        },
        methods: {

            agentDelete(row, index) {
                let self = this;
                self.$confirm('删除该区域代理, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/area/mall/area/delete',
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

            down() {
                var alink = document.createElement("a");
                alink.href = this.qrimg;
                alink.download = this.nickname;
                alink.click();
            },

            confirmSubmit() {
                this.search.status = this.activeName
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/area/mall/area/index'
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
                        this.exportList = e.data.data.export_list;
                        this.areaLevelList = e.data.data.areaLevelList;
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
            apply(user_id, status) {
                this.$prompt('请输入原因', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'mall/area/apply',
                                    user_id: user_id,
                                    status: status,
                                    reason: instance.inputValue
                                },
                                method: 'get'
                            }).then(e => {
                                done();
                                instance.confirmButtonLoading = false;
                                if (e.data.code == 0) {
                                    this.loadData();
                                } else {
                                    this.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                done();
                                instance.confirmButtonLoading = false;
                            });
                        } else {
                            done();
                        }
                    }
                }).then(({value}) => {
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '取消输入'
                    });
                });
            },
            order(id) {
                navigateTo({
                    r: 'mall/area/order',
                    id: id
                })
            },
            cash(user_id) {
                navigateTo({
                    r: 'mall/area/cash',
                    user_id: user_id
                })
            },

            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            remarks(row) {
                this.dialogContent = true;
                this.remarksForm = {
                    remarks: row.remarks,
                    id: row.id
                }
            },
            remarksSubmit() {
                this.remarksLoading = true;
                request({
                    params: {
                        r: 'plugin/area/mall/area/remarks-edit',
                    },
                    method: 'post',
                    data:{
                        remarks: this.remarksForm.remarks,
                        id: this.remarksForm.id
                    }
                }).then(e => {
                    this.remarksLoading = false;
                    if (e.data.code == 0) {
                        this.dialogContent = false;
                        this.loadData();
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.data.msg);
                    }
                }).catch(e => {
                    this.remarksLoading = false;

                    this.$message.error('未知错误');
                });
            },
            dialogChildShow(share, status) {
                this.dialogChild = true;
                this.dialogLoading = true;
                this.select = {
                    nickname: share.nickname,
                    status: status
                };
                request({
                    params: {
                        r: 'mall/area/team',
                        status: status,
                        id: share.user_id
                    },
                    method: 'get'
                }).then(e => {
                    this.dialogLoading = false;
                    if (e.data.code == 0) {
                        this.childList = e.data.data.list;
                    }
                }).catch(e => {
                    this.dialogLoading = false;
                    this.$message.error('未知错误');
                });
            },
            editClick() {
                this.edit.show = true;
            },
            editLevel(row) {
                this.level.show = true;
                this.current_area = row;
            },
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.id);
                })
            },
            levelSuccess() {
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