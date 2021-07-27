<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 职位操作
 * Author: zal
 * Date: 2020-07-09
 * Time: 15:50
 */

?>

<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/business_card-position/index'})">职位
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>
                    <div v-if="is_show">
                        编辑职位
                    </div>
                    <div v-else>
                        添加职位
                    </div>

                </el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form_box">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-card style="margin-top: 10px" shadow="never">

                    <el-col :span="14">
                        <el-form-item label="职位名" prop="name" >
                            <el-input v-model.name="ruleForm.name" type="text">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="所属部门">
                            <el-select v-model="ruleForm.bcpid" placeholder="请选择部门">

                                <el-option
                                        v-for="item in department_list"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-card>
            </el-form>
            <el-button :loading="btnLoading" class="button-item" type="primary" style="margin-top: 24px;"
                       @click="submitForm('ruleForm')" size="small">保存
            </el-button>
        </div>

    </el-card>
</div>
<script>

    const app = new Vue({
        el: '#app',
        data() {
            return {
                msg: 1,
                options: [],//会员等级列表
                level: 0,
                form: {
                    type: [],
                },
                department_list: [],
                showConditionMsg: false,
                conditionMsg: '',
                weights: [],
                ruleForm: {
                    name: '',
                    bcpid: '',
                    is_delete: 0,
                },
                rules: {
                    name: [
                        {required: true, message: '请输入职位名称', trigger: 'change'},
                    ],
                    bcpid: [
                        {required: true, message: '请选择部门', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
                is_show:false,
            };
        },
        created() {
            this.getDepartment();
        },
        mounted() {
            if (getQuery('id')) {
                this.loadData();
                this.is_show = true;
            }else{
                this.is_show = false;
            }
            this.getSetting();
        },
        methods: {
            getDepartment() {
                request({
                    params: {
                        r: 'plugin/business_card/mall/business-card-department/index',
                        limit:100,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.department_list = e.data.data.list;
                    } else {

                    }
                })
            },
            submitForm(formName) {
                let self = this;
                self.showConditionMsg = false;

                this.$refs[formName].validate((valid) => {

                    if (valid) {
                        self.btnLoading = true;
                        self.ruleForm.bcpid = self.ruleForm.bcpid ? self.ruleForm.bcpid : 0;
                        request({
                            params: {
                                r: 'plugin/business_card/mall/business-card-position/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                                id: getQuery('id')
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                navigateTo({
                                    r: 'plugin/business_card/mall/business-card-position/index'
                                })
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            loadData() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/business_card/mall/business-card-position/edit',
                        id: getQuery('id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.detail) {
                            this.ruleForm = e.data.data.detail;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }

                    console.log(this.ruleForm);

                }).catch(e => {
                    console.log(e);
                });
            },
        }
    });
</script>

<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 20%;
        min-width: 900px;
    }

    .form-body .el-form-item {
        padding-right: 25%;
        min-width: 850px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .check-group {
        font-size: 14px !important;
    }

    .check-group .el-col {
        display: flex;
    }

    .check-group .el-input {
        margin: 0 5px;
    }

    .check-group .el-col .el-checkbox {
        display: flex;
        align-items: center;
    }

    .check-group .el-col .el-input {
        width: 100px;
    }

    .check-group .el-col .el-input .el-input__inner {
        height: 30px;
        width: 100px;
    }

    .el-checkbox-group .el-col {
        margin-bottom: 10px;
    }
</style>