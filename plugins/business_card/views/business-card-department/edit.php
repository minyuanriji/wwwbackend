<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 17:50
 */


?>

<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/business_card-department/index'})">
                        部门
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑部门</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form_box">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-card style="margin-top: 10px" shadow="never">

                    <el-col :span="14">
                        <el-form-item label="部门名" prop="name" >
                            <el-input v-model.name="ruleForm.name" type="text">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="排序" prop="sort" >
                            <el-input v-model.sort="ruleForm.sort" type="text">
                            </el-input>
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
                showConditionMsg: false,
                conditionMsg: '',
                weights: [],
                ruleForm: {
                    name: '',
                    sort: 0,
                    is_delete: 0,
                },
                rules: {
                    name: [
                        {required: true, message: '请输入部门名称', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
            };
        },
        mounted() {
            if (getQuery('id')) {
                this.loadData();
            }
            this.getSetting();
        },
        methods: {
            submitForm(formName) {
                let self = this;
                self.showConditionMsg = false;

                this.$refs[formName].validate((valid) => {

                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/business_card/mall/business-card-department/edit'
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
                                    r: 'plugin/business_card/mall/business-card-department/index'
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
                        r: 'plugin/business_card/mall/business-card-department/edit',
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