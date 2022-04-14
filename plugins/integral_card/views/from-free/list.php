<?php
echo $this->render("com-edit");
echo $this->render("../com/com-tab-from");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">

        <com-tab-from :current="activeName"></com-tab-from>

        <div class="table-body">

            <div class="input-item">
                <el-input @keyup.enter.native="search" placeholder="请输入关键词搜索" v-model="searchData.keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <div style="float: right">
                <el-button type="primary" style="padding: 9px 15px !important;"  @click="newFree">添加领取活动</el-button>
            </div>
            <el-table :data="list" border style="width: 100%" v-loading="loading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="name" label="名称" width="200"></el-table-column>
                <el-table-column label="赠送配置">
                    <template slot-scope="scope">
                        <span v-if="scope.row.enable_score != 1">已关闭</span>
                        <span v-else>
                            <span v-if="scope.row.score_give_settings.is_permanent == 1">
                                永久积分，赠送数量{{scope.row.number}}
                            </span>
                            <span v-else>
                                限时积分，赠送数量{{scope.row.number}}，
                                送{{scope.row.score_give_settings.period}}个月，
                                有效期{{scope.row.score_give_settings.expire}}天
                            </span>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="150" label="开始时间">
                    <template slot-scope="scope">
                        {{scope.row.start_at}}
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="150" label="结束时间">
                    <template slot-scope="scope">
                        {{scope.row.end_at}}
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
                        <el-button @click="editFree(scope.row)" type="text" circle size="mini">
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

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'free',
                editDialogVisible: false,
                editData: {},
                searchData: {
                    keyword: ''
                },
                date: '',
                list: [],
                pagination: null,
                loading: false
            };
        },
        methods: {
            newFree(){
                this.editData = {};
                this.editDialogVisible = true;
            },
            editFree(row){
                this.editData = row;
                this.editDialogVisible = true;
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
                            r: 'plugin/integral_card/admin/from-free/delete'
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
                    r: 'plugin/integral_card/admin/from-free/list',
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