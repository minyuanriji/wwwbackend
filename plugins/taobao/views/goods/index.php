<?php
Yii::$app->loadComponentView('com-taobao-goods', '@app/plugins/taobao/views/components/');
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" >
            <span>商品管理</span>
        </div>
        <div class="table-body">
            <el-tabs v-model="activeName" type="card">
                <el-tab-pane label="商品管理" name="first">

                    <div style="display:flex;align-items: center">
                        <el-input style="width:300px;" @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                                  clearable @clear="toSearch">
                            <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                        </el-input>
                    </div>

                    <el-table :data="list" border v-loading="loading" size="small" style="margin: 15px 0;">
                        <el-table-column prop="goods_id" width="90" label="商品ID"></el-table-column>
                        <el-table-column label="商品名称" width="320">
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
                        <el-table-column label="操作">
                            <template slot-scope="scope">
                                <el-button @click="edit(scope.row)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                        <img src="statics/img/mall/edit.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
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
                </el-tab-pane>
                <el-tab-pane label="添加商品" name="second">
                    <com-taobao-goods :account="1"></com-taobao-goods>
                </el-tab-pane>
            </el-tabs>
        </div>
    </el-card>
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
                sort_prop: '',
                sort_type: '',
            },
            activeName: 'second',
        },
        mounted() {
            this.loadData();
        },
        methods: {
            edit(row){
                var path = window.location.origin + window.location.pathname + '?r=mall%2Fgoods%2Fedit&id=' + row.goods_id + '&mch_id=' + row.mch_id + '&page=' + this.search.page;
                window.open(path, '_blank');
            },

            toSearch() {
                this.search.page = 1;
                this.loadData();
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            loadData(){
                this.loading = true;
                let params = {
                    r: 'plugin/taobao/mall/goods/index'
                };
                params = Object.assign(params, this.search);
                let that = this;
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        this.list       = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
        }
    });
</script>
<style>

</style>