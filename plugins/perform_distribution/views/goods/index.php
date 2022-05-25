<?php
Yii::$app->loadPluginComponentView('com-goods-edit');
Yii::$app->loadComponentView('goods/com-select-goods');
?>
<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>商品设置</span>
                <div style="float: right; margin: -5px 0">
                    <com-select-goods :multiple="false" @selected="goodsSelect" title="商品选择">
                        <el-button type="primary" size="small">添加商品</el-button>
                    </com-select-goods>
                </div>
            </div>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small"  placeholder="请输入商品名称搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="listLoading" :data="list" border style="width: 100%">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="level" label="商品名称" width="350">
                    <template slot-scope="scope">
                        <div style="display: flex;">
                            <com-image :src="scope.row.cover_pic" style="flex-shrink: 0"></com-image>
                            <div style="margin-left:10px;">{{scope.row.name}}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="零售价" width="300">
                    <template slot-scope="scope">
                        <com-ellipsis :line="1" >{{scope.row.goods.price}}</com-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column label="操作" >
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text" @click="edit(scope.row)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" @click="goodsDelete(scope.row, scope.$index)">
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
    <com-goods-edit v-model="isEdit" @on-save="getList" :data="editForm"></com-goods-edit>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                isEdit: false,
                editForm: {
                    id: 0,
                    goods_id: 0,
                    goods: {name: '', price: '', profit_price: '', cover_pic: ''}
                },
                list: [],
                keyword: '',
                listLoading: false,
                page: 1,
                pageCount: 0
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
                        r: 'plugin/perform_distribution/mall/goods/index',
                        page: self.page,
                        keyword: this.keyword
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
            goodsSelect(e){
                let item = {
                    id: 0,
                    goods_id: e.id,
                    goods: {
                        name: e.name,
                        price: e.price,
                        profit_price: e.profit_price,
                        cover_pic: e.goodsWarehouse.cover_pic,
                        original_price: e.goodsWarehouse.original_price
                    }
                };
                this.edit(item);
            },
            edit(item){
                this.editForm = item;
                this.isEdit = true;
            },
            goodsDelete(row, index) {
                let self = this;
                self.$confirm('删除该商品, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/perform_distribution/mall/goods/delete',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
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