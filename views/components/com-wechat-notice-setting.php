<style>
    #pane-second .el-card__body {
        background-color: #F3F3F3;
        padding: 0;
    }

    .title {
        padding: 18px 20px;
        margin-top: 12px;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .el-alert .el-alert__description {
        margin-top: 0
    }

    .el-form-item {
        position: relative;
    }

    .send-btn {
        position: absolute !important;
        top: 0;
        right: -95px;
    }
</style>
<template id="com-wechat-notice-setting">
    <el-card shadow="never" style="border:0" v-loading="cardLoading"  v-cloak>
        <div slot="header">
            <span>默认行业消费品,行业ID为31。需要到微信公众号后台开通。</span>
        </div>
        <el-form :model="ruleForm" ref="ruleForm" :rules="rules" size="small" label-width="150px">
            <el-card shadow="never" style="margin-top: 12px;">
            <el-row>
                <el-col :span="24">
                    <el-switch
                            v-model="ruleForm.is_open"
                            active-text="开启消息模板推送"
                            active-value="1"
                            inactive-value="0"
                            active-color="#409EFF">
                    </el-switch>
                </el-col>
            </el-row>
            </el-card>
            <el-card shadow="never" style="margin-top: 12px;">
            <el-row>
                <el-col :span="24">
                    <el-switch
                            v-model="ruleForm.is_miniapp_priority"
                            active-text="是否跳转链接小程序优先"
                            active-value="1"
                            inactive-value="0"
                            active-color="#409EFF">
                    </el-switch>
                </el-col>
            </el-row>
            </el-card>

            <el-card shadow="never" style="margin-top: 12px;">
                <el-row>
                    <el-col :span="24">
                        <el-switch
                                v-model="ruleForm.is_logging"
                                active-text="是否记录数据库日志"
                                active-value="1"
                                inactive-value="0"
                                active-color="#409EFF">
                        </el-switch>
                    </el-col>
                </el-row>
            </el-card>
            <el-button :loading="btnLoading" style="margin-top: 20px;padding: 9px 25px" type="primary" @click="store"
                       size="small">保存
            </el-button>
        </el-form>
    </el-card>
</template>
<script>
    Vue.component('com-wechat-notice-setting', {
        template: '#com-wechat-notice-setting',
        data() {
            return {
                ruleForm: {},
                rules: {},
                btnLoading:false,
                cardLoading:false,
            };
        },
        methods: {
            store() {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'mall/setting/mall',
                    },
                    method: 'post',
                    data: {
                        key:'wechat_notice',
                        value:self.ruleForm
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

            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/setting/mall',
                        key:'wechat_notice'
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>

