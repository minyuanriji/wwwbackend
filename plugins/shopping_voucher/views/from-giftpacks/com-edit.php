<?php

?>
<template id="com-edit">
    <div class="com-edit">
        <el-dialog width="70%" :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">

            <el-form label-width="15%" size="small">
                <el-form-item label="ID">
                    <el-input :disabled="searchStatus==1" type="number" min="0" placeholder="按大礼包ID精确搜索" v-model="searchForm.id" style="width:300px;"></el-input>
                </el-form-item>
                <el-form-item label="名称">
                    <el-input :disabled="searchStatus==1"  placeholder="按大礼包名称模糊搜索" v-model="searchForm.name" style="width:300px;"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button @click="searchStatus=0" v-if="searchStatus==1" size="big" icon="el-icon-refresh-left" type="danger">重新搜索</el-button>
                    <el-button @click="toSearch(1)" v-if="searchStatus==0" size="big" icon="el-icon-search" type="primary">点击搜索</el-button>
                </el-form-item>
            </el-form>

            <el-card class="box-card" v-if="searchStatus==1">
                <div slot="header" class="clearfix">
                    <span>大礼包列表</span>
                    <el-button @click="openFormDialog(true)" style="float: right; padding: 3px 0" type="text">全部设置</el-button>
                </div>
                <el-table @selection-change="handleSelectionChange" border v-loading="searchLoading" :data="searchResult.list" style="width: 100%">
                    <el-table-column align="center" type="selection" width="60"></el-table-column>
                    <el-table-column prop="id" label="ID" width="90"> </el-table-column>
                    <el-table-column prop="title" label="大礼包名称" width="300">
                        <template slot-scope="scope">
                            <div flex="cross:center">
                                <com-image width="25" height="25" :src="scope.row.cover_pic"></com-image>
                                <div style="margin-left: 10px;width: 140px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.title}}</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="当前赠送配置">
                        <template slot-scope="scope">
                            <div v-if="scope.row.recommender != ''"><b>消费者：</b>
                                <div v-if="scope.row.give_type == 1">按比例{{scope.row.give_value}}%赠送</div>
                                <div v-if="scope.row.give_type == 2">按固定值{{scope.row.give_value}}赠送</div>
                            </div>
                            <div v-if="scope.row.recommender != ''"><b>推荐人：</b>
                                <div v-for="recommender in scope.row.recommender">
                                    <span v-if="recommender.type == 'branch_office'">分公司</span>
                                    <span v-if="recommender.type == 'partner'">合伙人</span>
                                    <span v-if="recommender.type == 'store'">VIP会员</span>
                                    <span v-if="recommender.type == 'user'">普通用户</span>
                                    <span v-if="recommender.give_type == 1">按比例{{recommender.give_value}}%赠送</span>
                                    <span v-if="recommender.give_type == 2">按固定值{{recommender.give_value}}赠送</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
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
                <el-form-item :label="!formData.is_all ? '记录数' : '总页数'">
                    <span>{{formProgressData.total_num}}</span>
                </el-form-item>
                <el-form-item label="已完成">
                    <span>{{formProgressData.finished_num}}</span>
                </el-form-item>
                <el-form-item label="赠送比例" prop="give_value">
                    <div>
                        <el-input :disabled="formProgressData.loading" type="number" min="0" max="100" placeholder="请输入内容" v-model="formData.give_value" style="width:260px;">
                            <el-select v-model="formData.give_type" slot="prepend" placeholder="请选择" style="width:110px;">
                                <el-option label="按比例" value="1"></el-option>
                                <el-option label="按固定值" value="2"></el-option>
                            </el-select>
                            <template slot="append">{{formData.give_type == 1 ? "%" : "券"}}</template>
                        </el-input>
                    </div>
                    <el-table :data="formData.recommender" border style="margin-top:10px;width:100%">
                        <el-table-column label="级别" width="110" align="center">
                            <template slot-scope="scope">
                                <span v-if="scope.row.type == 'branch_office'">分公司</span>
                                <span v-if="scope.row.type == 'partner'">合伙人</span>
                                <span v-if="scope.row.type == 'store'">VIP会员</span>
                                <span v-if="scope.row.type == 'user'">普通用户</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="比例">
                            <template slot-scope="scope">
                                <el-input type="number" min="0" max="100" placeholder="请输入内容" v-model="scope.row.give_value" style="width:260px;">
                                    <el-select v-model="scope.row.give_type" slot="prepend" placeholder="请选择" style="width:110px;">
                                        <el-option label="按比例" value="1"></el-option>
                                        <el-option label="按固定值" value="2"></el-option>
                                    </el-select>
                                    <template slot="append">{{scope.row.give_type == 1 ? "%" : "券"}}</template>
                                </el-input>
                            </template>
                        </el-table-column>
                    </el-table>
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
                dialogTitle: "设置大礼包",
                activeName: "first",
                dialogVisible: false,
                searchForm:{
                    id: '',
                    name:'',
                    page: 1
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
                    give_type: "1",
                    give_value: 0,
                    start_at: '',
                    recommender: [
                        {type: 'branch_office', give_type: "1", give_value: 0},
                        {type: 'partner', give_type: "1", give_value: 0},
                        {type: 'store', give_type: "1", give_value: 0},
                        {type: 'user', give_type: "1", give_value: 0}
                    ]
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
                }
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            }
        },
        mounted: function () {

        },
        methods: {
            //选择待设置大礼包
            handleSelectionChange(selection) {
                this.formData.list = selection;
            },
            //搜索大礼包
            toSearch(page){
                let params = Object.assign({
                    r: 'plugin/shopping_voucher/mall/from-giftpacks/search-giftpacks'
                }, this.searchForm);
                params['page'] = typeof page != "undefined" ? page : 1;
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
            pageChange(page) {
                this.toSearch(page);
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
                            r: "plugin/shopping_voucher/mall/from-giftpacks/batch-save"
                        },
                        method: "post",
                        data: that.formData
                    }).then(e => {
                        that.formProgressData.loading = false;
                        if (e.data.code == 0) {
                            if(!that.formData.is_all){
                                that.formProgressData.finished_num = that.formProgressData.total_num;
                                that.$emit('update');
                                that.formDialogVisible=false;
                            }else{
                                that.formProgressData.finished_num = that.formData.do_page;
                                if(that.formData.do_page < that.formProgressData.total_num){
                                    that.formData.do_page++;
                                    do_request();
                                }else{
                                    that.$emit('close');
                                    that.$emit('update');
                                    that.formDialogVisible=false;
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
            }
        }
    });
</script>