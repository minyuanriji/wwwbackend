

<div id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>全局配置</span>
            </div>
        </div>
        <div class="form_box">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-card style="margin-top: 10px" shadow="never">
                    <div slot="header">
                        <div>推荐配置</div>
                    </div>
                    <el-col >
                        <el-form-item label="奖励类型" prop="invited_price_type">
                            <el-radio-group v-model="ruleForm.invited_price_type">
                                <el-radio :label="1">百分比</el-radio>
                                <el-radio :label="2">固定金额</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="奖励佣金">
                            <el-table
                                    :data="tableData"
                                    border style="width: 100%;" class="invited-table-rule">
                                <el-table-column prop="text" label="角色" width="180"></el-table-column>
                                <el-table-column label="推荐分公司奖励" prop="branch_office">
                                    <template slot-scope="scope">
                                        <el-input placeholder="请输入内容" v-model="scope.row.branch_office.first">
                                            <template slot="prepend">1级</template>
                                            <template slot="append">{{ruleForm.invited_price_type == 1 ? '%' : '元'}}</template>
                                        </el-input>
                                        <el-input placeholder="请输入内容" v-model="scope.row.branch_office.first" style="margin-top:5px;">
                                            <template slot="prepend">2级</template>
                                            <template slot="append">{{ruleForm.invited_price_type == 1 ? '%' : '元'}}</template>
                                        </el-input>
                                        <el-input placeholder="请输入内容" v-model="scope.row.branch_office.first"  style="margin-top:5px;">
                                            <template slot="prepend">3级</template>
                                            <template slot="append">{{ruleForm.invited_price_type == 1 ? '%' : '元'}}</template>
                                        </el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column label="推荐合伙人奖励" prop="partner">
                                    <template slot-scope="scope">
                                        <el-input placeholder="请输入内容" v-model="scope.row.partner.first">
                                            <template slot="prepend">1级</template>
                                            <template slot="append">{{ruleForm.invited_price_type == 1 ? '%' : '元'}}</template>
                                        </el-input>
                                        <el-input placeholder="请输入内容" v-model="scope.row.partner.first" style="margin-top:5px;">
                                            <template slot="prepend">2级</template>
                                            <template slot="append">{{ruleForm.invited_price_type == 1 ? '%' : '元'}}</template>
                                        </el-input>
                                        <el-input placeholder="请输入内容" v-model="scope.row.partner.first"  style="margin-top:5px;">
                                            <template slot="prepend">3级</template>
                                            <template slot="append">{{ruleForm.invited_price_type == 1 ? '%' : '元'}}</template>
                                        </el-input>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-form-item>
                    </el-col>
                </el-card>
                <el-button :loading="btnLoading" class="button-item" type="primary" style="margin-top: 24px;"
                           @click="store('ruleForm')" size="small">保存
                </el-button>
            </el-form>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                ruleForm: {
                    invited_price_type: 1,
                    commissionLevel: [],
                    selectData: '',
                    batchShareLevel: 0
                },
                rules: {
                    invited_price_type: [
                        {message: '请选择奖励类型', trigger: 'blur', required: true}
                    ]
                },
                selectList: [],
                tableData: [
                    {
                        text          : '分公司',
                        role          : 'branch_office',
                        branch_office : {
                            first  : '0.00',
                            second : '0.00',
                            three  : '0.00'
                        },
                        partner       : {
                            first  : '0.00',
                            second : '0.00',
                            three  : '0.00'
                        }
                    },
                    {
                        text          : '合伙人',
                        role          : 'partner',
                        branch_office : {
                            first  : '0.00',
                            second : '0.00',
                            three  : '0.00'
                        },
                        partner       : {
                            first  : '0.00',
                            second : '0.00',
                            three  : '0.00'
                        }
                    }
                ]
            }
        },
        mounted: function () {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                let self = this;
                request({
                    params: {
                        r: 'plugin/commission/mall/setting/options',
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = Object.assign(this.ruleForm, e.data.data.setting);
                    }
                }).catch(e => {
                    this.loading = false;
                })
            },
            handleSelectionChange(data) {
                this.selectList = data;
            },
            batchAttr() {

            }
        }
    });
</script>
<style>
    .invited-table-rule table tr th{
        font-weight:normal;
        color:#606266;
    }

    .form_box {
        background-color: #f3f3f3;
        padding: 0 0 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .el-input-group__append {
        background-color: #fff;
        color: #353535;
    }

    .commission-batch-set-box{
        border-top: 1px solid #E8EAEE;
        border-left: 1px solid #E8EAEE;
        border-right: 1px solid #E8EAEE;
        padding: 16px;
    }

    .commission-batch-set-box .batch {
        margin-left: -10px;
        margin-right: 20px;
    }

    .form_box .el-select .el-input {
        width: 130px;
    }

    .form_box .detail {
        width: 100%;
    }

    .form_box .detail .el-input-group__append {
        padding: 0 10px;
    }

    .form_box input::-webkit-outer-spin-button,
    .form_box input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }

    .form_box input[type="number"] {
        -moz-appearance: textfield;
    }

    .form_box .el-table .cell {
        text-align: center;
    }

    .form_box .el-table thead.is-group th {
        background: #ffffff;
    }
</style>