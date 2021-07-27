<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-20
 * Time: 11:41
 */
?>

<style>
    .form_box {
        background-color: #fff;
        padding: 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="cardLoading" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">微信小程序配置</div>
        <div class="form_box">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="小程序名称" prop="name">
                            <el-input v-model.trim="ruleForm.name"></el-input>
                        </el-form-item>
                        <el-form-item label="小程序app_id" prop="app_id">
                            <el-input v-model.trim="ruleForm.app_id"></el-input>
                        </el-form-item>
                        <el-form-item label="小程序secret" prop="secret">
                            <el-input @focus="hidden.secret = false"
                                      v-if="hidden.secret"
                                      readonly
                                      placeholder="已隐藏内容，点击查看或编辑">
                            </el-input>
                            <el-input v-else v-model.trim="ruleForm.secret"></el-input>
                        </el-form-item>
                        <el-form-item label="微信支付商户号" prop="mch_id">
                            <el-input v-model.trim="ruleForm.mch_id"></el-input>
                        </el-form-item>
                        <el-form-item label="微信支付Api密钥" prop="pay_secret">
                            <el-input @focus="hidden.pay_secret = false"
                                      v-if="hidden.pay_secret"
                                      readonly
                                      placeholder="已隐藏内容，点击查看或编辑">
                            </el-input>
                            <el-input v-else v-model.trim="ruleForm.pay_secret"></el-input>
                        </el-form-item>
                        <el-form-item label="微信支付apiclient_cert.pem" prop="cert_pem">
                            <el-input @focus="hidden.cert_pem = false"
                                      v-if="hidden.cert_pem"
                                      readonly
                                      type="textarea"
                                      :rows="5"
                                      placeholder="已隐藏内容，点击查看或编辑">
                            </el-input>
                            <el-input v-else type="textarea" :rows="5" v-model="ruleForm.cert_pem"></el-input>
                        </el-form-item>
                        <el-form-item label="微信支付apiclient_key.pem" prop="key_pem">
                            <el-input @focus="hidden.key_pem = false"
                                      v-if="hidden.key_pem"
                                      readonly
                                      type="textarea"
                                      :rows="5"
                                      placeholder="已隐藏内容，点击查看或编辑">
                            </el-input>
                            <el-input v-else type="textarea" :rows="5" v-model="ruleForm.key_pem"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
        </div>
        <el-button class='button-item' :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                hidden: {
                    app_id: true,
                    secret: true,
                    mch_id: false,
                    pay_secret: true,
                    cert_pem: true,
                    key_pem: true,
                },
                ruleForm: {
                    name: '',
                    app_id: '',
                    secret: '',
                    cert_pem: '',
                    pay_secret: '',
                    key_pem: '',
                    mch_id: '',
                },
                rules: {
                    name: [
                        {required: true, message: '请输入小程序名称', trigger: 'change'},
                    ],
                    app_id: [
                        {required: true, message: '请输入app_id', trigger: 'change'},
                    ],
                    secret: [
                        {required: true, message: '请输入secret', trigger: 'change'},
                    ],
                    pay_secret: [
                        {required: true, message: '请输入pay_secret', trigger: 'change'},
                        {max: 32, message: '微信支付Api密钥最多为32个字符', trigger: 'change'},
                    ],
                    mch_id: [
                        {required: true, message: '请输入mch_id', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                       // self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/mpwx/mall/config/setting'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
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
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/mpwx/mall/config/setting',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>
