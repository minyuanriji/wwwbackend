<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:11
 */
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-select-cat');
?>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>基础设置</span>
            </div>
        </div>
        <div class="form_box">
            <el-form :model="ruleForm"   size="small" ref="ruleForm" label-width="150px">
                <el-card shadow="never">
                    <div slot="header">
                        <div>
                            <span>股东设置</span>
                        </div>
                    </div>
                    <div>
                        <el-row>
                            <el-col :span="16">
                                <el-form-item label="启用股东提成" style="margin-bottom: 0">
                                    <div>
                                        <el-switch v-model="ruleForm.is_enable" :active-value="1" :inactive-value="0">
                                        </el-switch>
                                    </div>
                                </el-form-item>


                                <el-form-item prop="compute_type" label="结算方式">
                                    <el-col :span="8">
                                        <el-radio-group  v-model="ruleForm.compute_type" size="small">
                                            <el-radio :label="0" border>订单金额</el-radio>
                                            <el-radio :label="1" border>利润</el-radio>
                                        </el-radio-group>
                                    </el-col>
                                </el-form-item>

                                <el-form-item prop="compute_period" label="结算周期">
                                    <el-col :span="8">
                                        <el-radio-group  v-model="ruleForm.compute_period" size="small">
                                            <el-radio :label="0" border>天</el-radio>
                                            <el-radio :label="1" border>周</el-radio>
                                            <el-radio :label="2" border>月</el-radio>
                                        </el-radio-group>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="股东分红说明" prop="detail">
                                    <el-input type="textarea" :rows="3" placeholder="请输入股东分红说明"
                                              v-model="ruleForm.detail" maxlength="80" show-word-limit></el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </div>
                </el-card>
            </el-form>
            <el-button :loading="btnLoading" class="button-item" type="primary" style="margin-top: 24px;"
                       @click="store('ruleForm')" size="small">保存
            </el-button>
        </div>

    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                ruleForm: {
                    is_enable: 0,
                    compute_type:0,//结算方式
                    compute_period:0,//结算周期
                    detail:''//股东分红说明
                },
            }
        },
        mounted: function () {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/boss/mall/boss/setting',
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = Object.assign(this.ruleForm, e.data.data.setting);

                    }
                }).catch(e => {
                    this.loading = false;
                })
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/boss/mall/boss/setting',
                            },
                            method: 'post',
                            data: this.ruleForm
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.$message.error(e);
                            this.btnLoading = false;
                        })
                    } else {
                        this.btnLoading = false;
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
        }
    });
</script>
<style>
    .form_box {
        background-color: #f3f3f3;
        padding: 0 0 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .el-input-group__append {
        background-color: #fff;
        color: #353535;
    }
</style>