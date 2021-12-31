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
                            <el-col :span="10">
                                <el-radio-group v-model="ruleForm.period_unit" size="small">
                                    <el-radio :label="0" border>天</el-radio>
                                    <el-radio :label="1" border>周</el-radio>
                                    <el-radio :label="2" border>月</el-radio>
                                    <el-radio :label="3" border>年</el-radio>
                                </el-radio-group>
                            </el-col>
                        </el-form-item>

                        <el-form-item prop="automatic_audit" label="自动审核">
                            <el-col :span="8">
                                <el-radio-group v-model="ruleForm.automatic_audit" size="small">
                                    <el-radio :label="0" border>否</el-radio>
                                    <el-radio :label="1" border>是</el-radio>
                                </el-radio-group>
                            </el-col>
                        </el-form-item>

                        <el-form-item label="比例" prop="rate">
                            <el-input v-model="ruleForm.rate" style="width: 220px"></el-input>
                            %
                        </el-form-item>

                        <el-form-item label="奖金池总金额" prop="money">
                            <el-input v-model="ruleForm.money" :disabled="true" style="width: 220px"></el-input>
                            元
                        </el-form-item>

                        <el-form-item label="等级" prop="money">
                            <!--<el-select size="small" v-model="ruleForm.level_id" @change='toSearch' class="select">
                                <el-option :key="index" :label="item.name" :value="item.id"
                                           v-for="(item, index) in bossLevelList"></el-option>
                            </el-select>-->

                            <el-checkbox :checked="item.checked ? true : false"  v-for="item in bossLevelList" :label="item.name"
                                         :key="item.id" @change="pickerChange(item)">
                            </el-checkbox>

                        </el-form-item>

                    </el-col>
                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="submitForm('ruleForm')" size="small">
            保存
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
                    rate: '',//比例
                    period: 1,//结算周期时间
                    period_unit: '',//结算周期类型
                    automatic_audit: 0,//是否自动审核
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
                bossLevelList : [],
                checked : false,
                add_level_id : [],
            };
        },
        mounted() {
            if (getQuery('id')) {
                this.loadData();
            }
            setTimeout(()=>{
                this.getLevelList();
            },500);
        },
        methods: {
            close(e) {
                this.visible = false;
            },
            pickerChange (item) {
                if (item.checked) {
                    item.checked = false;
                    if (this.add_level_id) {
                        for (var i = 0; i < this.add_level_id.length; i++) {
                            if (this.add_level_id[i] == item.id) {
                                this.add_level_id.splice([i], 1);
                            }
                        }
                    }
                } else {
                    item.checked = true;
                    this.add_level_id.push(item.id);
                }
            },
            submitForm(formName) {
                console.log(formName);
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
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        if (!self.ruleForm.level_id) {
                            self.ruleForm.level_id = 0;
                        }
                        request({
                            params: {
                                r: 'plugin/boss/mall/prize/edit'
                            },
                            method: 'post',
                            data: {
                                id: getQuery('id'),
                                name: self.ruleForm.name,
                                period: self.ruleForm.period,
                                period_unit: period_type,
                                status: self.ruleForm.status,
                                rate: self.ruleForm.rate,
                                automatic_audit: self.ruleForm.automatic_audit,
                                level_ids: self.add_level_id,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                            } else {
                                self.$message.error(e.data.msg);
                            }
                            navigateTo({
                                r: 'plugin/boss/mall/prize/index'
                            })
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
                }).catch(e => {
                    console.log(e);
                });
            },
            getLevelList() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/boss/mall/level/index',
                    },
                    method: 'get',
                }).then(e => {
                    self.bossLevelList = e.data.data.list;
                    for (var i = 0; i< self.bossLevelList.length; i++) {
                        self.bossLevelList[i].checked = false;
                        if (self.ruleForm.level_id.length > 0) {
                            for (var m = 0; m < self.ruleForm.level_id.length; m++) {
                                console.log(self.ruleForm.level_id[m]);
                                if (self.bossLevelList[i].id == self.ruleForm.level_id[m]) {
                                    self.bossLevelList[i].checked = true;
                                    self.add_level_id.push(self.bossLevelList[i].id);
                                    continue;
                                }
                            }
                        }
                    }
                    console.log(self.bossLevelList);
                }).catch(e => {
                    console.log(e);
                });
            },
        }
    });
</script>

<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 900px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
        margin-left: 100px;
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