<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
    }
    .button-item {
        padding: 9px 25px;
    }
    .text {
        cursor: pointer;
        color: #419EFB;
    }
    .form-body input {
        width: 300px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="background-color: #f3f3f3;padding: 10px 0 0;" class="box-card" v-loading="cardLoading">
        <div slot="header">
            <div flex="cross:center box:first">
                <div><span @click="$navigate({r:'plugin/addcredit/mall/plateforms/plateforms/index'})" class="text">话费平台</span>/平台编辑</div>
                <div flex="dir:right">
                    <div>
                        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
                <el-form-item prop="name" label="平台名称">
                    <el-input v-model="ruleForm.name"></el-input>
                </el-form-item>
                <el-form-item label="cyd_id" prop="cyd_id">
                    <el-input v-model="ruleForm.cyd_id"></el-input>
                </el-form-item>
                <el-form-item label="秘钥" prop="secret_key">
                    <el-input  v-model="ruleForm.secret_key"></el-input>
                </el-form-item>
                <el-form-item label="sdk_dir目录" prop="sdk_dir">
                    <el-input  v-model="ruleForm.sdk_dir"></el-input>
                </el-form-item>
                <el-form-item label="服务费比例" prop="transfer_rate">
                    <el-input type="number" v-model="ruleForm.transfer_rate" style="width: 100px;">
                        <template slot="append">%</template>
                    </el-input>
                    <span style="color: red;font-size: 12px">请输入服务费0-100</span>
                </el-form-item>
                <el-form-item label="红包收费比例" prop="ratio">
                    <el-input type="number" v-model="ruleForm.ratio" style="width: 100px;">
                        <template slot="append">%</template>
                    </el-input>
                    <span style="color: red;font-size: 12px">例如：10   充值100元 红包扣取110</span>
                </el-form-item>
                <el-form-item label="推荐人" prop="parent_id">
                    <el-autocomplete size="small"
                                     v-model="ruleForm.parent_name"
                                     value-key="nickname"
                                     :fetch-suggestions="querySearchAsync"
                                     placeholder="请输入搜索内容"
                                     @select="inviterClick">
                    </el-autocomplete>
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
                    ratio: '',
                    cyd_id: '',
                    secret_key: '',
                    parent_id: '',
                    transfer_rate: '',
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
                    ],
                    transfer_rate: [
                        {required: true, message: '请输入服务费0-100', trigger: 'change'},
                    ]
                },
                btnLoading: false,
                cardLoading: false,
                keyword: '',
            };
        },
        methods: {
            //搜索
            querySearchAsync(queryString, cb) {
                this.keyword = queryString;
                this.searchUser(cb);
            },
            inviterClick(row) {
                this.ruleForm.parent_id = row.id;
            },
            searchUser(cb) {
                request({
                    params: {
                        r: 'mall/user/get-can-bind-inviter',
                        keyword: this.keyword,
                        user_id: this.ruleForm.parent_id,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
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
