<style>
    .form-body {
        background-color: #fff;
        padding: 20px 50% 20px 0;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

</style>

<div id="com-score" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="ruleForm"
                 :rules="rules"
                 ref="ruleForm"
                 label-width="170px"
                 size="small">
                <div class="form-body">
                    <el-form-item label="用户积分" prop="score">
                        <el-input v-model="ruleForm.setting.score"
                                  type="number">
                            <template slot="append">积分抵扣1元</template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="用户积分使用规则" prop="score_rule">
                        <el-input v-model="ruleForm.setting.score_rule" type="textarea">
                        </el-input>
                    </el-form-item>
                </div>
            <el-button :loading="submitLoading" class="button-item" size="small" type="primary"
                       @click="submit('ruleForm')">保存
            </el-button>
        </el-form>
    </el-card>
</div>

<script>
    Vue.component('com-score', {
        template: '#com-score',
        data() {
            return {
                loading: false,
                submitLoading: false,
                mall: null,
                ruleForm: {
                    name: '',
                    setting: {},
                    recharge: {}
                },
                rules: {

                },
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
                        r: 'mall/setting/setting',
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        this.ruleForm = e.data.data.detail;
                        let setting = this.ruleForm.setting;
                        this.ruleForm.setting.latitude_longitude = setting.latitude + ',' + setting.longitude;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                });
            },
            submit(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.submitLoading = true;
                        request({
                            params: {
                                r: 'mall/setting/setting',
                            },
                            method: 'post',
                            data: {
                                ruleForm: JSON.stringify(this.ruleForm)
                            },
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    } else {
                        this.$message.error('部分参数验证不通过');
                    }
                });
            },
            handleClick(tab, event) {
                console.log(tab, event);
            },
        },
    });
</script>