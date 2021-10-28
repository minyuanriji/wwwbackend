<?php
echo $this->render("com-edit");
echo $this->render("com-update");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">

        <div style="background: white;margin-bottom: 20px;padding-left:20px;">
            <el-tabs v-model="activeName">
                <el-tab-pane label="商户列表" name="first"></el-tab-pane>
            </el-tabs>
        </div>

        <div class="table-body">
            <el-alert title="说明：用户通过扫商户二维码进行付款，成功后可获得赠送积分" type="info" :closable="false" style="margin-bottom: 20px;"></el-alert>

            <div class="input-item">
                <el-input @keyup.enter.native="search" placeholder="请输入关键词搜索" v-model="searchData.keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <div style="float: right">
                <el-button type="primary" style="padding: 9px 15px !important;"  @click="newStore">添加商户</el-button>
            </div>
            <el-table :data="list" border style="width: 100%" v-loading="loading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column sortable="custom" label="商户名称" width="350">
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
                <el-table-column prop="give_value" label="赠送配置" width="300">
                    <template slot-scope="scope">
                        <span v-if="scope.row.enable_score != 1">已关闭</span>
                        <span v-else>
                            <span v-if="scope.row.score_give_settings.is_permanent == 1">
                                永久，赠送比例{{scope.row.rate}}%
                            </span>
                            <span v-else>
                                限时，赠送比例{{scope.row.rate}}%，
                                送{{scope.row.score_give_settings.period}}个月
                            </span>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="200" label="启动时间">
                    <template slot-scope="scope">
                        {{scope.row.start_at}}
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="150" label="添加时间">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="150" label="更新时间">
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
              @update="update"></com-edit>

    <com-update :visible="updateDialogVisible"
              :edit-data="editData"
              @up_close="up_close"
              @up_update="up_update"></com-update>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'first',
                editDialogVisible: false,
                updateDialogVisible: false,
                editData: {},
                searchData: {
                    keyword: ''
                },
                date: '',
                list: [],
                pagination: null,
                loading: false,

            };
        },
        methods: {
            newStore(){
                this.editData = {};
                this.editDialogVisible = true;
            },
            editStore(row){
                this.editData = row;
                this.updateDialogVisible = true;
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
                            r: 'plugin/integral_card/admin/from-store/delete'
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
                let params = {
                    r: 'plugin/integral_card/admin/from-store/list',
                    page: this.page,
                    keyword: this.searchData.keyword,
                };
                request({
                    params,
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
                this.close();
            },
            close(){
                this.editDialogVisible = false;
            },
            up_update(){
                this.getList();
                this.up_close();
            },
            up_close(){
                this.updateDialogVisible = false;
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

    .table-body .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
</style>