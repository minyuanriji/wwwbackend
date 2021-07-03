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
                            <span>代理设置</span>
                        </div>
                    </div>
                    <div>
                        <el-row>
                            <el-col :span="16">
                                <el-form-item label="启用云库存" style="margin-bottom: 0">
                                    <div>
                                        <el-switch v-model="ruleForm.is_enable" :active-value="1" :inactive-value="0">
                                        </el-switch>
                                    </div>
                                </el-form-item>




                                <el-form-item label="允许临时补货" style="margin-bottom: 0">
                                    <div>
                                        <el-switch
                                                v-model="ruleForm.is_allow_temp_fill"
                                                :active-value="1"
                                                :inactive-value="0"
                                        >
                                        </el-switch>
                                    </div>
                                </el-form-item>

                                <el-form-item prop="temp_fill_time" label="补货限时">
                                    <el-col :span="8">
                                        <el-input v-model="ruleForm.temp_fill_time"
                                                  type="number">
                                            <template slot="append">小时</template>
                                        </el-input>
                                    </el-col>
                                </el-form-item>



                                <el-form-item prop="compute_time" label="订单完成后结算时间">
                                    <el-col :span="8">
                                        <el-input v-model="ruleForm.compute_time"
                                                  type="number">
                                            <template slot="append">小时</template>
                                        </el-input>
                                    </el-col>
                                </el-form-item>


                                <el-form-item prop="agent_level" label="平级层级">
                                    <el-col :span="8">
                                        <el-input v-model="ruleForm.equal_level"
                                                  type="number">
                                            <template slot="append">层</template>
                                        </el-input>
                                    </el-col>
                                </el-form-item>


                                <div class="title">
                                    <span>补货提醒短信通知</span>
                                </div>
                                <div style="background-color: #fff;">

                                    <el-form-item label="模板ID">
                                        <div style="">
                                            <el-input style="width: 200px" v-model="ruleForm.fill_sms.template_id"
                                                      placeholder="请输入模板ID"></el-input>
                                        </div>
                                    </el-form-item>
                                    <el-form-item label="模板变量-补货商品id" style="width: 200px">
                                        <el-input style="width: 200px" v-model="ruleForm.fill_sms.template_variable_id"></el-input>
                                    </el-form-item>
                                    <el-form-item label="模板变量-补货数量">
                                        <el-input style="width: 200px" v-model="ruleForm.fill_sms.template_variable_num"></el-input>
                                    </el-form-item>
                                    <el-form-item label="模板变量-补货时间">
                                        <el-input style="width: 200px" v-model="ruleForm.fill_sms.template_variable_duration"></el-input>
                                    </el-form-item>
                                </div>


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
                    equal_level:0,
                    is_allow_temp_fill:0,
                    temp_fill_time:0,
                    compute_time:0,
                    fill_sms: {
                        "template_id":"",
                        "template_variable_id":"",
                        "template_variable_num":"",
                        "template_variable_duration":""
                    }
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
                        r: 'plugin/stock/mall/stock/setting',
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
                                r: 'plugin/stock/mall/stock/setting',
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