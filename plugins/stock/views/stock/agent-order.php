<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:48
 */
Yii::$app->loadPluginComponentView('agent-order-edit');
?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>自取订单</span>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;">
                    <el-table-column prop="id" width="80" label="ID"></el-table-column>

                    <el-table-column label="用户信息" >
                        <template slot-scope="scope">
                            <com-image style="float: left;margin-right: 5px;" mode="aspectFill"
                                       :src="scope.row.avatar_url"></com-image>
                            <div>{{scope.row.nickname}}</div>

                        </template>
                    </el-table-column>
                    <el-table-column label="商品信息">
                        <template slot-scope="scope">
                            <com-image style="float: left;margin-right: 5px;" mode="aspectFill"
                                       :src="scope.row.cover_pic"></com-image>
                            <div>{{scope.row.goods_name}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="商品数量" prop="num"></el-table-column>
                    <el-table-column label="收货信息">
                        <template slot-scope="scope">
                            <div>收货地址： {{scope.row.address}}</div>
                            <div>收货人： {{scope.row.name}} {{scope.row.mobile}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="发货信息">
                        <template slot-scope="scope">
                            <div>快递名称： {{scope.row.express_name}}</div>
                            <div>快递单号： {{scope.row.express_no}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="created_at"  label="提交时间"></el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                                <el-button type="text" @click="editClick(scope.row.id)" v-if="scope.row.status==0">发货</el-button>
                            <el-button type="text"   v-if="scope.row.status==1">已发货</el-button>
                            <el-button type="text"   v-if="scope.row.status==2">已收货</el-button>
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
    <agent-order-edit v-model="edit.show" :edit_row="edit"></agent-order-edit>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data: {
            edit: {
                show: false,
                id: 0,
            },
            loading: false,
            activeName: '-1',
            list: [],
            pagination: null,
            agent_level_list: [],
            search: {
                page: 1,
                keyword:'',
            }
        },
        mounted() {
            this.loadData();
        },
        methods: {
            editClick(id) {
                if (id) {
                    this.edit.id = id;
                    this.edit.show = true;
                } else {
                    this.edit.show = true;
                }
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/stock/mall/stock/agent-order'
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
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            handleClick(tab, event) {
                this.search.page = 1;
                this.search.status = this.activeName;
                this.loadData()
            },
            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
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