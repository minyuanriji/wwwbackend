<?php
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-select-cat');
?>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <!--<div slot="header">
            <div>
                <span>基础设置</span>
            </div>
        </div>-->
        <div class="form_box">
            <el-form :model="ruleForm" size="small" ref="ruleForm" label-width="150px">

                <!--<el-card shadow="never">
                    <div slot="header">
                        <div>
                            <span>区域分红设置</span>
                        </div>
                    </div>
                    <div>
                        <el-row>
                            <el-col :span="16">
                                <el-form-item label="启用区域分红商" style="margin-bottom: 0">
                                    <div>
                                        <el-switch v-model="ruleForm.is_enable" :active-value="1" :inactive-value="0">
                                        </el-switch>
                                    </div>
                                </el-form-item>
                                <el-form-item prop="is_apply" label="成为区域代理的条件">
                                    <el-col :span="8">
                                        <el-radio-group v-model="ruleForm.is_apply" size="small">
                                            <el-radio :label="0" border>无条件</el-radio>
                                            <el-radio :label="1" border>申请</el-radio>
                                        </el-radio-group>
                                    </el-col>
                                </el-form-item>

                                <el-form-item prop="is_check" label="是否需要审核">
                                    <el-col :span="8">
                                        <el-radio-group v-model="ruleForm.is_check" size="small">
                                            <el-radio :label="0" border>不需要</el-radio>
                                            <el-radio :label="1" border>需要</el-radio>
                                        </el-radio-group>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="申请协议" prop="protocol" v-if="ruleForm.is_apply==1">
                                    <el-input type="textarea"
                                              :rows="4"
                                              placeholder="申请协议"
                                              v-model="ruleForm.protocol">
                                    </el-input>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </div>
                </el-card>-->

                <el-card shadow="never" style="margin-top:20px">
                    <div slot="header">
                        <div>
                            <span>结算设置</span>
                        </div>
                    </div>
                    <div>
                        <el-row>
                            <!--<el-col :span="16">
                                <el-form-item label="开启平均分红" style="margin-bottom: 0">
                                    <div>
                                        <el-switch v-model="ruleForm.is_equal" :active-value="1" :inactive-value="0">
                                        </el-switch>
                                    </div>
                                </el-form-item>


                                <el-form-item label="开启极差分红" style="margin-bottom: 0">
                                    <div>
                                        <el-switch v-model="ruleForm.is_level" :active-value="1" :inactive-value="0">
                                        </el-switch>
                                    </div>
                                </el-form-item>-->


                                <el-form-item label="默认分红比例" style="margin-bottom: 0; width: 500px">
                                    <div>

                                        <el-form-item label="省代">
                                            <el-input v-model.number="ruleForm.province_price" type="number" min="0">
                                                <template slot="append">%</template>
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item label="市代">
                                            <el-input v-model.number="ruleForm.city_price" type="number" min="0">
                                                <template slot="append">%</template>
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item label="区代">
                                            <el-input v-model.number="ruleForm.district_price" type="number" min="0">
                                                <template slot="append">%</template>
                                            </el-input>
                                        </el-form-item>
                                        <!--<el-form-item label="镇代">
                                            <el-input v-model.number="ruleForm.town_price" type="number">
                                                <template slot="append">%</template>
                                            </el-input>
                                        </el-form-item>-->

                                    </div>
                                </el-form-item>
                               <!-- <el-form-item prop="compute_type" label="结算方式">
                                    <el-col :span="24">
                                        <el-radio-group v-model="ruleForm.compute_type" size="small">
                                            <el-radio :label="0" border>订单完成后</el-radio>
                                            <el-radio :label="1" border>订单支付后</el-radio>
                                        </el-radio-group>
                                    </el-col>
                                </el-form-item>-->

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
                    is_enable: 0,//是否启用
                    is_check:0,//是否需要审核
                    is_apply:0,//是否需要申请
                    is_equal: 0,//是否平均分
                    is_level: 0,//是否走极差
                    province_price: 0,//省
                    city_price: 0,//市
                    district_price: 0,//区
                    town_price: 0,//镇
                    compute_type: 0,//计算方式  0 订单完成后 1支付完成,
                    protocol:'',
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
                        r: 'plugin/area/mall/area/setting',
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
                                r: 'plugin/area/mall/area/setting',
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