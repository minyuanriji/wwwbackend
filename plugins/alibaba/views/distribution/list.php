<?php
echo $this->render("com-alibaba-goods");
?>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item>
                     <span style="color: #409EFF;cursor: pointer"
                           @click="$navigate({r:'plugin/alibaba/mall/app/list'})">应用管理</span>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item >社交电商</el-breadcrumb-item>
                    <el-breadcrumb-item >商品管理</el-breadcrumb-item>
                </el-breadcrumb>
            </div>
        </div>

        <div class="table-body">

            <div class="input-item">
                <el-input @keyup.enter.native="search" placeholder="请输入关键词搜索" v-model="searchData.keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <div style="float: right">
                <el-button type="primary"  @click="aliGoodsDialogVisible = true">添加商品</el-button>
            </div>

            <el-table :data="list" border style="width: 100%" v-loading="loading">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
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
                        <el-button @click="" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="" type="text" circle size="mini">
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
                    :page-size="pagination.pageSize"
                    :total="pagination.total_count"
                    style="float:right;margin:15px"
                    v-if="pagination">
                </el-pagination>
            </el-col>
        </div>

        <com-alibaba-goods @close="aliGoodsDialogVisible = false"
                           :visible="aliGoodsDialogVisible"></com-alibaba-goods>

    </el-card>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                aliGoodsDialogVisible: false,
                activeName: 'first',
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
            search() {
                this.page = 1;
                this.getList();
            },
            getList() {
                let params = Object.assign(this.searchData, {
                    r: 'plugin/alibaba/mall/distribution/goods-list',
                    page: this.page,
                    app_id:getQuery("app_id")
                });
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

</style>