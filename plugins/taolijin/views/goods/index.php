<?php
echo $this->render("com-edit");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>商品管理</span>
        </div>
        <div class="table-body">

            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>

            <div style="float: right">
                <el-button type="primary" size="small" style="padding: 9px 15px !important;"  @click="edit({})">添加商品</el-button>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">

                <el-table @sort-change="sortReload" :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column align='center' type="selection" width="80"></el-table-column>
                    <el-table-column sortable="custom" prop="id" width="90" label="ID"></el-table-column>
                    <el-table-column sortable="custom" label="商品名称" width="320">
                        <template slot-scope="scope">
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.cover_pic"></com-image>
                                </div>
                                <div flex="cross:top cross:center">
                                    <div flex="dir:left">
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{scope.row.name}}</div>
                                            </template>
                                            <com-ellipsis :line="2">{{scope.row.name}}</com-ellipsis>
                                        </el-tooltip>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="price" width="110" label="价格"></el-table-column>
                    <el-table-column prop="deduct_integral" width="110" label="抵扣红包"></el-table-column>
                    <el-table-column width="110" label="状态（上下架）">
                        <template slot-scope="scope">
                            <el-switch v-model="scope.row.status" active-value="1" inactive-value="0"></el-switch>
                        </template>
                    </el-table-column>
                    <el-table-column label="来源" width="200">
                        <template slot-scope="scope">
                            <div>平台类型：{{scope.row.ali_text}}</div>
                            <div>唯一编号：{{scope.row.ali_unique_id}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="scope" width="100" label="添加时间">
                        <template slot-scope="scope">
                            {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="scope" width="100" label="更新时间">
                        <template slot-scope="scope">
                            {{scope.row.updated_at|dateTimeFormat('Y-m-d')}}
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button @click="edit(scope.row)" type="text" circle size="mini">
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

            <div>
                <el-button style="border:1px solid #ddd;padding: 9px 15px !important;">批量删除</el-button>
            </div>
        </div>

    </el-card>

    <com-edit :visible="editDialogVisible" :goods-info="edittingGoods"
        @update="update"
        @close="editDialogVisible = false"></com-edit>

</div>



<script>
    const app = new Vue({
        el: '#app',
        data: {
            loading: false,
            list: [],
            pagination: null,
            search: {
                keyword: '',
                page: 1,
                platform: '',
                sort_prop: '',
                sort_type: '',
            },
            selections: [],
            activeName: '-1',
            edittingGoods: {},
            editDialogVisible: false
        },
        mounted() {
            this.loadData();
        },
        methods: {
            edit(row){
                this.edittingGoods = row;
                this.editDialogVisible = true;
            },
            sortReload(column){
                this.search.sort_prop = column.prop;
                this.search.sort_type = column.order == "descending" ? 0 : 1;
                this.loadData();
            },
            handleSelectionChange(val) {
                this.selections = val;
            },
            handleClick(tab, event) {
                this.search.page = 1;
                this.search.status = this.activeName;
                this.loadData()
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/taolijin/mall/goods/list'
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
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            update(){
                this.loadData();
                this.editDialogVisible = false;
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