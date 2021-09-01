<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>商品记录</span>
        </div>
        <div class="table-body">

            <div class="input-item">
                <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                          clearable @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
            </div>

            <div style="float: right">
                <el-button type="primary" size="small" style="padding: 9px 15px !important;"  @click="addBaopin()">添加商品</el-button>
            </div>
            <el-tabs v-model="activeName" @tab-click="handleClick">

                <el-table @sort-change="sortReload" :data="list" border v-loading="loading" size="small" style="margin-bottom: 15px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column align='center' type="selection" width="60"></el-table-column>
                    <el-table-column sortable="custom" prop="id" width="80" label="ID"></el-table-column>
                    <el-table-column sortable="custom" prop="goods_name" label="商品名称" width="350">
                        <template slot-scope="scope">
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.cover_pic"></com-image>
                                </div>
                                <div flex="cross:top cross:center">
                                    <div flex="dir:left">
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{scope.row.name}}(goods_id:{{scope.row.goods_id}})</div>
                                            </template>
                                            <com-ellipsis :line="2">{{scope.row.name}}(goods_id:{{scope.row.goods_id}})</com-ellipsis>
                                        </el-tooltip>

                                    </div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="scope" width="200" label="添加时间">
                        <template slot-scope="scope">
                            {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="scope" width="200" label="更新时间">
                        <template slot-scope="scope">
                            {{scope.row.updated_at|dateTimeFormat('Y-m-d H:i:s')}}
                        </template>
                    </el-table-column>

                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-link type="danger" underline="true" icon="el-icon-delete" @click="delete_bp(scope.row.id)">删除</el-link>&nbsp;
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
                <el-button @click="batch_delete_bp()" style="border:1px solid #ddd;padding: 9px 15px !important;">批量删除</el-button>
            </div>
        </div>

    </el-card>


    <el-dialog title="添加商品" :visible.sync="add_dialog_visible" width="30%">
        <el-input @keyup.enter.native="loadGoodsList"
                  size="small" placeholder="搜索商品"
                  v-model="search_goods.keyword"
                  clearable @clear="toGoodsSearch"
                  style="width:300px;">
            <el-button slot="append" icon="el-icon-search" @click="toGoodsSearch"></el-button>
        </el-input>
        <el-table @selection-change="handleGoodsSelectionChange" v-loading="get_goods_loading" :data="goods_list">
            <el-table-column align='center' type="selection" width="60"></el-table-column>
            <el-table-column property="id" label="商品ID" width="90"></el-table-column>
            <el-table-column label="商品名称">
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
        </el-table>

        <el-pagination
                v-if="goods_list.length > 0"
                style="display: inline-block;"
                background :page-size="goods_pagination.pageSize"
                @current-change="goodsPageChange"
                layout="prev, pager, next" :current-page="goods_pagination.current_page"
                :total="goods_pagination.total_count">
        </el-pagination>

        <span slot="footer" class="dialog-footer">
            <el-button @click="add_dialog_visible = false">关 闭</el-button>
            <el-button :loading="do_save_loading" type="primary" @click="doSave">确 定</el-button>
        </span>

    </el-dialog>
</div>



<script>
    const app = new Vue({
        el: '#app',
        data: {
            get_goods_loading: false,
            add_dialog_visible: false,
            edit_score_loading: false,
            edit_integral_loading: false,
            do_save_loading: false,
            search: {
                keyword: '',
                page: 1,
                platform: '',
                sort_prop: '',
                sort_type: '',
            },
            search_goods:{
                keyword: '',
                page: 1,
            },
            loading: false,
            activeName: '-1',
            list: [],
            pagination: null,
            dialogLoading: false,
            edit: {
                show: false,
            },
            label_position_top: "top",
            goods_list: [],
            goods_pagination: null,
            goods_selections: [],
            selections: []
        },
        mounted() {
            this.loadData();
        },
        methods: {

            sortReload(column){
                this.search.sort_prop = column.prop;
                this.search.sort_type = column.order == "descending" ? 0 : 1;
                this.loadData();
            },

            handleGoodsSelectionChange(selection) {
                this.goods_selections = selection;
            },

            //批量删除爆品
            batch_delete_bp(){

                if(this.selections.length <= 0){
                    this.$alert('请选择爆品记录');
                    return;
                }

                let self = this, i, ids = [];
                for(i=0; i < self.selections.length; i++){
                    ids.push(self.selections[i].id);
                }

                this.$confirm('此操作将永久删除商品记录，是否继续？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.loading = true;
                    request({
                        params: {
                            r: "plugin/shopping_voucher/mall/goods/batch-delete-goods"
                        },
                        method: 'post',
                        data: {
                            id_str: ids.join(",")
                        }
                    }).then(e => {
                        self.loading = false;
                        if (e.data.code === 0) {
                            self.loadData();
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.loading = false;
                        self.$message.error("request fail");
                    });
                });
            },

            //删除记录
            delete_bp(id){
                var self = this;
                this.$confirm('此操作将永久删除商品记录，是否继续？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.loading = true;
                    request({
                        params: {
                            r: "plugin/shopping_voucher/mall/goods/delete-goods"
                        },
                        method: 'post',
                        data: {
                            id: id
                        }
                    }).then(e => {
                        self.loading = false;
                        if (e.data.code === 0) {
                            self.loadData();
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.loading = false;
                        self.$message.error("request fail");
                    });
                });
            },

            //加载商品数据
            loadGoodsList(){
                let self = this;
                self.get_goods_loading = true;
                request({
                    params: {
                        r: "plugin/shopping_voucher/mall/goods/search-goods"
                    },
                    method: 'post',
                    data: {
                        page: self.search_goods.page,
                        keyword: self.search_goods.keyword
                    }
                }).then(e => {
                    self.get_goods_loading = false;
                    if (e.data.code === 0) {
                        self.goods_list = e.data.data.list;
                        self.goods_pagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.get_goods_loading = false;
                    self.$message.error("request fail");
                });
            },

            //添加爆品
            addBaopin(){
                this.add_dialog_visible = true;
                this.loadGoodsList();
            },

            doSave(){

                if(this.goods_selections.length <= 0){
                    this.$alert('请选择商品');
                    return;
                }

                let self = this, i, goods_ids = [];
                for(i=0; i < self.goods_selections.length; i++){
                    goods_ids.push(self.goods_selections[i].id);
                }

                self.do_save_loading = true;
                request({
                    params: {
                        r: "plugin/shopping_voucher/mall/goods/save"
                    },
                    method: 'post',
                    data: {
                        goods_id_str: goods_ids.join(",")
                    }
                }).then(e => {
                    self.do_save_loading = false;
                    if (e.data.code === 0) {
                        self.add_dialog_visible = false;
                        self.loadGoodsList();
                        self.loadData();
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.do_save_loading = false;
                    self.$message.error("request fail");
                });
            },

            //保存积分赠送设置
            save_score_setting(goods_id, form){
                this.edit_score_loading = true;
                let self = this;
                request({
                    params: {
                        r: "plugin/shopping_voucher/mall/goods/update-score-setting"
                    },
                    method: 'post',
                    data: {
                        goods_id: goods_id,
                        form: form
                    }
                }).then(e => {
                    self.edit_score_loading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.edit_score_loading = false;
                    self.$message.error("request fail");
                });
            },

            //保存购物券赠送设置
            save_integral_setting(goods_id, form){
                let self = this;
                self.edit_integral_loading = true;
                request({
                    params: {
                        r: "plugin/shopping_voucher/mall/goods/update-integra-setting"
                    },
                    method: 'post',
                    data: {
                        goods_id: goods_id,
                        form: form
                    }
                }).then(e => {
                    self.edit_integral_loading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.edit_integral_loading = false;
                    self.$message.error("request fail");
                });
            },

            //编辑爆品
            addOrEdit(row){
                this.openoff = true;
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/shopping_voucher/mall/goods/list'
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
            goodsPageChange(page){
                this.search_goods.page = page;
                this.loadGoodsList();
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
            toGoodsSearch(){
                this.search_goods.page = 1;
                this.loadGoodsList();
            },
            handleSelectionChange(val) {
                this.selections = val;
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