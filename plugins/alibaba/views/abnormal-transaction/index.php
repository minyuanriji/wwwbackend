<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .set-el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }
    .sort-input {
        width: 100%;
        background-color: #F3F5F6;
        height: 32px;
    }

    .sort-input span {
        height: 32px;
        width: 100%;
        line-height: 32px;
        display: inline-block;
        padding: 0 10px;
        font-size: 13px;
    }

    .sort-input .el-input__inner {
        height: 32px;
        line-height: 32px;
        background-color: #F3F5F6;
        float: left;
        padding: 0 10px;
        border: 0;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-info .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px;
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
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>商品交易异常问题</span>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入商品名称或商品ID搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column width='100' prop="id" label="ID"></el-table-column>
                <el-table-column prop="name" label="商品信息">
                    <template slot-scope="scope">
                        <div>
                            <com-image style="float: left;margin-right: 10px;height: 80px;width: 80px"
                                       :src="scope.row.cover_url"></com-image>
                            <div style="margin: 10px 0">{{ scope.row.name }}（ID：{{scope.row.goods_id}}）</div>
                            ¥：<span style="font-size: 16px;color: red">{{ scope.row.price }}</span>
                            <div style="margin-top: 10px">
                                规格：{{ scope.row.sku_name }}
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="remark" label="异常"></el-table-column>
                <el-table-column prop="flag" label="结果" width="100">
                    <template slot-scope="scope">
                        <span v-if="scope.row.flag==1" style="color: gray">已处理</span>
                        <span v-else style="color: red">未处理</span>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="时间" width="160">
                    <template slot-scope="scope">
                        <div>{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align:center;">
                <el-pagination v-if="pagination" :page-size="pagination.pageSize"
                               style="display: inline-block;" background @current-change="pageChange"
                               layout="prev, pager, next" :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            loading: false,
            list: [],
            keyword: '',
            pagination: null,
            page: 1,
        };
    },

    methods: {
        //搜索
        search() {
            this.page = 1;
            this.loadData();
        },
        //分页
        pageChange(page) {
            this.page = page;
            this.loadData();
        },

        // 根据参数获取请求信息
        loadData() {
            this.loading = true;
            request({
                params: {
                    r: 'plugin/alibaba/mall/abnormal-transaction/index',
                    keyword: this.keyword,
                    page: this.page,
                    status: -1,
                },
            }).then(e => {
                this.loading = false;
                if (e.data.code === 0) {
                    this.list = e.data.data.list;
                    this.pagination = e.data.data.pagination;
                } else {
                    this.listLoading = false;
                    this.$message({
                        message: e.data.msg,
                        type: 'error'
                    });
                }
            }).catch(e => {
                this.listLoading = false;
            });
        }
    },
    created() {
        this.loadData();
    }
})
</script>