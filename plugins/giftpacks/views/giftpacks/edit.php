<div id="edit_app" v-cloak>

    <el-dialog :title="dailogTitle" :visible.sync="dialogFormVisible">

        <el-form :rules="rules" ref="formData" label-width="80px" :model="formData" size="small">
            <el-form-item label="标题" prop="title">
                <el-input v-model="formData.title"></el-input>
            </el-form-item>
            <el-form-item label="描述" prop="descript">
                <el-input type="textarea" :rows="2" placeholder="请输入描述" v-model="formData.descript"></el-input>
            </el-form-item>
            <el-form-item label="封面" prop="cover_pic">
                <com-attachment :multiple="false" :max="1" v-model="formData.cover_pic">
                    <el-tooltip class="item"
                                effect="dark"
                                content="建议尺寸:240 * 240"
                                placement="top">
                        <el-button size="mini">选择文件</el-button>
                    </el-tooltip>
                </com-attachment>
                <com-image mode="aspectFill" width='80px' height='80px' :src="formData.cover_pic"></com-image>
            </el-form-item>
            <el-row>
                <el-col :span="12">
                    <el-form-item label="库存" prop="max_stock">
                        <el-input type="number" style="width:150px" v-model="formData.max_stock">
                            <template slot="append">件</template>
                        </el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="价格" prop="price">
                        <el-input type="number" style="width:150px" v-model="formData.price">
                            <template slot="append">元</template>
                        </el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="12">
                    <el-form-item label="利润" prop="profit_price">
                        <el-input type="number" style="width:150px" v-model="formData.profit_price">
                            <template slot="append">元</template>
                        </el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="限购" prop="purchase_limits_num">
                        <el-input type="number" style="width:150px" v-model="formData.purchase_limits_num">
                            <template slot="append">件</template>
                        </el-input>
                    </el-form-item>
                </el-col>
            </el-row>

            <el-form-item label="支付模式" prop="allow_currency">
                <el-radio v-model="formData.allow_currency" label="money">现金</el-radio>
                <el-radio v-model="formData.allow_currency" label="integral">红包</el-radio>
                <el-card shadow="never" v-if="formData.allow_currency == 'money'">
                    <el-form-item label="返红包" prop="integral_enable">
                        <el-switch
                                v-model="formData.integral_enable"
                                active-text="启用"
                                inactive-text="关闭">
                        </el-switch>
                    </el-form-item>
                    <el-form-item label="数量" prop="integral_give_num" v-if="formData.integral_enable">
                        <el-input type="number" style="width:150px" v-model="formData.integral_give_num"></el-input>
                    </el-form-item>
                </el-card>
            </el-form-item>

            <el-form-item label="拼团" prop="group_enable">
                <el-switch
                        v-model="formData.group_enable"
                        active-text="启用"
                        inactive-text="关闭">
                </el-switch>
                <el-card v-if="formData.group_enable" shadow="never">
                    <el-form-item label="拼团价" prop="group_price">
                        <el-input type="number" style="width:150px" v-model="formData.group_price"></el-input>
                    </el-form-item>
                    <el-form-item label="人数" prop="group_need_num">
                        <el-input type="number" style="width:150px" v-model="formData.group_need_num"></el-input>
                    </el-form-item>
                    <el-form-item label="有效期" prop="group_expire_time">
                        <el-input placeholder="请输入内容" style="width:150px"  v-model="formData.group_expire_time">
                            <template slot="append">时</template>
                        </el-input>
                    </el-form-item>
                </el-card>
            </el-form-item>

        </el-form>

        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogFormVisible = false">取 消</el-button>
            <el-button :loading="btnLoading" type="primary" @click="save()">确 定</el-button>
        </div>
    </el-dialog>
</div>
<script>
    function initFormData(){
        return {
            title: '',
            cover_pic: '',
            max_stock: 0,
            price: 0,
            profit_price: 0,
            purchase_limits_num: 1,
            descript: '',
            group_enable: false,
            group_price: 0,
            group_need_num: 0,
            group_expire_time: '',
            allow_currency: 'money',
            integral_enable: false,
            integral_give_num: 0
        };
    }
    const editApp = new Vue({
        el: '#edit_app',
        data: {
            dailogTitle: '',
            dialogFormVisible: false,
            btnLoading: false,
            formData: initFormData(),
            rules: {
                title: [
                    {required: true, message: '标题不能为空', trigger: 'change'}
                ],
                descript: [
                    {required: true, message: '描述不能为空', trigger: 'change'}
                ],
                cover_pic: [
                    {required: true, message: '封面不能为空', trigger: 'change'}
                ],
                max_stock: [
                    {required: true, message: '库存不能为空', trigger: 'change'}
                ],
                price: [
                    {required: true, message: '价格不能为空', trigger: 'change'}
                ],
                profit_price: [
                    {required: true, message: '利润不能为空', trigger: 'change'}
                ],
                purchase_limits_num:[
                    {required: true, message: '限购数量不能为空', trigger: 'change'}
                ],
                allow_currency: [
                    {required: true, message: '请选择支付模式', trigger: 'change'}
                ]
            },
            savedCallFn : null
        },
        methods: {
            show(title, row, fn){
                this.dailogTitle = title;
                this.dialogFormVisible = true;
                this.savedCallFn = fn;
                if(row != null){
                    var groupEnable = row.group_enable == 1 ? true : false;
                    var integralEnable = row.integral_enable == 1 ? true : false;
                    this.formData = row;
                    this.formData['group_enable'] = groupEnable;
                    this.formData['integral_enable'] = integralEnable;
                }else{
                    this.formData = initFormData();
                }
            },
            hide(){
                this.dialogFormVisible = false;
            },
            save() {
                let formData = JSON.parse(JSON.stringify(this.formData));
                this.$refs['formData'].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        formData['integral_enable']   = formData['integral_enable'] ? 1 : 0;
                        formData['group_enable']      = formData['group_enable'] ? 1 : 0;
                        formData['group_expire_time'] = 3600 * formData['group_expire_time'];
                        request({
                            params: {
                                r: 'plugin/giftpacks/mall/giftpacks/edit'
                            },
                            method: 'post',
                            data: formData
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                self.hide();
                                self.savedCallFn();
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
