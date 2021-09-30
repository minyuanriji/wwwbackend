<?php

?>
<template id="com-edit">
    <div class="com-edit">
        <el-dialog width="70%" :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">

            <el-form label-width="15%" size="small">

                <el-form-item label="ID">
                    <el-input :disabled="searchStatus==1" type="number" min="0" placeholder="按商户ID精确搜索" v-model="searchForm.id" style="width:300px;"></el-input>
                </el-form-item>
                <el-form-item label="名称">
                    <el-input :disabled="searchStatus==1"  placeholder="按商户名称模糊搜索" v-model="searchForm.name" style="width:300px;"></el-input>
                </el-form-item>
                <el-form-item label="地区">
                    <el-cascader
                            :options="district"
                            :props="props"
                            v-model="searchForm.district"
                            :disabled="searchStatus==1"
                            clearable>
                    </el-cascader>
                </el-form-item>
                <el-form-item label="日期">
                    <el-date-picker
                            v-model="searchForm.date"
                            type="datetimerange"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                            :disabled="searchStatus==1" >
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="折扣">
                    <el-input :disabled="searchStatus==1" type="number" min="0" placeholder="最小值" v-model="searchForm.transfer_rate_min" style="width:100px;"></el-input>
                    <span style="margin-left:10px;margin-right:10px;">至</span>
                    <el-input :disabled="searchStatus==1" type="number" min="0" placeholder="最大值" v-model="searchForm.transfer_rate_max" style="width:100px;"></el-input>
                </el-form-item>
                <el-form-item label="收入">
                    <el-select :disabled="searchStatus==1"  v-model="searchForm.income_unit" placeholder="请选择" style="width:130px;">
                        <el-option value="day" label="日收入大于"></el-option>
                        <el-option value="month" label="月收入大于"></el-option>
                        <el-option value="year" label="年收入大于"></el-option>
                    </el-select>
                    <el-input :disabled="searchStatus==1" type="number" min="0" placeholder="最小值" v-model="searchForm.income_min" style="width:200px;"></el-input>
                </el-form-item>
                <el-form-item label="支出">
                    <el-select :disabled="searchStatus==1"  v-model="searchForm.cash_unit" placeholder="请选择" style="width:130px;">
                        <el-option value="day" label="日支出大于"></el-option>
                        <el-option value="month" label="月支出大于"></el-option>
                        <el-option value="year" label="年支出大于"></el-option>
                    </el-select>
                    <el-input :disabled="searchStatus==1"  type="number" min="0" placeholder="最小值" v-model="searchForm.cash_min" style="width:200px;"></el-input>
                </el-form-item>
                <el-form-item >
                    <el-button @click="searchStatus=0" v-if="searchStatus==1" size="big" icon="el-icon-refresh-left" type="danger">重新搜索</el-button>
                    <el-button @click="toSearch" v-if="searchStatus==0" size="big" icon="el-icon-search" type="primary">点击搜索</el-button>
                </el-form-item>
            </el-form>

            <el-card class="box-card" v-if="searchStatus==1">
                <div slot="header" class="clearfix">
                    <span>商户列表</span>
                    <el-button @click="openFormDialog(true)" style="float: right; padding: 3px 0" type="text">全部设置</el-button>
                </div>
                <el-table  @selection-change="handleSelectionChange" border v-loading="searchLoading" :data="searchResult.list" style="width: 100%">
                    <el-table-column align="center" type="selection" width="60"></el-table-column>
                    <el-table-column prop="id" label="ID" width="90"> </el-table-column>
                    <el-table-column prop="name" label="商户名称">
                        <template slot-scope="scope">
                            <div flex="cross:center">
                                <com-image width="25" height="25" :src="scope.row.cover_url"></com-image>
                                <div style="margin-left: 10px;width: 140px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.name}}</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="id" label="ID" width="90"> </el-table-column>
                    <el-table-column label="手机/地址" width="260">
                        <template slot-scope="scope">
                            <div>{{scope.row.mobile}}</div>
                            <el-tooltip class="item" effect="dark" placement="top">
                                <template slot="content">
                                    {{scope.row.province}} {{scope.row.city}} {{scope.row.district}}{{scope.row.address}}
                                </template>
                                <com-ellipsis :line="1">{{scope.row.province}} {{scope.row.city}} {{scope.row.district}}{{scope.row.address}}</com-ellipsis>
                            </el-tooltip>
                        </template>
                    </el-table-column>
                    <el-table-column prop="account_money" label="账户余额" width="150"> </el-table-column>
                    <el-table-column label="推荐人/折扣" width="150">
                        <template slot-scope="scope">
                            <com-ellipsis :line="1">{{scope.row.parent_nickname}}</com-ellipsis>
                            <div style="color:darkred">折扣：{{scope.row.transfer_rate}}折</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="created_at" label="入驻时间" width="150"> </el-table-column>
                </el-table>

                <div style="display: flex;justify-content: space-between;margin-top:20px;">
                    <div v-if="formData.list.length > 0" style="margin: 5px 0px;" >
                        <el-button @click="openFormDialog(false)" type="primary">自选设置</el-button>
                    </div>

                    <el-pagination
                            background
                            layout="prev, pager, next"
                            @current-change="pageChange"
                            :page-size="searchResult.pagination.pageSize"
                            :total="searchResult.pagination.total_count"
                            style="float:right;margin:10px"
                            v-if="searchResult.pagination">
                    </el-pagination>
                </div>

            </el-card>

        </el-dialog>

        <el-dialog width="30%" title="设置购物券赠送" :visible.sync="formDialogVisible" :close-on-click-modal="false">
            <el-form ref="formData" :rules="formRule" label-width="15%" :model="formData" size="small">
                <el-form-item :label="!formData.is_all ? '商户数' : '总页数'">
                    <span>{{formProgressData.total_num}}</span>
                </el-form-item>
                <el-form-item label="已完成">
                    <span>{{formProgressData.finished_num}}</span>
                </el-form-item>
                <el-form-item label="赠送比例" prop="give_value">
                    <el-input :disabled="formProgressData.loading" type="number" min="0" max="100" placeholder="请输入内容" v-model="formData.give_value" style="width:260px;">
                        <template slot="append">%</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="启动日期" prop="start_at">
                    <el-date-picker :disabled="formProgressData.loading" v-model="formData.start_at" type="date" placeholder="选择日期"></el-date-picker>
                </el-form-item>
            </el-form>
            <div v-if="!formProgressData.loading" slot="footer" class="dialog-footer">
                <el-button @click="formDialogVisible = false">取 消</el-button>
                <el-button type="primary" @click="save">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>

    Vue.component('com-edit', {
        template: '#com-edit',
        props: {
            visible: Boolean,
            editData: Object
        },
        data() {
            return {
                dialogTitle: "设置商户",
                activeName: "first",
                dialogVisible: false,
                searchForm:{
                    id: '',
                    name:'',
                    district: '',
                    date: '',
                    income_unit: 'day',
                    income_min: '',
                    cash_unit: 'day',
                    cash_min: '',
                    page: 1,
                    transfer_rate_min:'',
                    transfer_rate_max: ''
                },
                searchStatus: 0,
                searchLoading: false,
                searchResult:{
                    list: [],
                    pagination: null,
                },
                formDialogVisible: false,
                formData: {
                    list: [],
                    is_all:0,
                    do_page: 1,
                    do_search: null,
                    give_type: 1,
                    give_value: 0,
                    start_at: ''
                },
                formRule:{
                    give_value: [
                        {required: true, message: '赠送比例不能为空', trigger: 'change'},
                    ],
                    start_at:[
                        {required: true, message: '启动日期不能为空', trigger: 'change'},
                    ]
                },
                formProgressData:{
                    loading: false,
                    total_num:0,
                    finished_num:0
                },
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list',
                    checkStrictly: true
                },
                district: []
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            }
        },
        mounted: function () {
            this.getDistrict();
        },
        methods: {
            //选择待设置商户
            handleSelectionChange(selection) {
                this.formData.list = selection;
            },
            //搜索商户
            toSearch(){
                let params = Object.assign({
                    r: 'plugin/shopping_voucher/mall/from-store/search-store'
                }, this.searchForm);
                params['page'] = 1;
                this.searchStatus=1;
                this.searchLoading=true;
                request({
                    params
                }).then(e => {
                    if (e.data.code === 0) {
                        this.searchResult.list = e.data.data.list;
                        this.searchResult.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.searchLoading=false;
                }).catch(e => {
                    this.searchLoading=false;
                });
            },
            pageChange(currentPage) {
                this.searchForm.page = currentPage;
                this.toSearch();
            },
            //打开设置对话框
            openFormDialog(is_all){
                this.formData.is_all = is_all ? 1 : 0;
                this.formData.do_page = 1;
                this.formData.do_search = this.searchForm;
                if(is_all){
                    this.formProgressData.total_num = this.searchResult.pagination.page_count;
                }else{
                    this.formProgressData.total_num = this.formData.list.length;
                }
                this.formProgressData.finished_num = 0;
                this.formProgressData.loading = false;
                this.formDialogVisible = true;
            },
            save(){
                let that = this;
                let do_request = function(){
                    that.formProgressData.loading = true;
                    request({
                        params: {
                            r: "plugin/shopping_voucher/mall/from-store/batch-save"
                        },
                        method: "post",
                        data: that.formData
                    }).then(e => {
                        that.formProgressData.loading = false;
                        if (e.data.code == 0) {
                            if(!that.formData.is_all){
                                that.formProgressData.finished_num = that.formProgressData.total_num;
                                that.$emit('update');
                            }else{
                                that.formProgressData.finished_num = that.formData.do_page;
                                if(that.formData.do_page < that.formProgressData.total_num){
                                    that.formData.do_page++;
                                    do_request();
                                }else{
                                    that.$emit('update');
                                }
                            }
                        } else {
                            that.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        that.$message.error(e.data.msg);
                        that.formProgressData.loading = true;
                    });
                };
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        do_request();
                    }
                });
            },
            close(){
                this.$emit('close');
            },
            // 获取省市区列表
            getDistrict() {
                request({
                    params: {
                        r: 'district/index',
                        level: 3
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                    }
                }).catch(e => {

                });
            }
        }
    });
</script>