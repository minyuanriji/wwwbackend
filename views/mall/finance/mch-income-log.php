<?php
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-user-finance-stat');
?>

<div id="app" v-cloak>
    <el-card style="border-radius: 15px">
        <div>
            <span>商户收益记录</span>
            <!--<div style="float: right;margin-right: 10px;margin-bottom: 10px">
                <com-export-dialog :field_list='export_list' :params="searchData" @selected="exportConfirm"></com-export-dialog>
            </div>-->
        </div>
    </el-card>

    <el-card style="border-radius: 15px;margin-top: 15px;">
        <div style="display: flex;">
            <div style="margin-right: 10px">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入关键词搜索" v-model="keyword" clearable @clear="search" @input="triggeredChange">
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
        </div>
    </el-card>
    <el-card shadow="never" style="border:0;" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div class="table-body" style="border-radius: 15px;">
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="nickname" label="商户">
                    <template slot-scope="scope">
                        <com-image mode="aspectFill" :src="scope.row.cover_url"
                                   style="float: left;margin-right: 10px"></com-image>
                        <div>{{scope.row.store_name}}（ID：{{scope.row.mch_id}}）</div>
                    </template>
                </el-table-column>
                <el-table-column label="金额" width="150">
                    <template slot-scope="scope">
                        <div style="font-size: 18px;color: #68CF3D" v-if="scope.row.type == 1">+{{scope.row.money}}</div>
                        <div style="font-size: 18px;color: #F6AA5A" v-if="scope.row.type == 2">-{{scope.row.money}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="desc" label="说明" width="700"></el-table-column>
                <el-table-column prop="scope" width="180" label="时间">
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

        <com-dialog-select
                @close="closeDlgSelect"
                @selected="selectUser"
                :url="forDlgSelect.url"
                :multiple="forDlgSelect.multiple"
                :title="forDlgSelect.title"
                :list-key="forDlgSelect.listKey"
                :params="forDlgSelect.params"
                :columns="forDlgSelect.columns"
                :visible="forDlgSelect.visible">
        </com-dialog-select>
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
                type: '',
                levelShow:false,
                export_list: [],
                select_keyword_option:[
                    {
                        label:'商户ID',
                        value:'mch_id',
                    },
                    {
                        label:'店铺名',
                        value:'store_name',
                    },
                    {
                        label:'手机号',
                        value:'mobile',
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
                    r: 'mall/finance/mch-income-log',
                    page: this.page,
                    date: this.date,
                    mch_id: getQuery('mch_id'),
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
                        this.pagination = e.data.data.pagination;
                        // this.export_list = e.data.data.export_list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
                this.listLoading = true;
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