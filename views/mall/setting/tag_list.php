<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片列表页
 * Author: zal
 * Date: 2020-07-09
 * Time: 15:48
 */

?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>标签列表</span>
            <div style="float: right;margin-top: -5px">
                <el-button type="primary"
                           @click="$navigate({r: 'mall/setting/tag'})"
                           size="small">添加标签
                </el-button>
            </div>
        </div>
        <div class="table-body">

            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column align='center' type="selection" width="60"></el-table-column>
                    <el-table-column prop="id" width="80" label="标签ID"></el-table-column>
                    <el-table-column prop="name" width="120" label="名称"></el-table-column>
                    <el-table-column prop="type_name" width="120" label="标签类型"></el-table-column>
                    <el-table-column label="时间" width="200">
                        <template slot-scope="scope">
                            <div>{{scope.row.created_at}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button type="text" size="mini" circle style="margin-top: 10px"
                                       @click.native="customer(scope.row.id)">
                            </el-button>
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="toEdit(scope.row.id)">
                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
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
            qrimg: '',
            showqr: false,
            avatar: '',
            nickname: '',
            search: {
                keyword: '',
            },
            loading: false,
            activeName: '-1',
            list: [],
            pagination: null,
            dialogChild: false,
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
                distribution: null,
            },
            distributionLevelList: [],
            choose_list: [],
            addDialogIntegral: false,
            dialogLoading: false,
            dialogTableLoading: false,
            personnelForm: {
                department_id: 0,
                department_name: '',
                role_id: 0,
                position_id: '',
                user_ids: [],
            },
            personnelFormRules: {
                role_id: [
                    {required: true, message: '角色必须选择', trigger: 'blur'},
                ],
                position_id: [
                    {required: true, message: '职位必须选择', trigger: 'blur'},
                ]
            },
            position_list: [],
            personnelFormKeyword: '',
            userData: [],
            userDataPages: {
                current_page: 1,
                pageSize: 10
            }
        },
        mounted() {
            this.loadData();
        },
        methods: {
            personnelFormSubmit() {
                this.$refs.personnelForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.personnelForm);
                        console.log(para,this.personnelForm,"表单")
                        this.dialogLoading = true;
                        request({
                            params: {
                                r: 'plugin/business_card/mall/business-card-auth/add',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                                this.dialogIntegral = false;
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.dialogLoading = false;
                        }).catch(e => {
                            this.dialogLoading = false;
                        });
                    }
                });
            },
            personnelSelectionChange(e) {
                let temp = e.map(item => {
                    return item.id;
                });
                this.personnelForm.user_ids = temp;
            },
            handleCurrentChange(val) {
                console.log(`当前页: ${val}`);
                this.userDataPages.current_page = val;
                this.getUserList();
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
                    r: 'mall/setting/tag-list'
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
                                    r: 'mall/distribution/apply',
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
            customer(id) {
                navigateTo({
                    r: 'plugin/business_card/mall/business-card-auth/look-user',
                    id: id
                })
            },
            toEdit(id) {
                navigateTo({
                    r: 'mall/setting/tag',
                    id: id
                })
            },

            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            editClick() {
                this.edit.show = true;
            },
            editLevel(row) {
                this.level.show = true;
                this.level.distribution = row;
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