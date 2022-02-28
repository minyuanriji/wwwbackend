<?php
echo $this->render("com-edit");
echo $this->render("../com/com-tab-from");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">

        <com-tab-from :current="activeName"></com-tab-from>

        <div class="table-body">
            <el-alert title="说明：用户通过扫商户二维码进行付款，成功后可获得赠送红包" type="info" :closable="false" style="margin-bottom: 20px;"></el-alert>

            <div style="">
                <el-button size="big" type="primary" @click="newStore">添加商户</el-button>
            </div>

            <el-card class="box-card" style="margin-top:20px;margin-bottom:20px;">
                <el-form label-width="15%" size="small">
                    <el-form-item label="推荐人">
                        <el-input style="width:300px;" placeholder="ID/昵称/手机号" v-model="searchData.parent" clearable >
                            <!--
                            <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                            -->
                        </el-input>
                    </el-form-item>
                    <el-form-item label="关键词">
                        <el-input style="width:300px;" placeholder="请输入关键词搜索" v-model="searchData.keyword" clearable>
                            <!--
                            <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                            -->
                        </el-input>
                    </el-form-item>
                    <el-form-item label="地区">
                        <el-cascader
                                :options="district"
                                :props="props"
                                v-model="searchData.district"
                                clearable>
                        </el-cascader>
                    </el-form-item>
                    <el-form-item label="日期">
                        <el-date-picker
                                v-model="searchData.date"
                                type="datetimerange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="赠送比例">
                        <el-input type="number" min="0" placeholder="最小值" v-model="searchData.give_value_min" style="width:100px;"></el-input>
                        <span style="margin-left:10px;margin-right:10px;">至</span>
                        <el-input type="number" min="0" placeholder="最大值" v-model="searchData.give_value_max" style="width:100px;"></el-input>
                    </el-form-item>
                    <el-form-item label="折扣">
                        <el-input type="number" min="0" placeholder="最小值" v-model="searchData.transfer_rate_min" style="width:100px;"></el-input>
                        <span style="margin-left:10px;margin-right:10px;">至</span>
                        <el-input type="number" min="0" placeholder="最大值" v-model="searchData.transfer_rate_max" style="width:100px;"></el-input>
                    </el-form-item>
                    <el-form-item label="收入统计">
                        <el-date-picker
                                v-model="searchData.income_stat_date"
                                type="datetimerange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                   </el-form-item>
                    <el-form-item label="赠送红包统计">
                        <el-date-picker
                                v-model="searchData.send_stat_date"
                                type="datetimerange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期">
                    </el-form-item>
                    <el-form-item >
                        <el-button @click="search" size="big" icon="el-icon-search" type="primary">点击搜索</el-button>
                    </el-form-item>
                </el-form>

            </el-card>

            <el-table :data="list" border style="width: 100%" v-loading="loading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column sortable="custom" label="商户名称" width="300">
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px;">
                                <com-image mode="aspectFill" :src="scope.row.cover_url"></com-image>
                            </div>
                            <div >
                                <div>
                                    <el-tooltip class="item" effect="dark" placement="top">
                                        <template slot="content">
                                            <div style="width: 320px;">{{scope.row.name}}</div>
                                        </template>
                                        <com-ellipsis :line="2">{{scope.row.name}}</com-ellipsis>
                                    </el-tooltip>
                                </div>
                                <div>ID：{{scope.row.mch_id}}</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="give_value" label="赠送比例/折扣" width="130">
                    <template slot-scope="scope">
                        <div>{{scope.row.give_value}}%</div>
                        <div style="color:darkred">折扣：{{scope.row.transfer_rate}}折</div>
                    </template>
                </el-table-column>
                <el-table-column prop="total_income" label="总收入" width="110"></el-table-column>
                <el-table-column prop="total_send" label="总送出" width="110"></el-table-column>
                <el-table-column prop="parent_nickname" label="推荐人" width="150"></el-table-column>
                <el-table-column label="手机/地址" width="180">
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
                <el-table-column prop="scope" width="110" label="启动时间">
                    <template slot-scope="scope">
                        {{scope.row.start_at}}
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="110" label="添加时间">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="110" label="更新时间">
                    <template slot-scope="scope">
                        {{scope.row.updated_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="editStore(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="deleteOn(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <!--工具条 批量操作和分页-->
            <el-col :span="24" class="toolbar">
                <el-pagination
                    background
                    layout="prev, pager, next"
                    @current-change="pageChange"
                    :page-size="pagination.pageSize"
                    :total="pagination.total_count"
                    style="float:right;margin:15px"
                    v-if="pagination">
                </el-pagination>
            </el-col>
        </div>
    </el-card>

    <com-edit :visible="editDialogVisible"
              :edit-data="editData"
              @close="close"
              @update="update">
    </com-edit>

    <el-dialog title="修改比例" :visible.sync="dialogContent">
        <el-form :model="ratioForm">
            <el-form-item label="">
                <el-input style="display: none" :readonly="true" v-model="ratioForm.id"></el-input>
                <el-input type="number" min="0" max="100" placeholder="请输入内容" v-model="ratioForm.ratio" style="width:300px;">
                    <template slot="append">%</template>
                </el-input>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogContent = false">取 消</el-button>
            <el-button type="primary" @click="ratioSubmit" :loading="ratioLoading">确 定</el-button>
        </div>
    </el-dialog>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'store',
                editDialogVisible: false,
                editData: {},
                searchData: {
                    parent: '',
                    keyword: '',
                    district: '',
                    date: '',
                    income_unit: 'day',
                    income_min: '',
                    cash_unit: 'day',
                    cash_min: '',
                    page: 1,
                    transfer_rate_min:'',
                    transfer_rate_max: '',
                    give_value_min: '',
                    give_value_max: '',
                    income_stat_date: '',
                    send_stat_date: ''
                },
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list',
                    checkStrictly: true
                },
                district: [],
                date: '',
                list: [],
                pagination: null,
                loading: false,
                dialogContent: false,
                ratioForm:'',
                ratioLoading: false,
            };
        },
        methods: {
            newStore(){
                this.editData = {};
                this.editDialogVisible = true;
            },
            editStore(row){
                this.dialogContent = true;
                this.ratioForm = {
                    ratio: row.ratio,
                    id: row.id
                }
            },
            ratioSubmit() {
                this.ratioLoading = true;
                request({
                    params: {
                        r: 'plugin/shopping_voucher/mall/from-store/edit-ratio',
                    },
                    method: 'post',
                    data:{
                        ratio: this.ratioForm.ratio,
                        id: this.ratioForm.id
                    }
                }).then(e => {
                    this.ratioLoading = false;
                    if (e.data.code == 0) {
                        this.dialogContent = false;
                        this.getList();
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.data.msg);
                    }
                }).catch(e => {
                    this.ratioLoading = false;

                    this.$message.error('未知错误');
                });
            },
            deleteOn(row){
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.loading = true;
                    request({
                        params: {
                            r: 'plugin/shopping_voucher/mall/from-store/delete'
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.loading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.loading = false;
                    });
                }).catch(() => {

                });
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                this.getList();
            },
            getList() {
                let params = Object.assign({
                    r: 'plugin/shopping_voucher/mall/from-store/list'
                }, this.searchData);
                params['page'] = this.page;
                request({
                    params
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.loading = false;
                }).catch(e => {
                    this.loading = false;
                });
                this.loading = true;
            },
            update(){
                this.getList();
            },
            close(){
                this.editDialogVisible = false;
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
        },
        mounted: function() {
            this.getList();
            this.getDistrict();
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
        margin: 0 0 20px 0px;
    }

    .input-item .el-input__inner {
        border-right: 0;
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

</style>