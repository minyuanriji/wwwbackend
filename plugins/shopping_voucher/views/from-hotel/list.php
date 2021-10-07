<?php
echo $this->render("../com/com-tab-from");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">

        <com-tab-from :current="activeName"></com-tab-from>

        <div class="table-body">
            <el-alert title="说明：用户通过现金支付酒店预订单，成功后可获得赠送购物券" type="info" :closable="false" style="margin-bottom: 20px;"></el-alert>

            <el-tabs v-model="activeName2" type="border-card">
                <el-tab-pane label="通用配置" name="first">
                    <el-form ref="commonSetForm" :model="commonSet" :rules="commFormRule" label-width="120px">
                        <el-form-item label="是否开启">
                            <el-switch v-model="commonSet.is_allow" active-text="是" inactive-text="否"></el-switch>
                        </el-form-item>
                        <template v-if="commonSet.is_allow">
                            <el-form-item label="赠送比例" prop="give_value">
                                <el-input  type="number" min="0" max="100" placeholder="请输入内容" v-model="commonSet.give_value" style="width:260px;">
                                    <template slot="append">%</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="启动日期" prop="start_at">
                                <el-date-picker v-model="commonSet.start_at" type="date" placeholder="选择日期"></el-date-picker>
                            </el-form-item>
                            <el-form-item >
                                <el-button :loading="loading" type="primary" @click="saveCommon">确 定</el-button>
                            </el-form-item>
                        </template>
                    </el-form>

                </el-tab-pane>
                <el-tab-pane label="指定酒店" name="second">
                    <el-table :data="list" border style="width: 100%" v-loading="loading">
                        <el-table-column prop="id" label="ID" width="100"></el-table-column>
                        <el-table-column sortable="custom" label="酒店名称" width="300">
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

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'hotel',
                activeName2: 'first',
                editDialogVisible: false,
                editData: {},
                list: [],
                pagination: null,
                loading: false,
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
            saveCommon(){
                let that = this;
                this.$refs['commonSetForm'].validate((valid) => {
                    if (valid) {
                        that.loading = true;
                        request({
                            params: {
                                r: "plugin/shopping_voucher/mall/from-hotel/save-common"
                            },
                            method: "post",
                            data: {
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
            }
        },
        mounted: function() {}
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