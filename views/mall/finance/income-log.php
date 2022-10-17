<?php
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-user-finance-stat');
?>

<div id="app" v-cloak>
    <el-card style="border-radius: 15px">
        <div>
            <span>收益记录</span>
            <div style="float: right;margin-right: 10px;margin-bottom: 10px">
                <com-export-dialog :field_list='export_list' :params="searchData" @selected="exportConfirm"></com-export-dialog>
            </div>
            <div style="float: right;margin-right: 10px;margin-bottom: 10px">
                <el-button @click="handleIncome" type="primary" size="small">收益充值</el-button>
            </div>
        </div>
    </el-card>

    <el-card style="border-radius: 15px;margin-top: 15px;">
        <div style="display: flex;">
            <div style="margin-right: 10px">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索" v-model="keyword" clearable @clear="search" @input="triggeredChange">
                    <el-select slot="prepend" v-model="kw_type" placeholder="请选择" size="small" style="width:120px;">
                        <el-option v-for="item in select_keyword_option" :label="item.label" :key="item.value" :value="item.value"></el-option>
                    </el-select>
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <div style="margin-right: 10px">
                <el-date-picker size="small" v-model="date" type="datetimerange"
                                style="float: left"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                range-separator="至" start-placeholder="开始日期"
                                @change="selectDateTime"
                                end-placeholder="结束日期">
                </el-date-picker>
            </div>

            <div style="width: 18%">
                类型
                <el-tooltip class="item" effect="dark" content="只有选择订单或者商家扫码类型，才能筛选省市区" placement="bottom">
                    <i class="el-icon-question"></i>
                </el-tooltip>
                <el-select size="small" v-model="type" @change='searchType' class="select" placeholder="请选择类型">
                    <el-option v-for="item in type_option" :label="item.label" :key="item.value" :value="item.value"></el-option>
                </el-select>
            </div>
            <div style="margin-right: 10px"  v-if="levelShow">
                等级
                <el-select size="small" v-model="level" placeholder="请选择区域等级" @change="levelChange">
                    <el-option
                            v-for="item in level_list"
                            :label="item.name"
                            :value="item.level">
                    </el-option>
                </el-select>
            </div>
            <div style="margin-right: 10px" v-if="level>0">
                省市区
                <el-cascader
                        size="small"
                        @change="addressChange"
                        :options="district"
                        :props="props"
                        v-model="address">
                </el-cascader>
            </div>
        </div>
    </el-card>
    <el-card shadow="never" style="border:0;" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div class="table-body" style="border-radius: 15px;">
            <div style="margin-bottom:20px;">
                <div style="display: flex;justify-content: space-evenly">
                    <div>
                        <div style="text-align: center">总收入</div>
                        <div id="assets">{{Statistics.income}}元</div>
                    </div>
                    <div>
                        <div style="text-align: center">总支出</div>
                        <div id="assets">{{Statistics.expend}}元</div>
                    </div>
                    <div>
                        <div style="text-align: center">当页收入</div>
                        <div id="assets">{{Statistics.currentIncome}}元</div>
                    </div>
                    <div>
                        <div style="text-align: center">当页支出</div>
                        <div id="assets">{{Statistics.currentExpend}}元</div>
                    </div>
                </div>
            </div>

            <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="nickname" label="昵称">
                    <template slot-scope="scope">
                        <com-user-finance-stat :user-id="parseInt(scope.row.user_id)">
                            {{scope.row.nickname}}
                        </com-user-finance-stat>
                    </template>
                </el-table-column>
                <el-table-column label="收支情况(收益)" width="150">
                    <template slot-scope="scope">
                        <div style="font-size: 18px;color: #68CF3D" v-if="scope.row.type == 1">+{{scope.row.income}}</div>
                        <div style="font-size: 18px;color: #F6AA5A" v-if="scope.row.type == 2">-{{scope.row.income}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="总收益"  prop="money" width="130">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type == 2">{{Number(scope.row.money) - Number(scope.row.income)}}</span>
                        <span v-else>{{Number(scope.row.money) + Number(scope.row.income)}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="desc" label="说明" width="700"></el-table-column>
                <el-table-column prop="scope" width="180" label="收益时间">
                       <template slot-scope="scope">
                           {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                       </template>
                </el-table-column>
            </el-table>

            <!--工具条 批量操作和分页-->
            <div style="text-align: center">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        @current-change="pageChange"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="margin-top:20px;"
                        v-if="pagination">
                </el-pagination>
            </div>
        </div>

        <!-- 充值收益 -->
        <el-dialog title="充值收益" :visible.sync="dialogIncome" width="30%">
            <el-form :model="incomeForm" label-width="80px" :rules="incomeFormRules" ref="incomeForm">
                <el-form-item label="操作" prop="type">
                    <el-radio v-model="incomeForm.type" label="1">充值</el-radio>
                    <el-radio v-model="incomeForm.type" label="2">扣除</el-radio>
                </el-form-item>
                <el-form-item label="用户" prop="user_id">
                    <el-input style="display: none;" v-model="incomeForm.user_id"></el-input>
                    <el-input disabled v-model="incomeForm.nickname">
                        <template slot="append">
                            <el-button @click="getUsers" type="primary">选择</el-button>
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item label="金额" prop="price" size="small">
                    <el-input type="number" v-model="incomeForm.price"></el-input>
                </el-form-item>
                <el-form-item label="备注" prop="remark" size="small">
                    <el-input v-model="incomeForm.remark"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogIncome = false">取消</el-button>
                <el-button :loading="btnLoading" type="primary" @click="incomeSubmit">充值</el-button>
            </div>
        </el-dialog>

        <com-dialog-select
                @close="closeDlgSelect"
                @selected="selectUser"
                :url="forDlgSelect.url"
                :multiple="forDlgSelect.multiple"
                :title="forDlgSelect.title"
                :list-key="forDlgSelect.listKey"
                :params="forDlgSelect.params"
                :columns="forDlgSelect.columns"
                :visible="forDlgSelect.visible"></com-dialog-select>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                incomeForm: {
                    type: "1",
                    user_id: '',
                    price: 0.00,
                    nickname: '',
                    remark: '',
                    is_manual: 1,
                },
                forDlgSelect:{
                    visible: false,
                    multiple: false,
                    title: "选择用户",
                    params: {},
                    columns: [
                        {label:"收益", key:"total_income"},
                        {label:"手机号", key:"mobile"},
                        {label:"等级", key:"role_type_text"}
                    ],
                    listKey: 'nickname',
                    url: "mall/user/index",
                },
                incomeFormRules: {
                    user_id: [
                        {required: true, message: '请选择用户', trigger: 'blur'},
                    ],
                    type: [
                        {required: true, message: '操作不能为空', trigger: 'blur'},
                    ],
                    price: [
                        {required: true, message: '金额不能为空', trigger: 'blur'},
                    ],
                },
                btnLoading: false,
                searchData: {
                    keyword: '',
                    kw_type: '',
                    date: '',
                    start_date: '',
                    end_at: '',
                    type: '',
                    level: '',
                    address: null,
                },
                date: '',
                keyword: '',
                kw_type: '',
                is_manual: '',
                form: [],
                pagination: null,
                listLoading: false,
                dialogIncome: false,
                Statistics: '',
                type: '',
                level_list: [
                    {
                        name: '省',
                        level: 1
                    },
                    {
                        name: '市',
                        level: 2
                    },
                    {
                        name: '区',
                        level: 3
                    },
                ],
                level: '',
                address: null,
                district: [],
                town_list: [],
                province_id: 0,
                city_id: 0,
                district_id: 0,
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                },
                levelShow:false,
                export_list: [],
                select_keyword_option:[
                    {
                        label:'手机号',
                        value:'mobile',
                    },
                    {
                        label:'昵称',
                        value:'nickname',
                    },
                    {
                        label:'用户ID',
                        value:'user_id',
                    },
                    {
                        label:'备注',
                        value:'remark',
                    },
                ],
                type_option:[
                    {
                        label:'全部',
                        value:''
                    },
                    {
                        label:'商品分佣',
                        value:'goods'
                    },
                    {
                        label:'结账单收入',
                        value:'checkout'
                    },
                    {
                        label:'提现',
                        value:'cash'
                    },
                    {
                        label:'管理员操作',
                        value:'admin'
                    },
                    {
                        label:'推荐门店分佣',
                        value:'store'
                    },
                    {
                        label:'旧商城迁移',
                        value:'migrate'
                    },
                    {
                        label:'股东分红',
                        value:'boss'
                    },
                    {
                        label:'推荐酒店分佣',
                        value:'hotel_commission'
                    },
                    {
                        label:'酒店消费分佣',
                        value:'hotel_3r_commission'
                    },
                    {
                        label:'大礼包分佣',
                        value:'giftpacks_commission'
                    },
                    {
                        label:'区域商品分红分佣',
                        value:'region_goods'
                    },
                    {
                        label:'区域门店扫码分红',
                        value:'region_checkout'
                    },
                ],
            };
        },
        methods: {
            triggeredChange (){
                if (this.keyword.length>0 && this.kw_type.length<=0) {
                    alert('请选择搜索方式');
                    this.keyword='';
                }
            },
            getUsers(){
                this.forDlgSelect.visible = true;
            },
            selectUser(row){
                this.incomeForm.user_id = row.user_id;
                this.incomeForm.nickname = row.nickname;
            },
            closeDlgSelect(){
                this.forDlgSelect.visible = false;
            },
            handleIncome() {
                this.dialogIncome = true;
                this.incomeForm.user_id = '';
                this.incomeForm.nickname = '';
            },
            incomeSubmit() {
                var self = this;
                this.$refs.incomeForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, self.incomeForm);
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/finance/income-modified',
                            },
                            method: 'post',
                            data: para,
                        }).then(e => {
                            if (e.data.code === 0) {
                                location.reload();
                                self.dialogIncome = false;
                            } else {
                                self.$message.error(e.data.msg);
                            }
                            self.btnLoading = false;
                        }).catch(e => {
                            self.btnLoading = false;
                        });
                    }
                });
            },
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.kw_type = this.kw_type;
                this.searchData.start_date = this.date[0];
                this.searchData.end_date = this.date[1];
                this.searchData.type = this.type;
                this.searchData.level = this.level;
                this.searchData.address = this.address;
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                if (this.date == null) {
                    this.date = ''
                }
                this.getList();
            },
            selectDateTime(e) {
                if (e != null) {
                    this.searchData.start_date = e[0];
                    this.searchData.end_date = e[1];
                } else {
                    this.searchData.start_date = '';
                    this.searchData.end_date = '';
                }
                this.page = 1;
                this.search();
            },
            getList() {
                let params = {
                    r: 'mall/finance/income-log',
                    page: this.page,
                    date: this.date,
                    user_id: getQuery('user_id'),
                    keyword: this.keyword,
                    kw_type: this.kw_type,
                    type: this.type,
                    level: this.level,
                    address: this.address,
                };
                if (this.date) {
                    Object.assign(params, {
                        start_date: this.date[0],
                        end_date: this.date[1],
                    });
                }
                request({
                    params,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;
                        this.Statistics = e.data.data.Statistics;
                        this.pagination = e.data.data.pagination;
                        this.export_list = e.data.data.export_list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
                this.listLoading = true;
            },
            searchType(e) {
                if (e == 'goods' || e == 'checkout') {
                    this.levelShow=true;
                } else {
                    this.levelShow=false;
                    this.level='';
                }
                this.page = 1;
                this.getList();
            },
            levelChange(e) {
                this.getDistrict(e);
            },
            // 获取省市区列表
            getDistrict(level) {
                if (level == 1) {
                    level1 = 1;
                } else if (level == 2) {
                    level1 = 2;
                } else if (level == 3) {
                    level1 = 3;
                } else {
                    level1 = 4;
                }
                request({
                    params: {
                        r: 'district/index',
                        level: level1
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                    }
                }).catch(e => {
                });
            },
            addressChange(e) {
                this.town_list = []
                this.page = 1;
                this.getList();
            },
        },
    mounted: function() {
        this.getList();
    }
});
</script>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }
    #assets {
        font-size: 18px;
        color: #1ed0ff;
        margin-left: 10px;
    }
    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>