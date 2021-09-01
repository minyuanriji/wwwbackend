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
                <div><span @click="$navigate({r:'plugin/shopping_voucher/mall/store/list'})" class="text">商户管理</span>/商户编辑</div>
                <div flex="dir:right">
                    <div>
                        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
                <el-form-item label="门店" prop="mch_id">
                    <el-autocomplete size="small"
                                     v-model="ruleForm.name"
                                     value-key="name"
                                     :disabled="ruleForm.id ? true : false"
                                     :fetch-suggestions="querySearchAsync"
                                     placeholder="请输入搜索内容"
                                     @select="inviterClick">
                    </el-autocomplete>
                </el-form-item>

                <el-form-item label="返购物券比例" prop="ratio">
                    <el-input type="number" v-model="ruleForm.ratio" style="width: 100px;">
                        <template slot="append">%</template>
                    </el-input>
                    <span style="color: red;font-size: 12px">例如：10 门店扫码100 返10购物券</span>
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
                    ratio: '',
                    mch_id: '',
                },
                rules: {
                    mch_id: [
                        {required: true, message: '请选择商户', trigger: 'change'},
                    ],
                    ratio: [
                        {required: true, message: '请输入购物券比例', trigger: 'change'},
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
                this.ruleForm.mch_id = row.mch_id;
            },
            searchUser(cb) {
                request({
                    params: {
                        r: 'plugin/shopping_voucher/mall/store/mch-list',
                        keyword: this.keyword,
                        mch_id: this.ruleForm.mch_id,
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
                                r: 'plugin/shopping_voucher/mall/store/edit'
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
                                    r: 'plugin/shopping_voucher/mall/store/list'
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
                        r: 'plugin/shopping_voucher/mall/store/edit',
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
