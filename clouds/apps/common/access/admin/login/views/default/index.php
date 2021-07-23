<template id="content">
    <div class="main">

        <a-form id="formLogin" class="user-layout-login" ref="formLogin">

            <a-tabs :activeKey="tabActiveKey"  @change="callback">
                <a-tab-pane key="1" tab="Tab 1">
                    Content of Tab Pane 1
                </a-tab-pane>
                <a-tab-pane key="2" tab="Tab 2" force-render>
                    Content of Tab Pane 2
                </a-tab-pane>
                <a-tab-pane key="3" tab="Tab 3">
                    Content of Tab Pane 3
                </a-tab-pane>
            </a-tabs>

            <a-tabs :activeKey="customActiveKey"
                    :tabBarStyle="{ textAlign: 'center', borderBottom: 'unset' }"
                    >
                <a-tab-pane key="tab1" tab="账号密码登陆">
                    <a-alert v-if="isLoginError" type="error" showIcon style="margin-bottom: 24px;" message="账号密码不正确" />
                    <a-form-item>
                        <a-input size="large" type="text" placeholder="用户名">
                            <a-icon slot="prefix" type="user" :style="{ color: 'rgba(0,0,0,.25)' }"/>
                        </a-input>
                    </a-form-item>

                    <a-form-item>
                        <a-input-password size="large" placeholder="密码"/>
                            <a-icon slot="prefix" type="lock" :style="{ color: 'rgba(0,0,0,.25)' }"/>
                        </a-input-password>
                    </a-form-item>
                </a-tab-pane>
                <a-tab-pane key="tab2" tab="手机号码登陆">
                    <a-form-item>
                        <a-input size="large" type="text" placeholder="手机号码" >
                            <a-icon slot="prefix" type="mobile" :style="{ color: 'rgba(0,0,0,.25)' }"/>
                        </a-input>
                    </a-form-item>

                    <a-row :gutter="16">
                        <a-col class="gutter-row" :span="16">
                            <a-form-item>
                                <a-input size="large" type="text" placeholder="验证码" >
                                    <a-icon slot="prefix" type="mail" :style="{ color: 'rgba(0,0,0,.25)' }"/>
                                </a-input>
                            </a-form-item>
                        </a-col>
                        <a-col class="gutter-row" :span="8">
                            <a-button class="getCaptcha" tabindex="-1" :disabled="state.smsSendBtn" v-text="'获取验证码'"></a-button>
                        </a-col>
                    </a-row>
                </a-tab-pane>
            </a-tabs>

            <a-form-item>
                <a-checkbox>记住我</a-checkbox>
                <router-link  class="forge-password" style="float: right;">忘记密码</router-link>
            </a-form-item>

            <a-form-item style="margin-top:24px">
                <a-button
                        size="large"
                        type="primary"
                        htmlType="submit"
                        class="login-button"
                        :loading="state.loginBtn"
                        :disabled="state.loginBtn">登陆</a-button>
            </a-form-item>

            <div class="user-login-other">
                <span>其它登陆方式</span>
                <a>
                    <a-icon class="item-icon" type="alipay-circle"></a-icon>
                </a>
                <a>
                    <a-icon class="item-icon" type="taobao-circle"></a-icon>
                </a>
                <a>
                    <a-icon class="item-icon" type="weibo-circle"></a-icon>
                </a>
            </div>
        </a-form>

    </div>
</template>

<script>
    new Vue({
        el: '#content',
        data() {
            return {
                tabActiveKey: 1,


                customActiveKey: 'tab1',
                loginBtn: false,
                loginType: 0,
                isLoginError: false,
                requiredTwoStepCaptcha: false,
                stepCaptchaVisible: false,
                state: {
                    time: 60,
                    loginBtn: false,
                    // login type: 0 email, 1 username, 2 telephone
                    loginType: 0,
                    smsSendBtn: false
                }
            }
        },
        created() {
        },
        methods: {
            callback(key) {
                console.log(key);
            }
        },
    });
</script>

<style type="text/css">
.user-layout-login label{font-size: 14px;}
.user-layout-login .getCaptcha{
    display: block;
    width: 100%;
    height: 40px;
}
.user-layout-login .forge-password{
    font-size: 14px;
}
.user-layout-login button.login-button{
    padding: 0 15px;
    font-size: 16px;
    height: 40px;
    width: 100%;
}
.user-layout-login .user-login-other{
    text-align: left;
    margin-top: 24px;
    line-height: 22px;
}
.user-layout-login .user-login-other .item-icon{
    font-size: 24px;
    color: rgba(0, 0, 0, 0.2);
    margin-left: 16px;
    vertical-align: middle;
    cursor: pointer;
    transition: color 0.3s;
}
.user-layout-login .user-login-other .item-icon &:hover{
    color: #1890ff;
}
.user-layout-login .user-login-other .register{
    float: right;
}

</style>

