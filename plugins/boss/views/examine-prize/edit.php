<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/boss/mall/prize/index'})">
                        奖金池
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑奖金池</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-row>
                    <el-col :span="24">

                        <el-form-item label="奖池编号" prop="award_sn">
                            <el-input v-model="ruleForm.award_sn" :disabled="true" style="width: 220px"></el-input>
                        </el-form-item>

                        <el-form-item label="奖池名称" prop="name">
                            <el-input v-model="ruleForm.name" placeholder="请输入名称" style="width: 220px"></el-input>
                        </el-form-item>

                        <el-form-item label="结算周期时间" prop="period">
                            <el-input v-model="ruleForm.period" style="width: 220px"></el-input>
                        </el-form-item>

                        <el-form-item prop="period_unit" label="结算周期类型">
                            <el-col :span="8">
                                <el-radio-group  v-model="ruleForm.period_unit" size="small">
                                    <el-radio :label="0" border>天</el-radio>
                                    <el-radio :label="1" border>周</el-radio>
                                    <el-radio :label="2" border>月</el-radio>
                                    <el-radio :label="3" border>年</el-radio>
                                </el-radio-group>
                            </el-col>
                        </el-form-item>

                        <el-form-item label="比例" prop="rate">
                            <el-input v-model="ruleForm.rate" style="width: 220px"></el-input>%
                        </el-form-item>

                        <el-form-item label="奖金池总金额" prop="money">
                            <el-input v-model="ruleForm.money" :disabled="true" style="width: 220px"></el-input>元
                        </el-form-item>

                    </el-col>
                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="submitForm('ruleForm')"
                   size="small">保存
        </el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    name: '',
                    status: 0,
                    award_sn: '',
                    rate:'',//比例
                    period:1,//结算周期时间
                    period_unit:'',//结算周期类型
                },
                rules: {
                    name: [
                        {required: true, message: '请输入股东等级名称', trigger: 'change'},
                    ],
                    period: [
                        {required: true, message: '请输入结算周期时间', trigger: 'change'},
                    ],
                    period_unit: [
                        {required: true, message: '请选择结算周期类型', trigger: 'change'},
                    ],
                    rate: [
                        {required: true, message: '请输入比例', trigger: 'change'},
                    ],
                },
            };
        },
        mounted() {
            if (getQuery('id')) {
                this.loadData();
            }
            this.getSetting();
        },
        methods: {
            close(e) {
                this.visible = false;
            },
            submitForm(formName) {
                let self = this;
                let period_type;
                switch (self.ruleForm.period_unit) {
                    case 0:
                        period_type = 'day';
                        break;
                    case 1:
                        period_type = 'week';
                        break;
                    case 2:
                        period_type = 'month';
                        break;
                    case 3:
                        period_type = 'year';
                        break;
                }
                console.log(period_type);
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/boss/mall/prize/edit'
                            },
                            method: 'post',
                            data: {
                                id : getQuery('id'),
                                name: self.ruleForm.name,
                                period: self.ruleForm.period,
                                period_unit: period_type,
                                status: self.ruleForm.status,
                                rate: self.ruleForm.rate,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                navigateTo({
                                    r: 'plugin/boss/mall/prize/index'
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
            loadData() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/boss/mall/prize/edit',
                        id: getQuery('id'),
                    },
                    method: 'get'
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        if (e.data.data) {
                            this.ruleForm = e.data.data;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }

                    console.log(this.ruleForm);

                }).catch(e => {
                    console.log(e);
                });
            },
            deleteGoods(index) {
                this.ruleForm.goods_list.splice(index, 1);
                this.ruleForm.goods_warehouse_ids.splice(index, 1);
            },
            getSetting() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/boss/mall/level/setting',
                    },
                    method: 'get',
                }).then(res => {
                    this.cardLoading = false;
                    if (res.data.code == 0) {
                        this.weights = res.data.data.weights;
                        this.level = res.data.data.level
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.cardLoading = false;
                    console.log(e);
                });
            }
        }
    });
</script>

<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 20%;
        min-width: 900px;
    }

    .form-body .el-form-item {
        padding-right: 25%;
        min-width: 850px;
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

    .check-group {
        font-size: 14px !important;
    }

    .check-group .el-col {
        display: flex;
    }

    .check-group .el-input {
        margin: 0 5px;
    }

    .check-group .el-col .el-checkbox {
        display: flex;
        align-items: center;
    }

    .check-group .el-col .el-input {
        width: 100px;
    }

    .check-group .el-col .el-input .el-input__inner {
        height: 30px;
        width: 100px;
    }

    .el-checkbox-group .el-col {
        margin-bottom: 10px;
    }
</style>