<style>
    .button-item {
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="padding: 10px 10px;" class="box-card" v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>秒杀编辑</span>
                <div style="float: right;margin-top: -5px">
                    <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
                </div>
            </div>
        </div>

        <div style="background: white;">
            <el-form :model="ruleForm" :rules="rules" size="medium" ref="ruleForm" label-width="200px">
                <el-tabs v-model="activeName">
                    <el-form-item prop="name" label="专题名称">
                        <el-input v-model="ruleForm.name"></el-input>
                    </el-form-item>

                    <el-form-item label="开始时间" prop="start_time">
                        <el-date-picker
                                v-model="ruleForm.start_time"
                                type="datetime"
                                placeholder="选择日期时间">
                        </el-date-picker>
                    </el-form-item>

                    <el-form-item label="结束时间" prop="end_time">
                        <el-date-picker
                                v-model="ruleForm.end_time"
                                type="datetime"
                                placeholder="选择日期时间">
                        </el-date-picker>
                    </el-form-item>
                </el-tabs>
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
                    start_time: '',
                    end_time: '',
                },
                rules: {
                    name: [
                        {required: true, message: '请输入名称', trigger: 'change'},
                    ],
                    start_time: [
                        {required: true, message: '请输入开始时间', trigger: 'change'},
                    ],
                    end_time: [
                        {required: true, message: '请输入结束时间', trigger: 'change'},
                    ],
                },
                keyword: '',
                btnLoading: false,
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
                                r: 'plugin/seckill/mall/special/special/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
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
                        r: 'plugin/seckill/mall/special/special/edit',
                        seckill_id: getQuery('id')
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
