<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-14
 * Time: 18:03
 */
?>


<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form
                style="position: relative;"
                :model="ruleForm"
                label-width="172px"
                size="small">
            <el-tabs v-model="activeName">
                <el-tab-pane label="余额设置" name="balance">
                    <el-row>
                        <el-col :span="24">
                            <div class="title">
                                <span>余额配置</span>
                            </div>
                            <div class="form-body">
                                <el-form-item label="启用余额支付" prop="balance_status">
                                    <el-switch v-model="ruleForm.balance_status" active-value="1"
                                               inactive-value="0"></el-switch>
                                </el-form-item>
                                <el-form-item label="启用支付密码" prop="pay_password_status">
                                    <el-switch v-model="ruleForm.pay_password_status" active-value="1"
                                               inactive-value="0"></el-switch>
                                </el-form-item>
                                <el-form-item label="启用余额充值">
                                    <el-switch v-model="ruleForm.balance_charge_status" active-value="1"
                                               inactive-value="0"></el-switch>
                                </el-form-item>

                                <el-form-item label="启用余额提现">
                                    <el-switch v-model="ruleForm.balance_cash_status" active-value="1"
                                               inactive-value="0"></el-switch>
                                </el-form-item>
                                <el-form-item label="启用余额转账">
                                    <el-switch v-model="ruleForm.balance_transfer_status" active-value="1"
                                               inactive-value="0"></el-switch>
                                </el-form-item>
                            </div>
                        </el-col>
                    </el-row>
                </el-tab-pane>
                <el-tab-pane label="微信支付" name="first">
                    <el-row>
                        <el-col :span="24">
                            <div class="title">
                                <span>微信支付配置</span>
                            </div>
                            <div class="form-body">
                                <el-form-item label="启用微信支付" prop="wechat_status">
                                    <el-switch v-model="ruleForm.wechat_status" active-value="1"
                                               inactive-value="0"></el-switch>
                                </el-form-item>
                                <el-form-item label="微信公众号APPID" prop="wechat_app_id">
                                    <el-input v-model="ruleForm.wechat_app_id"></el-input>
                                </el-form-item>
                                <el-form-item label="微信支付商户号" prop="wechat_mch_id">
                                    <el-input v-model.trim="ruleForm.wechat_mch_id"></el-input>
                                </el-form-item>
                                <el-form-item label="微信支付Api密钥" prop="wechat_pay_secret">
                                    <el-input @focus="hidden.wechat_pay_secret = false"
                                              v-if="hidden.wechat_pay_secret"
                                              readonly
                                              placeholder="已隐藏内容，点击查看或编辑">
                                    </el-input>
                                    <el-input v-else v-model.trim="ruleForm.wechat_pay_secret"></el-input>
                                </el-form-item>
                                <el-form-item label="微信支付apiclient_cert.pem" prop="wechat_cert_pem">
                                    <el-input @focus="hidden.wechat_cert_pem = false"
                                              v-if="hidden.wechat_cert_pem"
                                              readonly
                                              type="textarea"
                                              :rows="5"
                                              placeholder="已隐藏内容，点击查看或编辑">
                                    </el-input>
                                    <el-input v-else type="textarea" :rows="5" v-model="ruleForm.wechat_cert_pem"></el-input>
                                </el-form-item>
                                <el-form-item label="微信支付apiclient_key.pem" prop="wechat_key_pem">
                                    <el-input @focus="hidden.wechat_key_pem = false"
                                              v-if="hidden.wechat_key_pem"
                                              readonly
                                              type="textarea"
                                              :rows="5"
                                              placeholder="已隐藏内容，点击查看或编辑">
                                    </el-input>
                                    <el-input v-else type="textarea" :rows="5" v-model="ruleForm.wechat_key_pem"></el-input>
                                </el-form-item>
                            </div>
                        </el-col>
                    </el-row>
                </el-tab-pane>
                <el-tab-pane label="积分设置" name="score">
                    <el-row>
                        <el-col :span="24">
                            <div class="title">
                                <span>积分配置</span>
                            </div>
                            <div class="form-body">
                                <el-form-item label="启用积分抵扣功能" prop="score_status">
                                    <el-switch v-model="ruleForm.score_status" active-value="1"
                                               inactive-value="0"></el-switch>
                                </el-form-item>

                                <el-form-item label="积分抵扣">
                                    <el-input placeholder="多少积分抵扣1元" type="number" v-model="ruleForm.score_price">
                                        <template slot="append"> 积分抵扣1元</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="用户积分使用规则" prop="score_rule">
                                    <el-input v-model="ruleForm.score_rule" type="textarea">
                                    </el-input>
                                </el-form-item>
                            </div>
                        </el-col>
                    </el-row>
                </el-tab-pane>
                <el-tab-pane label="金豆券设置" name="integral">
                    <el-row>
                        <el-col :span="24">
                            <div class="title">
                                <span>金豆券设置</span>
                            </div>
                            <div class="form-body">
                                <el-form-item label="启用金豆券抵扣功能" prop="integral_status">
                                    <el-switch v-model="ruleForm.integral_status" active-value="1"
                                               inactive-value="0"></el-switch>
                                </el-form-item>
                            </div>
                        </el-col>
                    </el-row>
                </el-tab-pane>
                <el-tab-pane label="收入提现" name="cash">
                    <el-row>
                        <el-col :span="24">
                            <div class="title">
                                <span>提现设置</span>
                            </div>
                            <div class="form-body">
                                <el-form-item label="开启收入提现" prop="is_income_cash">
                                    <el-switch v-model="ruleForm.is_income_cash" active-value="1"
                                               inactive-value="0"></el-switch>
                                </el-form-item>
                                <el-form-item label="提现方式" prop="cash_type" required>
                                    <label slot="label">提现方式
                                        <el-tooltip class="item" effect="dark"
                                                    content="自动打款支付，需要申请相应小程序的相应功能，
                                                    例如：微信需要申请企业付款到零钱功能"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-checkbox-group v-model="ruleForm.cash_type">
                                        <el-checkbox label="auto">微信自动打款</el-checkbox>
                                        <el-checkbox label="wechat">微信线下转账</el-checkbox>
                                        <el-checkbox label="alipay">支付宝线下转账</el-checkbox>
                                        <el-checkbox label="bank">银行卡线下转账</el-checkbox>
                                        <el-checkbox label="balance">余额提现</el-checkbox>
                                    </el-checkbox-group>
                                </el-form-item>
                                <el-form-item label="最少提现额度" prop="min_money" required>
                                    <el-input type="number" v-model.number="ruleForm.min_money">
                                        <template slot="append">元</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="每日提现上限" prop="day_max_money" required>
                                    <label slot="label">每日提现上限
                                        <el-tooltip class="item" effect="dark"
                                                    content="-1元表示不限制每日提现金额"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input type="number" v-model.number="ruleForm.day_max_money">
                                        <template slot="append">元</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="提现手续费" prop="cash_service_fee" required>
                                    <label slot="label">提现手续费
                                        <el-tooltip class="item" effect="dark"
                                                    content="0表示不设置提现手续费"
                                                    placement="top">
                                            <i class="el-icon-info"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input type="number" v-model.number="ruleForm.cash_service_fee">
                                        <template slot="append">%</template>
                                    </el-input>
                                    <div>
                                        <span class="text-danger">提现手续费额外从提现中扣除</span><br>
                                        例如：<span style="color: #F56C6C;font-size: 12px">10%</span>的提现手续费：<br>
                                        提现<span style="color: #F56C6C;font-size: 12px">100</span>元，扣除手续费<span
                                                style="color: #F56C6C;font-size: 12px">10</span>元，
                                        实际到手<span style="color: #F56C6C;font-size: 12px">90</span>元
                                    </div>
                                </el-form-item>
                            </div>
                        </el-col>
                    </el-row>
                </el-tab-pane>

                <el-tab-pane label="充值金额" name="recharge">
                    <el-row>
                        <el-col :span="24">
                            <div class="title">
                                <span>充值金额</span>
                            </div>
                            <div class="form-body">
                                <el-form ref="recharge" @submit.native.prevent :model="ruleForm.recharge" label-width="150px">
                                    <el-form-item label="充值1">
                                        <el-input placeholder="请设置充值金额" type="number" v-model="ruleForm.recharge.recharge_money1">
                                            <template slot="append"> 充值金额1</template>
                                        </el-input>
                                        <el-input placeholder="请设置赠送金额" type="number" v-model="ruleForm.recharge.give_money1">
                                            <template slot="append"> 赠送金额1</template>
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item label="充值2">
                                        <el-input placeholder="请设置充值金额" type="number" v-model="ruleForm.recharge.recharge_money2">
                                            <template slot="append"> 充值金额2</template>
                                        </el-input>
                                        <el-input placeholder="请设置赠送金额" type="number" v-model="ruleForm.recharge.give_money2">
                                            <template slot="append"> 赠送金额2</template>
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item label="充值3">
                                        <el-input placeholder="请设置充值金额" type="number" v-model="ruleForm.recharge.recharge_money3">
                                            <template slot="append"> 充值金额3</template>
                                        </el-input>
                                        <el-input placeholder="请设置赠送金额" type="number" v-model="ruleForm.recharge.give_money3">
                                            <template slot="append"> 赠送金额3</template>
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item label="充值4">
                                        <el-input placeholder="请设置充值金额" type="number" v-model="ruleForm.recharge.recharge_money4">
                                            <template slot="append"> 充值金额4</template>
                                        </el-input>
                                        <el-input placeholder="请设置赠送金额" type="number" v-model="ruleForm.recharge.give_money4">
                                            <template slot="append"> 赠送金额4</template>
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item label="充值5">
                                        <el-input placeholder="请设置充值金额" type="number" v-model="ruleForm.recharge.recharge_money5">
                                            <template slot="append"> 充值金额5</template>
                                        </el-input>
                                        <el-input placeholder="请设置赠送金额" type="number" v-model="ruleForm.recharge.give_money5">
                                            <template slot="append"> 赠送金额5</template>
                                        </el-input>
                                    </el-form-item>
                                    <el-form-item label="充值6">
                                        <el-input placeholder="请设置充值金额" type="number" v-model="ruleForm.recharge.recharge_money6">
                                            <template slot="append"> 充值金额6</template>
                                        </el-input>
                                        <el-input placeholder="请设置赠送金额" type="number" v-model="ruleForm.recharge.give_money6">
                                            <template slot="append"> 赠送金额6</template>
                                        </el-input>
                                    </el-form-item>
                                </el-form>
                            </div>
                        </el-col>
                    </el-row>
                </el-tab-pane>
            </el-tabs>
            <el-button :loading="btnLoading" class="button-item" size="small" type="primary"
                       @click="submit" style="position: absolute;top: 10px;right: 10px;">保存
            </el-button>
        </el-form>

    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'balance',
                isShow: true,
                loading: false,
                btnLoading: false,
                file: 'doc',
                hidden: {
                    wechat_app_id: true,
                    wechat_mch_id: false,
                    wechat_pay_secret: true,
                    wechat_cert_pem: true,
                    wechat_key_pem: true,
                },
                ruleForm: {
                    cash_type:[],
                    min_money:0,
                    cash_service_fee:0,
                    is_income_cash: 0,
                    score_rule: '',
                    score_status: 0,
                    integral_status:0,
                    score_price: 0,
                    day_max_money:0,
                    balance_transfer_status: 0,
                    balance_cash_status: 0,
                    balance_charge_status: 0,
                    wechat_app_id: '',
                    wechat_status: 0,
                    wechat_cert_pem: '',
                    wechat_key_pem: '',
                    wechat_mch_id: '',
                    wechat_pay_secret: '',
                    balance_status: 0,
                    pay_password_status:0,
                    recharge:{
                        recharge_money1:0,
                        give_money1:0,
                        recharge_money2:0,
                        give_money2:0,
                        recharge_money3:0,
                        give_money3:0,
                        recharge_money4:0,
                        give_money4:0,
                        recharge_money5:0,
                        give_money5:0,
                    }
                }
            };
        },
        methods: {
            certPemFileSelect(e) {
                if (e.length) {
                    this.payment.wechat_cert_path = e[0].url;
                }
            },
            keyPemFileSelect(e) {
                if (e.length) {
                    this.payment.wechat_key_path = e[0].url;
                }
            },
            submit() {
                let self = this;
                this.btnLoading = true;
                if (this.ruleForm.score_status == 1) {
                    if (this.ruleForm.score_price == 0) {
                        self.$message.error('请填写积分抵扣额度！');
                        return;
                    }
                }
                if (this.ruleForm.wechat_status == 1) {
                    if (this.ruleForm.wechat_app_id == '' || this.ruleForm.wechat_mch_id == '' || this.ruleForm.wechat_key == '') {
                        self.$message.error('请完善微信支付信息！');
                        return;
                    }
                }
                request({
                    params: {
                        r: 'mall/finance/setting'
                    },
                    method: 'post',
                    data: {
                        form: self.ruleForm
                    }
                }).then(e => {

                    this.btnLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                        return;
                    }
                    self.$message.error(e.data.msg);
                }).catch(e => {

                    if (e.data.msg){
                        self.$message.error(e.data.msg);
                    }else{
                        self.$message.error('服务出错~~~');
                    }
                });

            },
            getSetting() {
                let self = this;
                this.loading = true;
                request({
                    params: {
                        r: 'mall/finance/setting'
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data.setting
                    }
                    console.log(this.ruleForm);
                }).catch(e => {
                    console.log(e);
                });
            },

        },
        mounted: function () {
            this.getSetting();
        }
    });
</script>


<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 0;
    }

    .title {
        margin-top: 10px;
        padding: 18px 20px;
        border-top: 1px solid #F3F3F3;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .form-body {
        background-color: #fff;
        padding: 20px 50% 20px 0;
    }
</style>