<?php

?>
<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0;min-width: 1000px;" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">

            <el-tabs v-model="activeName">
                <el-tab-pane label="基础设置" name="basic">
                    <el-row>
                        <el-col :span="24">
                            <div class="title">
                                <span>基础设置</span>
                            </div>
                            <div class="form-body">
                                <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="172px" size="small">
                                    <el-form-item label="app_key" prop="app_key">
                                        <el-input v-model="ruleForm.app_key" placeholder="请输入"></el-input>
                                    </el-form-item>
                                    <el-form-item label="app_secret" prop="app_secret">
                                        <el-input v-model="ruleForm.app_secret" placeholder="请输入"></el-input>
                                    </el-form-item>
                                    <el-form-item label="adzone_id" prop="adzone_id">
                                        <el-input v-model="ruleForm.adzone_id" placeholder="请输入"></el-input>
                                    </el-form-item>
                                    <el-form-item label="邀请码" prop="invite_code">
                                        <el-input v-model="ruleForm.invite_code" placeholder="请输入"></el-input>
                                    </el-form-item>
                                    <el-form-item label="会员ID" prop="special_id">
                                        <el-input v-model="ruleForm.special_id" placeholder="请输入"></el-input>
                                    </el-form-item>
                                    <el-form-item label=" ">
                                        <el-button @click="submit" :loading="btnLoading" size="big" type="primary" >保存</el-button>
                                    </el-form-item>
                                </el-form>
                            </div>
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
                activeName: 'basic',
                isShow: true,
                loading: false,
                btnLoading: false,
                ruleForm: {
                    app_key: '',
                    app_secret: '',
                    adzone_id: '',
                    invite_code: '',
                    special_id: '',
                },
                rules: {
                   app_key: [
                        {required: true, message: '请输入APP KEY', trigger: 'change'},
                    ],
                    app_secret: [
                        {required: true, message: '请输入SECRET KEY', trigger: 'change'},
                    ],
                    adzone_id: [
                        {required: true, message: '请输入广告位', trigger: 'change'},
                    ],
                    invite_code: [
                        {required: true, message: '请输入邀请码', trigger: 'change'},
                    ],
                    special_id: [
                        {required: true, message: '请输入会员ID', trigger: 'change'},
                    ]
                }
            };
        },
        methods: {

            submit() {
                this.$refs['ruleForm'].validate((valid) => {
                    if (valid) {
                        let self = this;
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/taobao/mall/setting/save'
                            },
                            method: 'post',
                            data: {data:self.ruleForm}
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                            }else{
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            if (e.data.msg){
                                self.$message.error(e.data.msg);
                            }else{
                                self.$message.error('服务出错~~~');
                            }
                        });
                    }
                });
            },
            getSetting() {
                let self = this;
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/taobao/mall/setting/index'
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        if(e.data.data.setting){
                            this.ruleForm = e.data.data.setting;
                        }
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
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