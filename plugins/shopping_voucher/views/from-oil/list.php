<?php
echo $this->render("com-edit");
echo $this->render("../com/com-tab-from");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">

        <com-tab-from :current="activeName"></com-tab-from>

        <div class="table-body">
            <el-alert title="说明：用户通过现金支付加油券订单，成功后可获得赠送购物券" type="info" :closable="false" style="margin-bottom: 20px;"></el-alert>

            <el-tabs v-model="activeName2" type="border-card">
                <el-tab-pane label="通用配置" name="first">
                    <el-form ref="commonSetForm" :model="commonSet" :rules="commFormRule" label-width="120px">
                        <el-form-item label="是否开启">
                            <el-switch v-model="commonSet.is_open" active-text="是" inactive-text="否"></el-switch>
                        </el-form-item>
                        <template v-if="commonSet.is_open">
                            <el-form-item label="首次赠送配置" prop="first_give_value">
                                <el-input type="number" min="0" placeholder="请输入内容" v-model="commonSet.first_give_value" style="width:260px;">
                                    <el-select v-model="commonSet.first_give_type" slot="prepend" placeholder="请选择" style="width:110px;">
                                        <el-option label="按比例" value="1"></el-option>
                                        <el-option label="按固定值" value="2"></el-option>
                                    </el-select>
                                    <template slot="append">{{commonSet.first_give_type == 1 ? "%" : "券"}}</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="首次赠送配置" prop="second_give_value">
                                <el-input type="number" min="0" placeholder="请输入内容" v-model="commonSet.second_give_value" style="width:260px;">
                                    <el-select v-model="commonSet.second_give_type" slot="prepend" placeholder="请选择" style="width:110px;">
                                        <el-option label="按比例" value="1"></el-option>
                                        <el-option label="按固定值" value="2"></el-option>
                                    </el-select>
                                    <template slot="append">{{commonSet.second_give_type == 1 ? "%" : "券"}}</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="启动日期" prop="start_at">
                                <el-date-picker v-model="commonSet.start_at" type="date" placeholder="选择日期"></el-date-picker>
                            </el-form-item>
                        </template>
                        <el-form-item >
                            <el-button :loading="loading" type="primary" @click="saveCommon">确 定</el-button>
                        </el-form-item>
                    </el-form>

                </el-tab-pane>
                <el-tab-pane label="指定产品" name="second">

                    <div style="">
                        <el-button size="big" type="primary" @click="newOilProduct">添加产品</el-button>
                    </div>

                    <el-table :data="list" border style="width: 100%;margin-top:20px;" v-loading="loading">
                        <el-table-column prop="id" label="ID" width="100" align="center"></el-table-column>
                        <el-table-column sortable="custom" label="产品名称" width="300">
                            <template slot-scope="scope">
                                <div>平台：{{scope.row.plat_name}}</div>
                                <div>产品：全部产品</div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="give_value" label="赠送配置" width="230">
                            <template slot-scope="scope">
                                <div><b>首次赠送：</b>
                                    <div v-if="scope.row.first_give_type == 1">按比例{{scope.row.first_give_value}}%赠送</div>
                                    <div v-if="scope.row.first_give_type == 2">按固定值{{scope.row.first_give_value}}赠送</div>
                                </div>
                                <div><b>第二次赠送：</b>
                                    <div v-if="scope.row.second_give_type == 1">按比例{{scope.row.second_give_value}}%赠送</div>
                                    <div v-if="scope.row.second_give_type == 2">按固定值{{scope.row.second_give_value}}赠送</div>
                                </div>
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
                                <el-button @click="editIt(scope.row)" type="text" circle size="mini">
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
                </el-tab-pane>
            </el-tabs>
        </div>
    </el-card>

    <com-edit :visible="editDialogVisible"
              :edit-data="editData"
              @close="close"
              @update="update">
    </com-edit>

    <el-dialog title="修改比例" :visible.sync="dialogContent">
        <el-form ref="aloneSetForm" :model="aloneSet" :rules="aloneFormRule" label-width="120px">
            <el-form-item label="首次赠送" prop="first_give_value">
                <el-input type="number" min="0" placeholder="请输入内容" v-model="aloneSet.first_give_value" style="width:260px;">
                    <el-select v-model="aloneSet.first_give_type" slot="prepend" placeholder="请选择" style="width:110px;">
                        <el-option label="按比例" value="1"></el-option>
                        <el-option label="按固定值" value="2"></el-option>
                    </el-select>
                    <template slot="append">{{aloneSet.first_give_type == 1 ? "%" : "券"}}</template>
                </el-input>
            </el-form-item>
            <el-form-item label="第二次赠送" prop="second_give_value">
                <el-input type="number" min="0" placeholder="请输入内容" v-model="aloneSet.second_give_value" style="width:260px;">
                    <el-select v-model="aloneSet.second_give_type" slot="prepend" placeholder="请选择" style="width:110px;">
                        <el-option label="按比例" value="1"></el-option>
                        <el-option label="按固定值" value="2"></el-option>
                    </el-select>
                    <template slot="append">{{aloneSet.second_give_type == 1 ? "%" : "券"}}</template>
                </el-input>
            </el-form-item>
            <el-form-item label="启动日期" prop="start_at">
                <el-date-picker v-model="aloneSet.start_at" type="date" placeholder="选择日期"></el-date-picker>
            </el-form-item>
            <el-form-item >
                <el-button :loading="aloneLoading" type="primary" @click="saveAlone">确 定</el-button>
            </el-form-item>
        </el-form>
    </el-dialog>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'oil',
                activeName2: 'first',
                editDialogVisible: false,
                editData: {},
                list: [],
                page:1,
                pagination: null,
                dialogContent: false,
                loading: false,
                aloneLoading: false,
                searchData: {},
                commonSet:{
                    is_open:false,
                    first_give_type: "1",
                    first_give_value: 0,
                    second_give_type: "1",
                    second_give_value: 0,
                    start_at: '',
                    recommender: []
                },
                commFormRule:{
                    first_give_value: [
                        {required: true, message: '首次赠送配置不能为空', trigger: 'change'},
                    ],
                    second_give_value: [
                        {required: true, message: '第二次赠送配置不能为空', trigger: 'change'},
                    ],
                    start_at:[
                        {required: true, message: '启动日期不能为空', trigger: 'change'},
                    ]
                },
                aloneFormRule:{
                    first_give_value: [
                        {required: true, message: '首次赠送配置不能为空', trigger: 'change'},
                    ],
                    second_give_value: [
                        {required: true, message: '第二次赠送配置不能为空', trigger: 'change'},
                    ],
                    start_at:[
                        {required: true, message: '启动日期不能为空', trigger: 'change'},
                    ]
                },
                aloneSet:{
                    first_give_type: "1",
                    first_give_value: 0,
                    second_give_type: "1",
                    second_give_value: 0,
                    plat_id: 0,
                    product_id: 0,
                    start_at: ''
                },
            };
        },
        methods: {
            editIt(row){
                this.dialogContent = true;
                this.aloneSet.first_give_type   = row.first_give_type;
                this.aloneSet.first_give_value  = row.first_give_value;
                this.aloneSet.second_give_type  = row.second_give_type;
                this.aloneSet.second_give_value = row.second_give_value;
                this.aloneSet.start_at          = row.start_at;
                this.aloneSet.plat_id           = row.plat_id;
                this.aloneSet.product_id        = row.product_id;
            },
            pageChange(page){
                this.page = page;
                this.getList();
            },
            newOilProduct(){
                this.editData = {};
                this.editDialogVisible = true;
            },
            switchChanged(){
                if(!this.commonSet.is_open){
                    this.saveCommon();
                }
            },
            saveCommon(){
                let that = this;
                this.$refs['commonSetForm'].validate((valid) => {
                    if (valid) {
                        that.loading = true;
                        request({
                            params: {
                                r: "plugin/shopping_voucher/mall/from-oil/save-common"
                            },
                            method: "post",
                            data: {
                                is_open:that.commonSet.is_open ? 1 : 0,
                                first_give_type:that.commonSet.first_give_type,
                                first_give_value:that.commonSet.first_give_value,
                                second_give_type:that.commonSet.second_give_type,
                                second_give_value:that.commonSet.second_give_value,
                                start_at:that.commonSet.start_at
                            }
                        }).then(e => {
                            that.loading = false;
                            if (e.data.code == 0) {
                                that.$message.success(e.data.msg);
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error('请求失败！');
                            that.loading = true;
                        });
                    }
                });
            },
            saveAlone(){
                let that = this;
                this.$refs['aloneSetForm'].validate((valid) => {
                    if (valid) {
                        that.aloneLoading = true;
                        request({
                            params: {
                                r: "plugin/shopping_voucher/mall/from-oil/save"
                            },
                            method: "post",
                            data: {
                                first_give_type:that.aloneSet.first_give_type,
                                first_give_value:that.aloneSet.first_give_value,
                                second_give_type:that.aloneSet.second_give_type,
                                second_give_value:that.aloneSet.second_give_value,
                                start_at:that.aloneSet.start_at,
                                plat_id: that.aloneSet.plat_id,
                                product_id: that.aloneSet.product_id
                            }
                        }).then(e => {
                            that.aloneLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success(e.data.msg);
                                that.dialogContent=false;
                                that.getList();
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error('请求失败！');
                            that.aloneLoading = true;
                        });
                    }
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
                            r: "plugin/shopping_voucher/mall/from-oil/delete"
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
            getList() {
                let params = Object.assign({
                    r: 'plugin/shopping_voucher/mall/from-oil/list'
                }, this.searchData), i;
                params['page'] = this.page;
                request({
                    params
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        let commonData = e.data.data.commonData;

                        this.commonSet.is_open           = commonData.is_open == 1 ? true : false;
                        this.commonSet.first_give_type   = commonData.first_give_type;
                        this.commonSet.first_give_value  = commonData.first_give_value;
                        this.commonSet.second_give_type  = commonData.second_give_type;
                        this.commonSet.second_give_value = commonData.second_give_value;
                        this.commonSet.start_at          = commonData.start_at;

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
                this.editDialogVisible = false;
            },
            close(){
                this.editDialogVisible = false;
            }
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