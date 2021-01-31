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
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-card shadow="never">
                    <div slot="header">
                        <div>
                            <span>分销设置</span>
                        </div>
                    </div>
                    <div>
                        <el-row>
                            <el-col :span="16">

                                <el-form-item prop="is_apply" label="成为分销商的条件">
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
<!--                                <el-form-item prop="is_rebuy" label="开启复购奖励">-->
<!--                                    <el-col :span="8">-->
<!--                                        <el-switch v-model="ruleForm.is_rebuy" :active-value="1" :inactive-value="0">-->
<!--                                        </el-switch>-->
<!--                                    </el-col>-->
<!--                                </el-form-item>-->
<!--                                <el-form-item prop="rebuy_price_date" label="复购奖励每月结算日期">-->
<!--                                    <el-col :span="8">-->
<!--                                        <el-input v-model.number="ruleForm.rebuy_price_date" type="number">-->
<!--                                            <template slot="append">日</template>-->
<!--                                        </el-input>-->
<!--                                    </el-col>-->
<!--                                </el-form-item>-->
<!---->
<!--                                <el-form-item prop="is_subsidy" label="开启补贴奖励">-->
<!--                                    <el-col :span="8">-->
<!--                                        <el-switch v-model="ruleForm.is_subsidy" :active-value="1" :inactive-value="0">-->
<!--                                        </el-switch>-->
<!--                                    </el-col>-->
<!--                                </el-form-item>-->
<!--                                <el-form-item prop="subsidy_price_date" label="补贴奖励发放日期">-->
<!--                                    <el-col :span="8">-->
<!--                                        <el-input v-model.number="ruleForm.subsidy_price_date" type="number">-->
<!--                                            <template slot="append">日</template>-->
<!--                                        </el-input>-->
<!--                                    </el-col>-->
<!--                                </el-form-item>-->
                                <el-form-item label="分销层级" prop="level" required>
                                    <el-radio-group v-model="ruleForm.level">
                                        <el-radio :label="0">关闭</el-radio>
                                        <el-radio :label="1">一级分销</el-radio>
                                        <el-radio :label="2">二级分销</el-radio>
                                        <el-radio :label="3">三级分销</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                                <el-form-item label="分销内购" prop="is_self_buy" required>
                                    <label slot="label">分销内购
                                        <el-tooltip class="item" effect="dark"
                                                    content="开启分销内购，分销商自己购买商品，享受一级佣金，上级享受二级佣金，上上级享受三级佣金"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-switch v-model="ruleForm.is_self_buy" :active-value="1" :inactive-value="0">
                                    </el-switch>
                                </el-form-item>

                                <el-form-item label="启用团队复购奖励" prop="is_team" required>
                                    <label slot="label">启用团队复购奖励
                                        <el-tooltip class="item" effect="dark"
                                                    content="开启团队奖励需要在团队奖励等级进行配置"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-switch v-model="ruleForm.is_team" :active-value="1" :inactive-value="0">
                                    </el-switch>
                                </el-form-item>


                                <el-form-item label="分销商等级入口" style="margin-bottom: 0">
                                    <div>
                                        <el-switch v-model="ruleForm.is_show_share_level" :active-value="1" :inactive-value="0">
                                        </el-switch>
                                    </div>
                                </el-form-item>
                                <el-form-item>
                                    <label slot="label">
                                        <el-button type="text" @click="show_share_level = true">查看图例</el-button>
                                    </label>
                                    <el-dialog
                                            title="查看分销商等级入口图例"
                                            :visible.sync="show_share_level"
                                            width="30%">
                                        <div style="text-align: center">
                                            <image src="statics/img/mall/is_show_share_level.png"></image>
                                        </div>
                                        <div slot="footer" class="dialog-footer">
                                            <el-button type="primary" @click="show_share_level = false">我知道了</el-button>
                                        </div>
                                    </el-dialog>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </div>
                </el-card>
                <el-card style="margin-top: 10px" shadow="never">
                    <div slot="header">
                        <div>分销佣金设置
                            <el-tooltip class="item" effect="dark"
                                        content="需要开启分销层级，才能设置对应的分销佣金"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </div>
                    </div>
                    <el-col :span="14">
                        <el-form-item label="分销佣金类型" prop="price_type" v-if="ruleForm.level > 0">
                            <el-radio-group v-model="ruleForm.price_type">
                                <el-radio :label="1">百分比</el-radio>
                                <el-radio :label="2">固定金额</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="一级佣金" prop="first_price" v-if="ruleForm.level > 0">
                            <el-input v-model.number="ruleForm.first_price" type="number">
                                <template slot="append" v-if="ruleForm.price_type == 2">元</template>
                                <template slot="append" v-if="ruleForm.price_type == 1">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="二级佣金" prop="second_price" v-if="ruleForm.level > 1">
                            <el-input v-model.number="ruleForm.second_price" type="number">
                                <template slot="append" v-if="ruleForm.price_type == 2">元</template>
                                <template slot="append" v-if="ruleForm.price_type == 1">%</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="三级佣金" prop="third_price" v-if="ruleForm.level > 2">
                            <el-input v-model.number="ruleForm.third_price" type="number">
                                <template slot="append" v-if="ruleForm.price_type == 2">元</template>
                                <template slot="append" v-if="ruleForm.price_type == 1">%</template>
                            </el-input>
                        </el-form-item>
                    </el-col>
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
            let firstPriceValidate = (rule, value, callback) => {
                if (this.ruleForm.level > 0 && !value && value !== 0) {
                    callback(new Error('一级佣金不能为空'));
                }
                callback();
            };
            let secondPriceValidate = (rule, value, callback) => {
                if (this.ruleForm.level > 1 && !value && value !== 0) {
                    callback(new Error('二级佣金不能为空'));
                }
                callback();
            };
            let thirdPriceValidate = (rule, value, callback) => {
                if (this.ruleForm.level > 2 && !value && value !== 0) {
                    callback(new Error('三级佣金不能为空'));
                }
                callback();
            };
            return {
                loading: false,
                btnLoading: false,
                cat_show: false,
                show_share_level: false,
                ruleForm: {
                    level: 0,
                    is_apply:0,
                    is_check:0,
                    is_self_buy: 0,
                    price_type: 1,
                    first_price: 0,
                    second_price: 0,
                    third_price: 0,
                    is_show_share_level: 1,
                    rebuy_price_date:1,
                    is_rebuy:0,
                    is_team:0,
                    subsidy_price_date:1,
                    is_subsidy:0,
                    protocol:'',
                },
                rules: {
                    level: [
                        {message: '请选择分销层级', trigger: 'blur', required: true}
                    ],
                    is_self_buy: [
                        {message: '请选择分销内购', trigger: 'blur', required: true}
                    ],
                    price_type: [
                        {message: '请选择分销佣金类型', trigger: 'blur', required: true}
                    ],
                    first_price: [
                        {validator: firstPriceValidate, trigger: 'blur'},
                        {type: 'number', message: '一级佣金必须为数字', trigger: 'blur'},
                    ],
                    second_price: [
                        {validator: secondPriceValidate, trigger: 'blur'},
                        {type: 'number', message: '二级佣金必须为数字', trigger: 'blur'},
                    ],
                    third_price: [
                        {validator: thirdPriceValidate, trigger: 'blur'},
                        {type: 'number', message: '三级佣金必须为数字', trigger: 'blur'},
                    ],
                }
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
                        r: 'plugin/distribution/mall/setting/index',
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


                if(this.ruleForm.rebuy_price_date>28){
                    this.$message.error('为防止部分月份低于28天，请填写28日及之前');
                    return;
                }

                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/distribution/mall/setting/index',
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