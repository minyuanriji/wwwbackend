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

            <el-table :data="list" border style="width: 100%" v-loading="loading" @selection-change="handleSelectionChange">
                <el-table-column align='center' type="selection" width="60"></el-table-column>
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column label="类目" width="200">
                    <template slot-scope="scope">
                        <el-tag size="small" :key="item.label" v-for="item in scope.row.categorys" style="margin-left:10px;">
                            {{item.name}}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="标题" >
                    <template slot-scope="scope">
                        <div style="padding-bottom:3px;">编号：{{scope.row.ali_offerId}}</div>
                        <div flex="box:first">
                            <div style="padding-right: 10px;">
                                <com-image mode="aspectFill" :src="scope.row.cover_url"></com-image>
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
                <el-table-column prop="price" width="110" label="零售价"></el-table-column>
                <el-table-column prop="origin_price" width="110" label="划线价"></el-table-column>
                <el-table-column width="90" label="分销价">
                    <template slot-scope="scope">
                        {{scope.row.ali_data_json.currentPrice}}
                    </template>
                </el-table-column>
                <el-table-column width="90" label="渠道价">
                    <template slot-scope="scope">
                        {{scope.row.ali_data_json.channelPrice}}
                    </template>
                </el-table-column>
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
                        <!--
                        <el-button @click="" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        -->
                        <el-button @click="deleteIt(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div style="display: flex;justify-content: space-between;margin-top:20px;">
                <div style="margin: 7.5px 0px;" v-if="batchSetForm.selections.length > 0">
                    <el-button @click="batchSetForm.dialogVisible = true" type="primary">批量设置</el-button>
                    <el-button @click="batchDeleteIt" type="danger">批量删除</el-button>
                </div>
                <el-pagination
                        background
                        layout="prev, pager, next"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="float:right;margin:15px"
                        v-if="pagination">
                </el-pagination>
            </div>

        </div>

        <com-alibaba-goods @close="aliGoodsDialogVisible = false"
                           @import="aliGoodsImport"
                           :visible="aliGoodsDialogVisible"></com-alibaba-goods>

        <el-dialog title="批量设置" :visible.sync="batchSetForm.dialogVisible" :close-on-click-modal="false">

            <div style="display: flex;">
                <el-cascader v-model="batchSetForm.category"
                             :options="batchSetForm.categorys"></el-cascader>
                <el-button type="primary" @click="setCommonCategory" style="margin-left:10px;">统一类目</el-button>
            </div>


            <el-table v-loading="batchSetForm.loading" :data="batchSetForm.selections" border style="margin-top:20px;width: 100%">
                <el-table-column prop="id" label="ID" width="90"></el-table-column>
                <el-table-column label="标题" >
                    <template slot-scope="scope">
                        <div style="padding-bottom:3px;">编号：{{scope.row.ali_offerId}}</div>
                        <div flex="box:first">
                            <div style="padding-right: 10px;">
                                <com-image mode="aspectFill" :src="scope.row.cover_url"></com-image>
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
                <el-table-column width="190" label="类目">
                    <template slot-scope="scope">
                        <el-cascader v-model="scope.row.ali_category_id" :options="batchSetForm.categorys"></el-cascader>
                    </template>
                </el-table-column>
                <el-table-column prop="price" width="110" label="零售价">
                    <template slot-scope="scope">
                        <el-input v-model="scope.row.price"></el-input>
                    </template>
                </el-table-column>
                <el-table-column prop="origin_price" width="110" label="划线价">
                    <template slot-scope="scope">
                        <el-input v-model="scope.row.origin_price"></el-input>
                    </template>
                </el-table-column>
                <el-table-column width="75" label="分销价">
                    <template slot-scope="scope">
                        {{scope.row.ali_data_json.currentPrice}}
                    </template>
                </el-table-column>
                <el-table-column width="75" label="渠道价">
                    <template slot-scope="scope">
                        {{scope.row.ali_data_json.channelPrice}}
                    </template>
                </el-table-column>
            </el-table>
            <div slot="footer" class="dialog-footer">
                <el-button :loading="batchSetForm.loading" type="primary" @click="batchSetConfirm">确 定</el-button>
            </div>
        </el-dialog>

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
                loading: false,


                batchSetForm: {
                    category:[],
                    dialogVisible: false,
                    categorys: [],
                    loading: false,
                    selections: []
                }

            };
        },
        methods: {
            handleSelectionChange(selection) {
                this.batchSetForm.selections = selection;
            },
            batchDeleteIt(){
                let i, ids = [];
                for(i=0; i < this.batchSetForm.selections.length; i++){
                    ids.push(this.batchSetForm.selections[i].id);
                }
                this.deleteWithIds(ids);
            },
            deleteIt(row){
                let ids = [];
                ids.push(row.id);
                this.deleteWithIds(ids);
            },
            deleteWithIds(id_arr){
                this.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/alibaba/mall/distribution/delete-goods'
                        },
                        method: 'post',
                        data: {id:id_arr}
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.$message.success(e.data.msg);
                            this.getList();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.$message.error(e.data.msg);
                    });
                }).catch(() => {

                });
            },
            setCommonCategory(){
                for(var i=0; i < this.batchSetForm.selections.length; i++){
                    this.batchSetForm.selections[i]['ali_category_id'] = this.batchSetForm.category;
                }
            },
            batchSetConfirm(){
                if(this.batchSetForm.selections.length <= 0){
                    this.$message.error("请选择要设置的商品");
                    return;
                }
                var i,price,currentPrice;
                for(i=0; i < this.batchSetForm.selections.length; i++){
                    if(parseInt(this.batchSetForm.selections[i]['category_id']) <= 0){
                        this.$message.error("请设置类目");
                        return;
                    }
                    price = this.batchSetForm.selections[i]['price'];
                    currentPrice = this.batchSetForm.selections[i]['ali_data_json']['currentPrice'];
                    if(price.length <=0 || isNaN(price)){
                        this.$message.error("零售价必须大于0");
                        return;
                    }
                    if(parseFloat(price) < parseFloat(currentPrice)){
                        this.$message.error("零售价建议大于分销价");
                        return;
                    }
                }

                this.batchSetForm.loading = true;
                request({
                    params: {
                        r: 'plugin/alibaba/mall/distribution/goods-batch-save'
                    },
                    method: 'post',
                    data: {
                        goods_list:this.batchSetForm.selections
                    }
                }).then(e => {
                    this.batchSetForm.loading = false;
                    if (e.data.code == 0) {
                        this.$message.success(e.data.msg);
                        this.batchSetForm.dialogVisible = false;
                        this.getList();
                        this.batchSetForm.selections = [];
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                    this.batchSetForm.loading = false;
                });
            },
            aliGoodsImport(rows){
                this.aliGoodsDialogVisible = false;
                this.batchSetForm.dialogVisible = true;
                this.batchSetForm.selections = rows;
            },
            search() {
                this.page = 1;
                this.getList();
            },
            getCategory(){
                let params = {
                    r: 'plugin/alibaba/mall/distribution/get-category',
                    app_id:getQuery("app_id")
                };
                request({
                    params
                }).then(e => {
                    if (e.data.code === 0) {
                        this.batchSetForm.categorys = e.data.data;
                    }
                }).catch(e => {

                });
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
            this.getCategory();
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