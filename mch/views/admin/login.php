<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-09
 * Time: 10:36
 */
$indSetting = \app\logic\OptionLogic::get(\app\models\Option::NAME_IND_SETTING);
?>
<script>const passportBg = '<?=($indSetting && !empty($indSetting['passport_bg'])) ? $indSetting['passport_bg'] : ''?>';</script>
<style>
    .site-wrapper.site-page--login {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        /*background-color: rgba(38, 50, 56, 0.5);*/
        overflow: hidden;
    }

    .site-wrapper.site-page--login:before {
        position: fixed;
        top: 0;
        left: 0;
        z-index: -1;
        width: 100%;
        height: 100%;
        content: "";
        /*background-color: #fa8bff;*/
        /*background-image: linear-gradient(45deg, #fa8bff 0%, #2bd2ff 52%, #2bff88 90%);*/
        background-image: url("/web/statics/img/admin/BG.png");
        background-size: cover;
    }

    .site-wrapper.site-page--login .site-content__wrapper {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        padding: 0;
        margin: 0;
        overflow-x: hidden;
        overflow-y: auto;
        background-color: transparent;
    }

    .site-wrapper.site-page--login .site-content {
        min-height: 100%;
        padding: 30px 500px 30px 30px;
    }

    .site-wrapper.site-page--login .brand-info {
        margin: 120px 100px 0 90px;
        color: #fff;
        position: relative;
    }

    .site-wrapper.site-page--login .logo {
        position: absolute;
        /*left: 40px;*/
        /*top: 40px;*/
        bottom: 150px;
        height: 60px;
    }

    .site-wrapper.site-page--login .brand-info__text {
        margin: 0 0 22px 0;
        font-size: 48px;
        font-weight: 400;
        text-transform: uppercase;
    }

    .site-wrapper.site-page--login .brand-info__intro {
        margin: 10px 0;
        font-size: 16px;
        line-height: 1.58;
        opacity: 0.6;
    }

    .site-wrapper.site-page--login .login-main {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        margin: auto;
        padding: 0 20px;
        width: 370px;
        height: 360px;
        /*background-color: #fff;*/
    }

    .site-wrapper.site-page--login .login-title {
        font-size: 16px;
        color: #fff;
    }

    .site-wrapper.site-page--login .login-captcha {
        overflow: hidden;
    }

    .site-wrapper.site-page--login .login-captcha > img {
        width: 100%;
        cursor: pointer;
    }

    .site-wrapper.site-page--login .login-btn-submit {
        width: 100%;
        /*margin-top: 38px;*/
    }

    .pic-captcha {
        width: 100px;
        height: 36px;
        vertical-align: middle;
        cursor: pointer;
    }
</style>

<div id="app" v-cloak>

    <div class="site-wrapper site-page--login">
        <div class="site-content__wrapper">
            <div class="site-content">
                <div class="brand-info">
<!--                    <img class="logo" :src="login_logo" alt="">-->
<!--                    <h2 class="brand-info__text">补商汇后台管理系统</h2>-->
<!--                    <p class="brand-info__intro">补商汇商城后台管理系统。</p>-->
                </div>
                <div class="login-main">
                    <h3 class="login-title">商户登录</h3>
                    <el-form :model="ruleForm" :rules="rules2" ref="ruleForm" @keyup.enter.native="login('ruleForm')" status-icon>
                        <el-form-item prop="username">
                            <el-input v-model="ruleForm.username" placeholder="帐号"></el-input>
                        </el-form-item>
                        <el-form-item prop="password">
                            <el-input v-model="ruleForm.password" type="password" placeholder="密码"></el-input>
                        </el-form-item>
                        <el-form-item prop="captcha">
                            <el-row :gutter="20">
                                <el-col :span="14">
                                    <el-input v-model="ruleForm.captcha" placeholder="验证码"></el-input>
                                </el-col>
                                <el-col :span="10" class="login-captcha">
                                    <img :src="pic_captcha_src" class="pic-captcha" @click="loadPicCaptcha">
                                </el-col>
                            </el-row>
                        </el-form-item>
                        <el-form-item style="color: #fff">
                            <el-checkbox v-model="ruleForm.checked">记住我，以后自动登录</el-checkbox>
                        </el-form-item>
                        <el-form-item>
                            <el-button class="login-btn-submit" type="primary" @click="login('ruleForm')">登录</el-button>
                        </el-form-item>
                    </el-form>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                login_bg: passportBg ? passportBg : (_baseUrl + '/statics/img/admin/BG.png'),
                login_logo: _siteLogo,
                btnLoading: false,
                dialogFormVisible: false,
                ruleForm: {
                    username: '',
                    password: '',
                    captcha: '',
                    checked: false
                },
                rules2: {
                    username: [
                        {required: true, message: '请输入用户名', trigger: 'blur'},
                    ],
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'},
                    ],
                    captcha: [
                        {required: true, message: '请输入右侧图片上的文字', trigger: 'blur'},
                    ],
                },
                pic_captcha_src: null,
            };
        },
        created() {
            this.loadPicCaptcha();
        },
        methods: {
            login(formName) {
                let self = this;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mch/admin/login'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                self.$navigate({
                                    r: e.data.data.url,
                                });
                            } else {
                                this.loadPicCaptcha();
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            console.log(e);
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            loadPicCaptcha() {
                this.$request({
                    noHandleError: true,
                    params: {
                        r: 'site/captcha',
                        refresh: true,
                    },
                }).then(response => {
                }).catch(response => {
                    if (response.data.url) {
                        this.pic_captcha_src = response.data.url;
                    }
                });
            },
        },
        mounted: function () {

        }
    });
</script>
