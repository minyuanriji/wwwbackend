<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-13
 * Time: 14:21
 */

Yii::$app->loadComponentView('com-dialog-select');
?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>收益记录</span>
                <div style="float: right;margin: -5px 0">
                    <el-button @click="handleIncome" type="primary" size="small">收益充值</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
                类型筛选
                <el-select style="width: 120px;" size="small" v-model="is_manual" @change='search'>
                    <el-option key="all" label="全部" value=""></el-option>
                    <el-option key="1" label="管理员操作" value="1"></el-option>
                    <el-option key="0" label="系统操作" value="0"></el-option>
                </el-select>

            <el-date-picker size="small" v-model="date" type="datetimerange"
                            style="float: left"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至" start-placeholder="开始日期"
                            @change="selectDateTime"
                            end-placeholder="结束日期">
            </el-date-picker>
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入昵称搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="user.nickname" label="昵称"></el-table-column>
                <el-table-column label="收支情况(收益)" width="130">
                    <template slot-scope="scope">
                        <div style="font-size: 18px;color: #68CF3D" v-if="scope.row.type == 1">+{{scope.row.income}}</div>
                        <div style="font-size: 18px;color: #F6AA5A" v-if="scope.row.type == 2">-{{scope.row.income}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="总收益"  prop="money" width="130">
                </el-table-column>
                <el-table-column prop="desc" label="说明" width="400"></el-table-column>
                <el-table-column prop="scope" width="180" label="收益时间">
                       <template slot-scope="scope">
                           {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                       </template>
                </el-table-column>
            </el-table>

            <!--工具条 批量操作和分页-->
            <el-col :span="24" class="toolbar">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        @current-change="pageChange"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="float:right;margin:15px"
                        v-if="pagination">
                </el-pagination>
            </el-col>
        </div>

        <!-- 充值收益 -->
        <el-dialog title="充值收益" :visible.sync="dialogIncome" width="30%">
            <el-form :model="incomeForm" label-width="80px" :rules="incomeFormRules" ref="incomeForm">
                <el-form-item label="操作" prop="type">
                    <el-radio v-model="incomeForm.type" label="1">充值</el-radio>
                    <el-radio v-model="incomeForm.type" label="2">扣除</el-radio>
                </el-form-item>
                <el-form-item label="用户" prop="user_id">
                    <el-input style="display: none;" v-model="incomeForm.user_id"></el-input>
                    <el-input disabled v-model="incomeForm.nickname">
                        <template slot="append">
                            <el-button @click="getUsers" type="primary">选择</el-button>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item label="金额" prop="price" size="small">
                    <el-input type="number" v-model="incomeForm.price"></el-input>
                </el-form-item>
                <el-form-item label="备注" prop="remark" size="small">
                    <el-input v-model="incomeForm.remark"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogIncome = false">取消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="incomeSubmit">充值</el-button>
            </div>
        </el-dialog>

        <com-dialog-select
                @close="closeDlgSelect"
                @selected="selectUser"
                :url="forDlgSelect.url"
                :multiple="forDlgSelect.multiple"
                :title="forDlgSelect.title"
                :list-key="forDlgSelect.listKey"
                :params="forDlgSelect.params"
                :columns="forDlgSelect.columns"
                :visible="forDlgSelect.visible"></com-dialog-select>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                incomeForm: {
                    type: "1",
                    user_id: '',
                    price: 0.00,
                    nickname: '',
                    remark: '',
                    is_manual: 1,
                },
                forDlgSelect:{
                    visible: false,
                    multiple: false,
                    title: "选择用户",
                    params: {},
                    columns: [
                        {label:"收益", key:"total_income"},
                        {label:"手机号", key:"mobile"},
                        {label:"等级", key:"role_type_text"}
                    ],
                    listKey: 'nickname',
                    url: "mall/user/index",
                },
                incomeFormRules: {
                    user_id: [
                        {required: true, message: '请选择用户', trigger: 'blur'},
                    ],
                    type: [
                        {required: true, message: '操作不能为空', trigger: 'blur'},
                    ],
                    price: [
                        {required: true, message: '金额不能为空', trigger: 'blur'},
                    ],
                },
                btnLoading: false,
                searchData: {
                    keyword: '',
                    date: '',
                    start_date: '',
                    end_at: '',
                    is_manual: '',
                },
                date: '',
                keyword: '',
                is_manual: '',
                form: [],
                pagination: null,
                listLoading: false,
                dialogIncome: false,

            };
        },
        methods: {
            getUsers(){
                this.forDlgSelect.visible = true;
            },
            selectUser(row){
                this.incomeForm.user_id = row.user_id;
                this.incomeForm.nickname = row.nickname;
            },
            closeDlgSelect(){
                this.forDlgSelect.visible = false;
            },
            handleIncome() {
                this.dialogIncome = true;
                this.incomeForm.user_id = '';
                this.incomeForm.nickname = '';
            },
            incomeSubmit() {
                var self = this;
                this.$refs.incomeForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, self.incomeForm);
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/finance/income-modified',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                                self.dialogIncome = false;
                            } else {
                                self.$message.error(e.data.msg);
                            }
                            self.btnLoading = false;
                        }).catch(e => {
                            self.btnLoading = false;
                        });
                    }
                });
            },
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.start_date = this.date[0];
                this.searchData.end_date = this.date[1];
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                if (this.date == null) {
                    this.date = ''
                }
                this.getList();
            },
            selectDateTime(e) {
                if (e != null) {
                    this.searchData.start_date = e[0];
                    this.searchData.end_date = e[1];
                } else {
                    this.searchData.start_date = '';
                    this.searchData.end_date = '';
                }
                this.page = 1;
                this.search();
            },

            getList() {
                let params = {
                    r: 'mall/finance/income-log',
                    page: this.page,
                    date: this.date,
                    user_id: getQuery('user_id'),
                    keyword: this.keyword,
                    is_manual: this.is_manual,
                };
                if (this.date) {
                    Object.assign(params, {
                        start_date: this.date[0],
                        end_date: this.date[1],
                    });
                }
                request({
                    params,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;

                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
                this.listLoading = true;
            },
        },
    mounted: function() {
        this.getList();
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
        margin: 0 0 20px 20px;
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

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>