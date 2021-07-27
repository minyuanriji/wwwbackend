<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }
    .text {
        cursor: pointer;
        color: #419EFB;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="background-color: #f3f3f3;padding: 10px 0 0;" class="box-card" v-loading="cardLoading">
        <div slot="header">
            <div flex="cross:center box:first">
                <div><span @click="$navigate({r:'mall/service/index'})" class="text">话费平台</span>/平台编辑</div>
                <div flex="dir:right">
                    <div>
                        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
                <el-form-item prop="name">
                    <template slot='label'>
                        <span>平台名称</span>
                    </template>
                    <el-input v-model="ruleForm.name"></el-input>
                </el-form-item>
                <el-form-item label="sdk_dir目录" prop="sdk_dir">
                    <el-input  v-model="ruleForm.sdk_dir"></el-input>
                </el-form-item>
                <el-form-item label="收费比例" prop="ratio">
                    <el-input type="number" v-model="ruleForm.ratio"></el-input>
                </el-form-item>
                <el-form-item label="cyd_id" prop="cyd_id">
                    <el-input v-model="ruleForm.cyd_id"></el-input>
                </el-form-item>
                <el-form-item label="秘钥" prop="secret_key">
                    <el-input  v-model="ruleForm.secret_key"></el-input>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    name: '',
                    sdk_dir: '',
                    ratio: 0,
                    cyd_id: '',
                    secret_key: '',
                },
                rules: {
                    name: [
                        {required: true, message: '请输入平台名称', trigger: 'change'},
                    ],
                    sdk_dir: [
                        {required: true, message: '请输入平台SDK目录', trigger: 'change'},
                    ],
                    cyd_id: [
                        {required: true, message: '请输入平台ID', trigger: 'change'},
                    ],
                    secret_key: [
                        {required: true, message: '请输入平台秘钥', trigger: 'change'},
                    ]
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
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/addcredit/mall/plateforms/plateforms/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'plugin/addcredit/mall/plateforms/plateforms/index'
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
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/addcredit/mall/plateforms/plateforms/edit',
                        id: getQuery('id')
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
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
        }
    });
</script>
