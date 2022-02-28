<?php
echo $this->render("com-edit");
echo $this->render("../com/com-tab-from");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">

        <com-tab-from :current="activeName"></com-tab-from>

        <div class="table-body">
            <el-alert title="说明：用户通过现金支付商城商品订单，成功后可获得赠送红包" type="info" :closable="false" style="margin-bottom: 20px;"></el-alert>


            <el-tabs v-model="activeName2" type="border-card">
                <el-tab-pane label="通用配置" name="first">
                    <el-form ref="commonSetForm" :model="commonSet" :rules="commFormRule" label-width="120px">
                        <el-form-item label="是否开启">
                            <el-switch v-model="commonSet.is_open" active-text="是" inactive-text="否"></el-switch>
                        </el-form-item>
                        <template v-if="commonSet.is_open">
                            <el-form-item label="赠送比例" prop="give_value">
                                <el-input  type="number" min="0" max="100" placeholder="请输入内容" v-model="commonSet.give_value" style="width:260px;">
                                    <template slot="append">%</template>
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
                <el-tab-pane label="指定商品" name="second">

                    <div style="">
                        <el-button size="big" type="primary" @click="newGoods">添加商品</el-button>
                    </div>

                    <el-table :data="list" border style="width: 100%;margin-top:20px;" v-loading="loading">
                        <el-table-column prop="id" label="ID" width="100"></el-table-column>
                        <el-table-column sortable="custom" label="商品名称" width="300">
                            <template slot-scope="scope">
                                <div flex="box:first">
                                    <div style="padding-right: 10px;">
                                        <com-image mode="aspectFill" :src="scope.row.cover_pic"></com-image>
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
                                        <div>ID：{{scope.row.goods_id}}</div>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="赠送比例">
                            <template slot-scope="scope">
                                <div>{{scope.row.give_value ? (scope.row.give_value+"%") : "-"}}</div>
                            </template>
                        </el-table-column>
                        <el-table-column label="运费（运营费）">
                            <template slot-scope="scope">
                                <span v-if="scope.row.enable_express == 1" style="color:darkgreen">送红包</span>
                                <span v-if="scope.row.enable_express == 0" style="color:gray;">不送红包</span>
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

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'mallGoods',
                activeName2: 'first',
                editDialogVisible: false,
                editData: {},
                list: [],
                page:1,
                pagination: null,
                loading: false,
                searchData: {},
                commonSet:{
                    is_open:false,
                    give_value: '',
                    start_at: ''
                },
                commFormRule:{
                    give_value: [
                        {required: true, message: '赠送比例不能为空', trigger: 'change'},
                    ],
                    start_at:[
                        {required: true, message: '启动日期不能为空', trigger: 'change'},
                    ]
                }
            };
        },
        methods: {
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
                                r: "plugin/shopping_voucher/mall/from-mall-goods/save-common"
                            },
                            method: "post",
                            data: {
                                is_open:that.commonSet.is_open ? 1 : 0,
                                give_value:that.commonSet.give_value,
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
                            r: "plugin/shopping_voucher/mall/from-mall-goods/delete"
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
                    r: 'plugin/shopping_voucher/mall/from-mall-goods/list'
                }, this.searchData);
                params['page'] = this.page;
                request({
                    params
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        let commonData = e.data.data.commonData;
                        this.commonSet.is_open    = commonData.is_open == 1 ? true : false;
                        this.commonSet.give_value = commonData.give_value;
                        this.commonSet.start_at   = commonData.start_at;
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
</style>