<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 30%;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .user-item {
        border: 1px #eeeeee solid;
        padding: 20px;
        margin-right: 20px;
        margin-bottom: 20px;
        width: 120px;
        height: 120px;
        position: relative;
    }

    .user-item .avatar {
        width: 50px;
        height: 50px;
    }

    .user-item .nickname {
        display: inline-block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
    }

    .user-item .close {
        position: absolute;
        right: -10px;
        top: -10px;
        padding: 0;
        border-radius: 100px;
        width: 20px;
        height: 20px;
    }

    .text {
        cursor: pointer;
        color: #419EFB;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <div flex="cross:center box:first">
                <div><span @click="$navigate({r:'mall/integral/index'})" class="text">自动发放积分</span>/自动发放积分编辑</div>
                <div flex="dir:right">
                    <div>
                        <el-button class="button-item" type="primary" size="small" :loading=btnLoading @click="onSubmit">提交</el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-body">
            <el-form :model="form" label-width="12rem" v-loading="loading" :rules="FormRules" ref="form">

                <el-form-item label="类型">
                    <el-radio-group v-model="form.controller_type">
                        <el-radio :label="0">积分券</el-radio>
<!--                        <el-radio :label="1">红包</el-radio>-->
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="发放用户" prop="user_id">
                    <el-autocomplete size="small"
                                     v-model="form.parent_name"
                                     value-key="nickname"
                                     :fetch-suggestions="querySearchAsync"
                                     placeholder="请输入搜索内容"
                                     @select="inviterClick">
                    </el-autocomplete>
                </el-form-item>

                <el-form-item label="面值" prop="integral_num">
                    <el-input style="width: 220px" type="number"  v-model="form.integral_num"></el-input>
                </el-form-item>

                <el-form-item label="周期" prop="period">
                    <el-input style="width: 220px" type="number" v-model="form.period"></el-input>
                </el-form-item>

                <el-form-item label="周期单位">
                    <el-radio-group v-model="form.period_unit">
                        <el-radio :label="0">周</el-radio>
                        <el-radio :label="1">月</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="积分类型">
                    <el-radio-group v-model="form.type">
                        <el-radio :label="1">永久积分</el-radio>
                        <el-radio :label="2">动态积分</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="有效天数" prop="effective_days">
                    <el-input style="width: 220px" type="number" v-model="form.effective_days"></el-input>
                </el-form-item>

                <el-form-item label="下次发放时间" prop="next_publish_time">
                    <template>
                        <div class="block">
                            <el-date-picker
                                    v-model="form.next_publish_time"
                                    type="datetime"
                                    placeholder="选择日期时间"
                                    align="right"
                                    :picker-options="pickerOptions">
                            </el-date-picker>
                        </div>
                    </template>
                    <span style="color: red">注意：请填写10分钟之后的时间</span>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</section>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                pickerOptions: {
                    shortcuts: [{
                        text: '今天',
                        onClick(picker) {
                            picker.$emit('pick', new Date());
                        }
                    }, {
                        text: '昨天',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24);
                            picker.$emit('pick', date);
                        }
                    }, {
                        text: '一周前',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
                            picker.$emit('pick', date);
                        }
                    }]
                },
                form: {
                    controller_type: 0,
                    user_id: 0,
                    integral_num: '',
                    period: '',
                    period_unit: 1,
                    type: 2,
                    effective_days: '',
                    next_publish_time: '',
                },
                loading: false,
                btnLoading: false,
                FormRules: {
                    user_id: [
                        {required: true, message: '请选择用户'}
                    ],
                    integral_num: [
                        {required: true, message: '请输入面值'}
                    ],
                    period: [
                        {required: true, message: '请输入周期'}
                    ],
                    effective_days: [
                        {required: true, message: '请输入有效天数'}
                    ],
                    next_publish_time: [
                        {required: true, message: '请输入下次发放时间'}
                    ],
                },
                dialog: {
                    show: false,
                    list:[],
                    loading: false,
                    page: 1,
                    pageCount: 0,
                    currentPage: null,
                    keyword: '',
                    waitSelectUsers: [],
                }
            };
        },
        methods: {
            querySearchAsync(queryString, cb) {
                this.keyword = queryString;
                this.searchUser(cb);
            },
            inviterClick(row) {
                this.form.user_id = row.id;
            },
            searchUser(cb) {
                request({
                    params: {
                        r: 'mall/user/search-user',
                        keyword: this.keyword,
                        user_id: this.form.user_id,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            // 提交
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign(this.form);
                        para.next_publish_time = para.next_publish_time.getTime() / 1000;
                        if (para.next_publish_time) {

                        }
                        request({
                            params: {
                                r: 'mall/integral/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message({
                                  message: e.data.msg,
                                  type: 'success'
                                });
                                setTimeout(function(){
                                    navigateTo({ r: 'mall/integral/index' });
                                },300);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },
            // 获取数据
            getList() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/integral/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.coupon_list = e.data.data.coupon_list;
                        this.form.coupon_id = this.coupon_list[0].id;
                        if (e.data.data.list.id > 0) {
                            this.form = e.data.data.list;
                        }
                    } else {
                        this.$alert(e.data.msg, '提示', {
                            confirmButtonText: '确定'
                        })
                    }
                }).catch(e => {
                    this.loading = false;
                    this.$alert(e.data.msg, '提示', {
                        confirmButtonText: '确定'
                    })
                });
            },
            selectUser() {
                this.dialog.show = true;
                this.dialog.loading = true;
                request({
                    params: {
                        r: 'mall/user/index',
                        page: this.dialog.page,
                        keyword: this.dialog.keyword,
                    }
                }).then(response => {
                    this.dialog.loading = false;
                    if (response.data.code === 0) {
                        this.dialog.waitSelectUsers = [];
                        this.dialog.list = response.data.data.list;
                        this.dialog.pageCount = response.data.data.pagination.page_count;
                        this.dialog.currentPage = response.data.data.pagination.current_page;
                    } else {
                        this.$alert(response.data.msg, '提示', {
                            confirmButtonText: '确定'
                        })
                    }
                });
            },
            pagination(currentPage) {
                this.dialog.page = currentPage;
                this.selectUser();
            },
            // 多选
            handleSelectionChange(val) {
                this.dialog.waitSelectUsers = val;
            },
            selectUserSubmit() {
                if (!this.form.user_list) {
                    this.form.user_list = [];
                }
                this.form.user_list = this.form.user_list.concat(this.dialog.waitSelectUsers);
                this.dialog.show = false;
            },
            deleteUser(index) {
                this.form.user_list.splice(index, 1);
            }
        },
        created() {
            this.getList();
        }
    })
</script>
