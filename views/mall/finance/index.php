<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-07
 * Time: 16:22
 */
?>


<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>用户财务</span>

            </div>
        </div>
        <div class="table-body">

            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入昵称搜索" v-model="keyword" clearable
                          @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table :data="form" border style="width: 100%" v-loading="listLoading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column label="用户信息" width="280">
                    <template slot-scope="scope">
                        <com-image mode="aspectFill" style="float: left;margin-right: 8px"
                                   :src="scope.row.avatar_url"></com-image>
                        <div>{{scope.row.nickname}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="scope" label="余额" width="400">
                    <template slot-scope="scope">
                        <div>当前余额：{{scope.row.balance}}</div>
                        <br>
                        <div>累计余额：{{scope.row.total_balance}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="scope" label="积分" width="400">
                    <template slot-scope="scope">
                        <div>当前积分：{{scope.row.score}}</div>
                        <br>
                        <div>累计积分：{{scope.row.total_score}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="scope" label="收益" width="400">
                    <template slot-scope="scope">
                        <div>当前收益：{{scope.row.commission}}</div>
                        <br>
                        <div>累计收益：{{scope.row.total_income}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="scope" label="优惠券数量" width="400">
                    <template slot-scope="scope">
                        <div>{{scope.row.coupon_count}}</div>

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
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                searchData: {
                    keyword: '',
                    date: '',
                    start_date: '',
                    end_at: '',
                },
                date: '',
                keyword: '',
                form: [],
                pagination: null,
                listLoading: false,

            };
        },
        methods: {
            exportConfirm() {
                this.searchData.keyword = this.keyword;
                this.searchData.start_date = this.date[0];
                this.searchData.end_date = this.date[1];
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            search() {
                this.page = 1;
                if (this.date == null) {
                    this.date = ''
                }
                this.getList();
            },
            getList() {
                let params = {
                    r: 'mall/finance/index',
                    page: this.page,
                    date: this.date,
                    user_id: getQuery('user_id'),
                    keyword: this.keyword,
                };
                if (this.date) {
                    Object.assign(params, {
                        start_date: this.date[0],
                        end_date: this.date[1],
                    });
                }
                request({
                    params,
                }).then(e => {
                    if (e.data.code === 0) {
                        this.form = e.data.data.list;

                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
                this.listLoading = true;
            },
        },
        mounted: function () {
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
        margin: 0 0 20px 20px;
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

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>