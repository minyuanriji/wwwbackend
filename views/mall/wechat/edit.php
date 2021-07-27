<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-20
 * Time: 15:38
 */

$this->title = "公众号配置";
?>
<div id="app" v-cloak>
    <el-card v-loading="dataLoading" class="box-card" shadow="never"
             style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>公众号设置</span>
            <div style="float: right; margin: -5px 0">
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="submit('ruleForm')" size="small">
                    保存
                </el-button>
            </div>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="9">
                        <el-form-item label="公众号名称" prop="name">
                            <el-input v-model="ruleForm.name"></el-input>
                        </el-form-item>
                        <el-form-item label="开发者ID(AppID)" prop="app_id">
                            <el-input v-model="ruleForm.app_id"></el-input>
                        </el-form-item>
                        <el-form-item label="开发者密码(AppSecret)" prop="secret">
                            <el-input v-model="ruleForm.secret"></el-input>
                        </el-form-item>
                        <el-form-item label="令牌(ToKen)" prop="token">
                            <el-input v-model="ruleForm.token"></el-input>
                        </el-form-item>
                        <el-form-item label="消息加解密密钥(EncodingAESKey)" prop="aes_key">
                            <el-input v-model="ruleForm.aes_key"></el-input>
                        </el-form-item>
                        <el-form-item label="公众号二维码" prop="pic_url">
                            <com-attachment :multiple="false" :max="1" @selected="qrcodePicUrl">
                                <el-tooltip class="item" effect="dark" content="建议尺寸44*44" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <com-image width="80px" height="80px" mode="aspectFill" :src="ruleForm.qrcode"></com-image>
                        </el-form-item>

<!--                        <el-form-item>-->
<!--                            <el-button class="button-item" :loading="btnLoading" type="primary" @click="submit('ruleForm')" size="small">-->
<!--                                保存-->
<!--                            </el-button>-->
<!--                        </el-form-item>-->
                    </el-col>
                </el-row>
            </el-form>
        </div>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                dataLoading: false,
                rules: {
                    name: [
                        {required: true, message: '请选择会员等级', trigger: 'change'},
                    ],
                    app_id: [
                        {required: true, message: '请输入APPID', trigger: 'change'},
                    ],
                    secret: [
                        {required: true, message: '请输入密钥', trigger: 'change'},
                    ],
                    token: [
                        {required: true, message: '请输入token', trigger: 'change'},
                    ],
                },
                wechat: null,
                btnLoading: false,
                ruleForm: {
                    name: '',
                    app_id: '',
                    secret: '',
                    token: '',
                    aes_key: '',
                    qrcode: ''
                }
            };
        },
        created() {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/wechat/edit',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.ruleForm = e.data.data.wechat;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            qrcodePicUrl(e) {
                if (e.length) {
                    this.ruleForm.qrcode = e[0].url;
                }
            },
            submit(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/wechat/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
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

        }
    });
</script>