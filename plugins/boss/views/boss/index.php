<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:48
 */



Yii::$app->loadPluginComponentView('boss-batch');
Yii::$app->loadPluginComponentView('boss-edit');
Yii::$app->loadPluginComponentView('boss-level');
?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>股东列表</span>

            <div style="float: right">
                <el-button type="primary" size="small" style="padding: 9px 15px !important;" @click="editClick">添加股东
                </el-button>
                <com-export-dialog :field_list='exportList' :action_url="'<?= Yii::$app->request->baseUrl . '/index.php?r=plugin/boss/mall/boss/index' ?>'" :params="search" ></com-export-dialog>
            </div>
        </div>
        <div class="table-body">
            <el-select size="small" v-model="search.level_id" @change='toSearch' class="select">
                <el-option :key="index" :label="item.name" :value="item.id"
                           v-for="(item, index) in bossLevelList"></el-option>
            </el-select>
            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-select slot="prepend" v-model="search.kw_type" placeholder="请选择" size="small"
                               style="width:120px;">
                        <el-option v-for="item in item_type_options"
                                   :key="item.value"
                                   :label="item.label"
                                   :value="item.value">
                        </el-option>
                    </el-select>
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
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

                    <el-table-column label="手机号" prop="mobile" width="150">
                        <template slot-scope="scope">
                            <div>{{scope.row.mobile}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="身份" prop="mobile" width="150">
                        <template slot-scope="scope">
                            <div v-if="scope.row.userInfo[0].role_type == 'user'">普通用户</div>
                            <div v-if="scope.row.userInfo[0].role_type == 'branch_office'">分公司</div>
                            <div v-if="scope.row.userInfo[0].role_type == 'partner'">合伙人</div>
                            <div v-if="scope.row.userInfo[0].role_type == 'store'">VIP会员</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="累计佣金" prop="total_price" width="100">
                        <template slot-scope="scope">
                            <div>{{scope.row.total_price}}</div>
                        </template>
                    </el-table-column>
                    <!--<el-table-column label="推荐人" prop="parent_name"></el-table-column>
                    <el-table-column width='200' label="下级用户">
                        <template slot-scope="scope">
                            <template v-for="(item, key, index) in share_name" v-if="scope.row[key] !== undefined">
                                <el-button type="text" @click="dialogChildShow(scope.row, index + 1)">
                                    {{item}}：{{scope.row[key]}}
                                </el-button>
                                <br>
                            </template>
                        </template>
                    </el-table-column>-->
                    <el-table-column label="股东等级" width="150" prop="level">
                        <template slot-scope="scope">
                            <el-tag size="small">{{scope.row.level_name}}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="时间" width="200">
                        <template slot-scope="scope">
                            <div>成为股东时间：<br>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="备注信息" prop="remarks"></el-table-column>
                    <el-table-column label="操作" width="300px">
                        <template slot-scope="scope">
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="remarks(scope.row)">
                                <el-tooltip class="item" effect="dark" content="添加备注" placement="top">
                                    <img src="statics/img/mall/order/add_remark.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="editLevel(scope.row)">
                                <el-tooltip class="item" effect="dark" content="修改股东等级" placement="top">
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
            <div style="text-align: center">
                <el-pagination
                        v-if="list.length > 0"
                        style="margin-top: 20px"
                        background :page-size="pagination.pageSize"
                        @current-change="pageChange"
                        layout="prev, pager, next" :current-page="pagination.current_page"
                        :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-card>
    <el-dialog
            title="下线情况"
            :visible.sync="dialogChild"
            width="40%">
        <div>
            <el-table :data="childList" border v-loading="dialogLoading">
                <el-table-column type="index" label="序号"></el-table-column>
                <el-table-column label="股东">
                    <template slot-scope="scope">
                        <span>{{select.nickname}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="下线等级" prop="nickname">
                    <template slot-scope="scope">
                        <span v-if="select.status == 1">{{share_name.first}}</span>
                        <span v-if="select.status == 2">{{share_name.second}}</span>
                        <span v-if="select.status == 3">{{share_name.third}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="昵称" prop="nickname"></el-table-column>
                <el-table-column label="成为下线时间" prop="junior_at"></el-table-column>
            </el-table>
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogChild = false">取 消</el-button>
            <el-button type="primary" @click="dialogChild = false">确 定</el-button>
        </div>
    </el-dialog>
    <el-dialog title="添加备注" :visible.sync="dialogContent">
        <el-form :model="remarksForm">
            <el-form-item label="备注">
                <el-input type="textboss" v-model="remarksForm.remarks" autocomplete="off"></el-input>
                <el-input style="display: none" :readonly="true" v-model="remarksForm.id"></el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogContent = false">取 消</el-button>
            <el-button type="primary" @click="remarksSubmit" :loading="remarksLoading">确 定</el-button>
        </div>
    </el-dialog>
    <boss-edit v-model="edit.show"></boss-edit>
    <boss-level v-model="level.show" :boss="level.boss" @success="levelSuccess"></boss-level>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data: {
            qrimg: '',
            showqr: false,
            avatar: '',
            nickname: '',
            search: {
                keyword: '',
                kw_type: 'mobile',
                status: -1,
                page: 1,
                platform: '',
                level: '',
                level_id: '',
            },
            loading: false,
            activeName: '-1',
            list: [],
            pagination: null,
            dialogChild: false,
            dialogLoading: false,
            childList: [],
            share_name: {
                first: '一级',
                second: '二级',
                third: '三级'
            },
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
                boss: null,
            },
            bossLevelList: [],
            choose_list: [],
            item_type_options:[
                {
                    value: 'mobile',
                    label: '手机号'
                },
                {
                    value: 'user_id',
                    label: '用户ID'
                },
                {
                    value: 'nickname',
                    label: '昵称'
                },
            ],
        },
        mounted() {
            this.loadData();
        },
        methods: {
            agentDelete(row, index) {
                let self = this;
                self.$confirm('删除该股东, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/boss/mall/boss/delete',
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
                    r: 'plugin/boss/mall/boss/index'
                };
                params = Object.assign(params, this.search);
                console.log(params);
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.exportList = e.data.data.export_list;
                        this.bossLevelList = e.data.data.bossLevelList;
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
                                    r: 'mall/boss/apply',
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
                    r: 'mall/boss/order',
                    id: id
                })
            },
            cash(user_id) {
                navigateTo({
                    r: 'mall/boss/cash',
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
                        r: 'plugin/boss/mall/boss/remarks-edit',
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
                        r: 'mall/boss/team',
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
                this.level.boss = row;
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