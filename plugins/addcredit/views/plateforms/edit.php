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

    .button-item {
        padding: 9px 25px;
    }

    .form-body .item {
        width: 300px;
        margin-bottom: 50px;
        margin-right: 25px;
    }

    .item-img {
        height: 550px;
        padding: 25px 10px;
        border-radius: 30px;
        border: 1px solid #CCCCCC;
        background-color: #fff;
    }

    .item .el-form-item {
        width: 300px;
        margin: 20px auto;
    }

    .left-setting-menu {
        width: 260px;
    }

    .left-setting-menu .el-form-item {
        height: 60px;
        padding-left: 20px;
        display: flex;
        align-items: center;
        margin-bottom: 0;
        cursor: pointer;
    }

    .left-setting-menu .el-form-item .el-form-item__label {
        cursor: pointer;
    }

    .left-setting-menu .el-form-item.active {
        background-color: #F3F5F6;
        border-top-left-radius: 10px;
        width: 105%;
        border-bottom-left-radius: 10px;
    }

    .left-setting-menu .el-form-item .el-form-item__content {
        margin-left: 0 !important
    }

    .no-radius {
        border-top-left-radius: 0 !important;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .reset {
        position: absolute;
        top: 3px;
        left: 90px;
    }

    .app-tip {
        position: absolute;
        right: 24px;
        top: 16px;
        height: 72px;
        line-height: 72px;
        max-width: calc(100% - 78px);
    }

    .app-tip:before {
        content: ' ';
        width: 0;
        height: 0;
        border-color: inherit;
        position: absolute;
        top: -32px;
        right: 100px;
        border-width: 16px;
        border-style: solid;
    }

    .tip-content {
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0 28px;
        font-size: 28px;
    }
    .btn-abs{
        position: absolute;
        top: 12px;
        right: 18px;
    }

    .form-body .app-share {
        padding-top: 12px;
        border-top: 1px solid #e2e2e2;
        margin-top: -20px;
    }

    .form-body .app-share .app-share-bg {
        position: relative;
        width: 310px;
        height: 360px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }

    .form-body .app-share .app-share-bg .title {
        width: 160px;
        height: 29px;
        line-height: 1;
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .form-body .app-share .app-share-bg .pic-image {
        background-repeat: no-repeat;
        background-position: 0 0;
        background-size: cover;
        width: 160px;
        height: 130px;
    }

</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="background-color: #f3f3f3;padding: 10px 10px;" class="box-card" v-loading="cardLoading">

        <el-tabs v-model="activeName">
            <el-tab-pane label="基础信息" name="Basics" style="background: white">
                <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="200px" style="padding:20px 0;">
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
                    <el-form-item label="class_dir目录" prop="class_dir">
                        <el-input  v-model="ruleForm.class_dir"></el-input>
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
                    <el-form-item >
                        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="big">保存</el-button>
                    </el-form-item>
                </el-form>
            </el-tab-pane>
            <el-tab-pane label="产品信息" name="product">

                <div style="padding:20px 20px;background:white">
                    <el-table v-loading="listLoading" :data="ruleForm.product_json_data" >

                        <el-table-column prop="product_id" label="ID" width="100"></el-table-column>
                        <el-table-column prop="price" label="价格" width="100"></el-table-column>

                        <el-table-column label="类型">
                            <template slot-scope="scope">
                                <com-ellipsis v-if="scope.row.type =='slow'">慢充</com-ellipsis>
                                <com-ellipsis v-else="">快充</com-ellipsis>
                            </template>
                        </el-table-column>

                        <el-table-column label="操作" width="220">
                            <template slot-scope="scope">
                                <el-button @click="delProduct(ruleForm.id, scope.row.product_id, scope.$index)" circle type="text" size="mini">
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>

                    </el-table>
                    <el-button :loading="btnLoading" type="primary" @click="addProduct" size="big" style="margin-top:10px;">新增</el-button>
                </div>

            </el-tab-pane>
        </el-tabs>
    </el-card>

    <!-- 添加产品 -->
    <el-dialog title="添加产品" :visible.sync="dialogProduct" width="30%">
        <el-form :model="productForm" label-width="80px" :rules="productFormRules" ref="productForm">
            <el-form-item label="产品ID" prop="product_id" size="small">
                <el-input type="number" v-model="productForm.product_id"></el-input>
            </el-form-item>
            <el-form-item label="金额" prop="product_price" size="small">
                <el-input type="number" v-model="productForm.product_price"></el-input>
            </el-form-item>
            <el-form-item label="类型" prop="product_type">
                <el-radio v-model="productForm.product_type" label="1">快充</el-radio>
                <el-radio v-model="productForm.product_type" label="2">慢充</el-radio>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogProduct = false">取消</el-button>
            <el-button :loading="pdLoading" type="primary" @click="productSubmit">添加</el-button>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'Basics',
                ruleForm: {
                    name: '',
                    sdk_dir: '',
                    ratio: '',
                    cyd_id: '',
                    secret_key: '',
                    parent_id: '',
                    transfer_rate: '',
                    class_dir: '',
                    product_json_data: '',
                },
                rules: {
                    name: [
                        {required: true, message: '请输入平台名称', trigger: 'change'},
                    ],
                    sdk_dir: [
                        {required: true, message: '请输入平台SDK目录', trigger: 'change'},
                    ],
                    class_dir: [
                        {required: true, message: '请输入平台class目录', trigger: 'change'},
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
                dialogProduct: false,
                productForm: {
                    product_id: '',
                    product_price: '',
                    product_type: '',
                    type: '',
                    price: '',
                    id:'',
                },
                pdLoading: false,
                productFormRules: {
                    product_id: [
                        {required: true, message: '请传入产品ID', trigger: 'blur'},
                    ],
                    product_price: [
                        {required: true, message: '金额不能为空', trigger: 'blur'},
                    ],
                    product_type: [
                        {required: true, message: '类型不能为空', trigger: 'change'},
                    ],
                },
            };
        },
        methods: {
            handleClick(tab, event) {
                this.activeName = tab.name;
            },

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

            delProduct(id, product_id, index){
                this.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/addcredit/mall/plateforms/plateforms/del-product'
                        },
                        method: 'post',
                        data: {
                            id:id,
                            product_id:product_id
                        }
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.$message.success(e.data.msg);
                            this.ruleForm.product_json_data.splice(index, 1)
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.$message.error(e.data.msg);
                    });
                }).catch(() => {

                });
            },

            addProduct() {
                this.dialogProduct = true;
                this.productForm.id = this.ruleForm.id;
            },

            productSubmit() {
                var self = this;
                this.$refs.productForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, self.productForm);
                        self.pdLoading = true;
                        request({
                            params: {
                                r: 'plugin/addcredit/mall/plateforms/plateforms/add-product',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                self.pdLoading = false;
                                self.dialogProduct = false;
                                location.reload();
                            } else {
                                self.$message.error(e.data.msg);
                            }
                            self.pdLoading = false;
                        }).catch(e => {
                            self.pdLoading = false;
                        });
                    }
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
