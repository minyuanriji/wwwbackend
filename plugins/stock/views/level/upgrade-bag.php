<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:11
 */
Yii::$app->loadPluginComponentView('upgrade-bag-edit');
?>

<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>升级礼包</span>
                <div style="float: right; margin: -5px 0">
                    <el-button type="primary" @click="editClick" size="small">添加礼包方案</el-button>
                </div>
            </div>
        </div>
        <div class="table-body">
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    style="width: 100%">
                <el-table-column
                        prop="id"
                        label="ID"
                        width="80">
                </el-table-column>

                <el-table-column prop="name" label="配置名称">

                </el-table-column>
                <el-table-column prop="level" label="商品信息" >
                    <template slot-scope="scope">
                        <com-image :src="scope.row.cover_pic"></com-image>
                        <div>{{scope.row.goods_name}}</div>
                    </template>
                </el-table-column>
                <el-table-column label="升级等级" >
                    <template slot-scope="scope">
                        <com-ellipsis :line="1">{{scope.row.level_name}}</com-ellipsis>
                    </template>
                </el-table-column>

                <el-table-column
                        label="关联库存"
                        width="120">
                    <template slot-scope="scope">
                        <el-switch
                                v-model="scope.row.is_stock"
                                :active-value="1"
                                :inactive-value="0"
                                disabled>
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column prop="level" label="库存商品信息" >
                    <template slot-scope="scope">
                        <com-image :src="scope.row.stock_cover_pic"></com-image>
                        <div>{{scope.row.stock_goods_name}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="stock_num" label="库存商品数量" >
                </el-table-column>
                <el-table-column prop="compute_type" label="计算方式">
                    <template slot-scope="scope">
                        {{scope.row.compute_type=='1'?'支付后':'订单完成后'}}
                    </template>
                </el-table-column>
                <el-table-column
                        label="启用状态"
                >
                    <template slot-scope="scope">
                        <el-switch
                                :active-value="1"
                                :inactive-value="0"
                                v-model="scope.row.is_enable"
                                disabled>
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column
                        label="操作"
                       >
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text" @click="editClick(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" @click="bagDelete(scope.row, scope.$index)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>

                    </template>
                </el-table-column>
            </el-table>

            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                        @current-change="pagination"
                        background
                        layout="prev, pager, next"
                        :page-count="pageCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
    <upgrade-bag-edit v-model="edit.show" :edit_row="edit"></upgrade-bag-edit>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                edit: {
                    show: false,
                    id: 0,
                },
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
            };
        },
        mounted: function () {
            this.getList();
        },
        methods: {
            search() {
                this.page = 1;
                this.getList();
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/stock/mall/level/upgrade-bag',
                        page: self.page,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },


            editClick(id) {
                if (id) {
                    this.edit.id = id;
                    this.edit.show = true;
                } else {

                    this.edit.show = true;
                }

            },
            bagDelete(row, index) {
                let self = this;
                self.$confirm('删除该升级礼包, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/stock/mall/level/upgrade-bag-delete',
                            id: row.id,
                        },
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.list.splice(index, 1);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
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
        margin: 0 0 20px;
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