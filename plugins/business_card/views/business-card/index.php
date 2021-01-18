<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片
 * Author: zal
 * Date: 2020-07-10
 * Time: 15:48
 */

?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>名片列表</span>
            <el-form size="small" :inline="true" :model="search" style="float: right;margin-top: -5px;">
                <el-form-item>
                    <com-export-dialog :field_list='exportList' :params="search" @selected="confirmSubmit">
                    </com-export-dialog>
                </el-form-item>
            </el-form>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keywords"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column prop="user_id" width="80" label="用户ID"></el-table-column>
                    <el-table-column prop="full_name" width="80" label="姓名"></el-table-column>
                    <el-table-column label="手机号" prop="mobile">
                        <template slot-scope="scope">
                            <div>{{scope.row.mobile}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="公司名" prop="company_name">
                        <template slot-scope="scope">
                            <div>{{scope.row.company_name}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="职位" prop="position_name">
                        <template slot-scope="scope">
                            <div>{{scope.row.position_name}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="邮箱" prop="email" width="200px"></el-table-column>
                    </el-table-column>
                    <el-table-column label="操作" width="300px">
                        <template slot-scope="scope">
                            <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                       @click.native="toEdit(scope.row.id)">
                                <el-tooltip class="item" effect="dark" content="查看" placement="top">
                                    <img src="statics/img/mall/business_card/business_card.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button @click="destroy(scope.row, scope.$index)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-tabs>
            <div flex="box:last cross:center">
                <div></div>
                <div>
                    <el-pagination
                            v-if="list.length > 0"
                            style="display: inline-block;float: right;"
                            background :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next" :current-page="pagination.current_page"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data: {
            qrimg: '',
            showqr: false,
            avatar: '',
            nickname: '',
            search: {
                keywords: '',
            },
            loading: false,
            activeName: '-1',
            list: [],
            pagination: null,
            dialogChild: false,
            dialogLoading: false,
            select: {
                nickname: '',
                status: 'first',
            },
            dialogContent: false,
            remarksForm: {
                remarks: '',
                id: ''
            },
            remarksLoading: false,
            exportList: [],
            edit: {
                show: false,
            },
            choose_list: []
        },
        mounted() {
            this.loadData();
        },
        methods: {
            down() {
                var alink = document.createElement("a");
                alink.href = this.qrimg;
                alink.download = this.nickname;
                alink.click();
            },

            confirmSubmit() {
                this.search.status = this.activeName
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/business_card/mall/business-card/index'
                };
                params = Object.assign(params, this.search);
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.exportList = e.data.data.export_list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/business_card/mall/business-card/delete',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).
                    catch(e => {
                        console.log(e);
                    })
                    ;
                }).catch(() => {
                    self.$message.info('已取消删除')
                })
                ;
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            handleClick(tab, event) {
                this.search.page = 1;
                this.search.status = this.activeName;
                this.loadData()
            },
            order(id) {
                navigateTo({
                    r: 'mall/distribution/order',
                    id: id
                })
            },
            toEdit(user_id) {
                navigateTo({
                    r: 'plugin/business_card/mall/business-card/detail',
                    id: user_id
                })
            },

            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            remarks(row) {
                this.dialogContent = true;
                this.remarksForm = {
                    remarks: row.remarks,
                    id: row.id
                }
            },
            editClick() {
                this.edit.show = true;
            },
            editLevel(row) {
                this.level.show = true;
                this.level.distribution = row;
            },
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.id);
                })
            },
            levelSuccess() {
                this.loadData();
            }
        }
    });
</script>
<style>
    .el-tabs__header {
        font-size: 16px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
        display: inline-block;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
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

    .batch {
        margin: 0 0 20px;
        display: inline-block;
    }

    .batch .el-button {
        padding: 9px 15px !important;
    }
</style>