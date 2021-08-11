<style>
    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 20px;
    }

    .open-img .el-dialog {
        margin-top: 0 !important;
    }

    .click-img {
        width: 100%;
    }

    .el-input-group__append {
        background-color: #fff
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item >账号设置</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="160px" size="small">
                <el-row>
                    <el-card shadow="never" style="margin-bottom: 20px">
                        <el-col :span="12">
                            <el-form-item label="原密码" prop="old_password">
                                <el-input type="password" v-model="ruleForm.old_password"></el-input>
                            </el-form-item>
                            <el-form-item label="新密码" prop="new_password">
                                <el-input type="password" v-model="ruleForm.new_password"></el-input>
                            </el-form-item>
                            <el-form-item label="确认密码" prop="confirm_password">
                                <el-input type="password" v-model="ruleForm.confirm_password"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-card>

                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存
        </el-button>
        <el-dialog :visible.sync="dialogImg" width="45%" class="open-img">
            <img :src="click_img" class="click-img" alt="">
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var self = this;
            var confirmPwdValidate = function(rule, value, callback){
                if (typeof self.ruleForm == "undefined" ||
                        self.ruleForm.new_password != value) {
                    callback(new Error('确认密码错误'))
                } else {
                    callback()
                }
            };
            return {
                ruleForm: {},
                rules: {
                    old_password: [
                        {required: true, message: '原密码', trigger: 'change'},
                    ],
                    new_password: [
                        {required: true, message: '新密码', trigger: 'change'},
                    ],
                    confirm_password: [
                        {required: true, message: '确认密码', trigger: 'change'},
                        { validator: confirmPwdValidate, trigger: 'change' }
                    ]
                },
                btnLoading: false,
                tableLoading: false,
                cardLoading: false,
                dialogImg: false,
                click_img: ''
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mch/mch/password'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mch/mch/password'
                                })
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
            }
        },
        mounted: function () {}
    });
</script>
