<?php
echo $this->render("com-edit");
echo $this->render("../com/com-tab-from");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">

        <com-tab-from :current="activeName"></com-tab-from>

        <div class="table-body">
            <el-alert title="说明：用户通过现金支付大礼包订单，成功后可获得赠送红包" type="info" :closable="false" style="margin-bottom: 20px;"></el-alert>

            <el-tabs v-model="activeName2" type="border-card">
                <el-tab-pane label="通用配置" name="first">
                    <el-form ref="commonSetForm" :model="commonSet" :rules="commFormRule" label-width="120px">
                        <el-form-item label="是否开启">
                            <el-switch v-model="commonSet.is_open" active-text="是" inactive-text="否"></el-switch>
                        </el-form-item>
                        <template v-if="commonSet.is_open">
                            <el-form-item label="赠送比例" prop="give_value">
                                <div>
                                    <el-input type="number" min="0" placeholder="请输入内容" v-model="commonSet.give_value" style="width:260px;">
                                        <el-select v-model="commonSet.give_type" slot="prepend" placeholder="请选择" style="width:110px;">
                                            <el-option label="按比例" value="1"></el-option>
                                            <el-option label="按固定值" value="2"></el-option>
                                        </el-select>
                                        <template slot="append">{{commonSet.give_type == 1 ? "%" : "券"}}</template>
                                    </el-input>
                                </div>
                                <el-table :data="commonSet.recommender" border style="margin-top:10px;width: 40%">
                                    <el-table-column label="级别" width="110" align="center">
                                        <template slot-scope="scope">
                                            <span v-if="scope.row.type == 'branch_office'">城市服务商</span>
                                            <span v-if="scope.row.type == 'partner'">区域服务商</span>
                                            <span v-if="scope.row.type == 'store'">VIP代理商</span>
                                            <span v-if="scope.row.type == 'user'">VIP会员</span>
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
                                <el-date-picker v-model="commonSet.start_at" type="date" placeholder="选择日期"></el-date-picker>
                            </el-form-item>
                        </template>
                        <el-form-item >
                            <el-button :loading="loading" type="primary" @click="saveCommon">确 定</el-button>
                        </el-form-item>
                    </el-form>

                </el-tab-pane>
                <el-tab-pane label="指定大礼包" name="second">

                    <div style="">
                        <el-button size="big" type="primary" @click="newGoods">添加商品</el-button>
                    </div>

                    <el-table :data="list" border style="width: 100%;margin-top:20px;" v-loading="loading">
                        <el-table-column prop="id" label="ID" width="100"></el-table-column>
                        <el-table-column sortable="custom" label="礼包名称" width="300">
                            <template slot-scope="scope">
                                <div flex="box:first">
                                    <div style="padding-right: 10px;">
                                        <com-image mode="aspectFill" :src="scope.row.cover_pic"></com-image>
                                    </div>
                                    <div >
                                        <div>
                                            <el-tooltip class="item" effect="dark" placement="top">
                                                <template slot="content">
                                                    <div style="width: 320px;">{{scope.row.title}}</div>
                                                </template>
                                                <com-ellipsis :line="2">{{scope.row.title}}</com-ellipsis>
                                            </el-tooltip>
                                        </div>
                                        <div>ID：{{scope.row.pack_id}}</div>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="give_value" label="赠送配置" width="230">
                            <template slot-scope="scope">
                                <div><b>消费者：</b>
                                    <div v-if="scope.row.give_type == 1">按比例{{scope.row.give_value}}%赠送</div>
                                    <div v-if="scope.row.give_type == 2">按固定值{{scope.row.give_value}}赠送</div>
                                </div>
                                <div><b>推荐人：</b>
                                    <div v-for="recommender in scope.row.recommender">
                                        <span v-if="recommender.type == 'branch_office'">城市服务商</span>
                                        <span v-if="recommender.type == 'partner'">区域服务商</span>
                                        <span v-if="recommender.type == 'store'">VIP代理商</span>
                                        <span v-if="recommender.type == 'user'">VIP会员</span>
                                        <span v-if="recommender.give_type == 1">按比例{{recommender.give_value}}%赠送</span>
                                        <span v-if="recommender.give_type == 2">按固定值{{recommender.give_value}}赠送</span>
                                    </div>
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
            <el-form-item label="是否开启">
                <el-switch v-model="aloneSet.is_open" active-text="是" inactive-text="否"></el-switch>
            </el-form-item>
            <template v-if="aloneSet.is_open">
                <el-form-item label="赠送比例" prop="give_value">
                    <div>
                        <el-input type="number" min="0" placeholder="请输入内容" v-model="aloneSet.give_value" style="width:260px;">
                            <el-select v-model="aloneSet.give_type" slot="prepend" placeholder="请选择" style="width:110px;">
                                <el-option label="按比例" value="1"></el-option>
                                <el-option label="按固定值" value="2"></el-option>
                            </el-select>
                            <template slot="append">{{aloneSet.give_type == 1 ? "%" : "券"}}</template>
                        </el-input>
                    </div>
                    <el-table :data="aloneSet.recommender" border style="margin-top:10px;width: 70%">
                        <el-table-column label="级别" width="110" align="center">
                            <template slot-scope="scope">
                                <span v-if="scope.row.type == 'branch_office'">城市服务商</span>
                                <span v-if="scope.row.type == 'partner'">区域服务商</span>
                                <span v-if="scope.row.type == 'store'">VIP代理商</span>
                                <span v-if="scope.row.type == 'user'">VIP会员</span>
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
                    <el-date-picker v-model="aloneSet.start_at" type="date" placeholder="选择日期"></el-date-picker>
                </el-form-item>
            </template>
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
                activeName: 'giftpacks',
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
                    give_type: "1",
                    give_value: 0,
                    start_at: '',
                    recommender: []
                },
                commFormRule:{
                    give_value: [
                        {required: true, message: '赠送比例不能为空', trigger: 'change'},
                    ],
                    start_at:[
                        {required: true, message: '启动日期不能为空', trigger: 'change'},
                    ]
                },
                aloneFormRule:{
                    give_value: [
                        {required: true, message: '赠送比例不能为空', trigger: 'change'},
                    ],
                    start_at:[
                        {required: true, message: '启动日期不能为空', trigger: 'change'},
                    ]
                },
                aloneSet:{
                    is_open:false,
                    give_type: "1",
                    pack_id: 0,
                    give_value: 0,
                    start_at: '',
                    recommender: []
                },
            };
        },
        methods: {
            editStore(row){
                console.log(row);
                this.dialogContent = true;
                this.aloneSet.recommender = row.recommender;
                this.aloneSet.is_open = true;
                this.aloneSet.give_type = row.give_type;
                this.aloneSet.give_value = row.give_value;
                this.aloneSet.start_at = row.start_at;
                this.aloneSet.pack_id = row.pack_id;
            },
            pageChange(page){
                this.page = page;
                this.getList();
            },
            newGoods(){
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
                                r: "plugin/shopping_voucher/mall/from-giftpacks/save-common"
                            },
                            method: "post",
                            data: {
                                is_open:that.commonSet.is_open ? 1 : 0,
                                give_type:that.commonSet.give_type,
                                give_value:that.commonSet.give_value,
                                start_at:that.commonSet.start_at,
                                recommender: that.commonSet.recommender
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
                                r: "plugin/shopping_voucher/mall/from-giftpacks/save"
                            },
                            method: "post",
                            data: {
                                is_open:that.aloneSet.is_open ? 1 : 0,
                                give_type:that.aloneSet.give_type,
                                give_value:that.aloneSet.give_value,
                                start_at:that.aloneSet.start_at,
                                recommender: that.aloneSet.recommender,
                                pack_id: that.aloneSet.pack_id,
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
                            r: "plugin/shopping_voucher/mall/from-giftpacks/delete"
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
                    r: 'plugin/shopping_voucher/mall/from-giftpacks/list'
                }, this.searchData), i;
                params['page'] = this.page;
                request({
                    params
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        let commonData = e.data.data.commonData;

                        this.commonSet.give_type   = commonData.give_type;
                        this.commonSet.is_open     = commonData.is_open == 1 ? true : false;
                        this.commonSet.give_value  = commonData.give_value;
                        this.commonSet.start_at    = commonData.start_at;
                        this.commonSet.recommender = commonData.recommender;

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