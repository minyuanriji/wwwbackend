<?php
Yii::$app->loadComponentView('com-sms-setting');
Yii::$app->loadComponentView('com-mail-setting');
Yii::$app->loadComponentView('com-wechat-notice-setting');
?>
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
<div id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;" v-loading="loading">
        <el-tabs v-model="activeName" @tab-click="handleClick" >

            <el-tab-pane label="公众号模板消息通知" name="one">
                <el-row>
                    <el-col :span="24">
                        <com-wechat-notice-setting></com-wechat-notice-setting>
                    </el-col>
                </el-row>
            </el-tab-pane>

            <el-tab-pane label="短信通知" name="second" v-if="isShow">
                <el-row>
                    <el-col :span="24">
                        <com-sms-setting></com-sms-setting>
                    </el-col>
                </el-row>
            </el-tab-pane>

            <el-tab-pane label="邮件通知" name="third">
                <el-row>
                    <el-col :span="24">
                        <com-mail-setting></com-mail-setting>
                    </el-col>
                </el-row>
            </el-tab-pane>


        </el-tabs>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'one',
                isShow: true,
                loading: false,
            };
        },
        methods: {
            handleClick(tab, event) {
                console.log(tab, event);
            },
            getRole() {
                return;
                let self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'mall/setting/role'
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0 && e.data.data == 'mch') {
                        self.isShow = false;
                        self.activeName = 'second';
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            this.getRole();
        }
    });
</script>

