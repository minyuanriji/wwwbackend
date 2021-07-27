<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:48
 */

?>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>出货收益</span>

        </div>
        <div class="table-body">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-table :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;">

                    <el-table-column prop="id" width="80" label="ID"></el-table-column>

                    <el-table-column prop="order_no"  label="订单编号"></el-table-column>
                    <el-table-column label="基本信息" >
                        <template slot-scope="scope">
                            <com-image style="float: left;margin-right: 5px;" mode="aspectFill"
                                       :src="scope.row.avatar_url"></com-image>
                            <div>{{scope.row.nickname}}</div>
                            <div>{{scope.row.agent_level_name}}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="商品信息">
                        <template slot-scope="scope">
                            <com-image style="float: left;margin-right: 5px;" mode="aspectFill"
                                       :src="scope.row.cover_pic"></com-image>
                            <div>{{scope.row.goods_name}}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="买家信息" >
                        <template slot-scope="scope">
                            <com-image style="float: left;margin-right: 5px;" mode="aspectFill"
                                       :src="scope.row.buy_user_avatar"></com-image>
                            <div>{{scope.row.buy_user_nickname}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="收益" prop="price" ></el-table-column>
                    <el-table-column prop="type"  label="下单类型"></el-table-column>
                    <el-table-column prop="created_at"  label="提成时间"></el-table-column>
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
            loading: false,
            activeName: '-1',
            list: [],
            pagination: null,
            search: {
                page: 1,
            }
        },
        mounted() {
            this.loadData();
        },
        methods: {

            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/stock/mall/stock/goods-price-log'
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