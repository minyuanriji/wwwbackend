<?php
Yii::$app->loadComponentView('com-taobao-goods-import', '@app/plugins/taobao/views/components/');
Yii::$app->loadComponentView('com-taobao-goods-import-edit', '@app/plugins/taobao/views/components/');
?>
<template id="com-taobao-goods">
    <div>
        <template v-if="importMode == 0">
            <el-input @change="toSearch" placeholder="关键词搜索" v-model="search.keyword" style="width:300px;">
                <el-button @click="toSearch" slot="append" icon="el-icon-search"></el-button>
            </el-input>
            <el-table :data="list" border v-loading="loading" size="small" style="margin: 15px 0;">
                <el-table-column align='center' type="selection" width="80"></el-table-column>
                <el-table-column width="110" label="类别" prop="category_name"></el-table-column>
                <el-table-column sortable="custom" label="商品名称" width="320">
                    <template slot-scope="scope">
                        <a :href="scope.row.url" target="_blank">
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.pict_url"></com-image>
                                </div>
                                <div flex="cross:top cross:center">
                                    <div flex="dir:left">
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{scope.row.title}}</div>
                                            </template>
                                            <com-ellipsis :line="2">{{scope.row.title}}</com-ellipsis>
                                        </el-tooltip>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </template>
                </el-table-column>
                <el-table-column width="110" label="一口价（元）" prop="reserve_price"></el-table-column>
                <el-table-column width="110" label="邮费（元）" prop="real_post_fee"></el-table-column>
                <el-table-column width="110" label="佣金（%）" >
                    <template slot-scope="scope">
                        {{commissionRate(scope.row)}}
                    </template>
                </el-table-column>
                <el-table-column width="350" label="优惠券信息">
                    <template slot-scope="scope">
                        <el-table size="small" :show-header="false" :data="couponInfos(scope.row)"  border style="width: 100%">
                            <el-table-column prop="label" width="100" align="right"></el-table-column>
                            <el-table-column prop="content"></el-table-column>
                        </el-table>
                    </template>
                </el-table-column>
                <el-table-column width="110" label="库存" prop="volume"></el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="importIt(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="上架" placement="top">
                                <img src="statics/img/mall/pass.png" alt="">
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
        </template>

        <com-taobao-goods-import
                :account="account"
                @success="importSuccess"
                @close="importShow=false"
                v-if="importMode == 1" :import-list="importList"></com-taobao-goods-import>


        <com-taobao-goods-import-edit
                @finish="importMode=0"
                :goods-id-list="importGoodsIds"
                v-if="importMode == 2"></com-taobao-goods-import-edit>

    </div>
</template>
<script>
    Vue.component('com-taobao-goods', {
        template: '#com-taobao-goods',
        props: {
            account: Number
        },
        data() {
            return {
                loading: false,
                list: [],
                pagination: null,
                search: {
                    keyword: '',
                    page: 1
                },

                importMode: 0,
                importList: [],
                importGoodsIds: []
            };
        },
        created() {
            this.loadData();
        },
        computed: {
            couponInfos(item){
                return function(item){
                    return [
                        {label: "起始金额", content: item.coupon_start_fee},
                        {label: "优惠金额", content: item.coupon_amount},
                        {label: "开始日期", content: item.coupon_start_time},
                        {label: "结束日期", content: item.coupon_end_time},
                        {label: "总数量", content: item.coupon_total_count},
                        {label: "剩余数量", content: item.coupon_remain_count},
                        {label: "说明", content: item.coupon_info},
                    ];
                }
            },
            commissionRate(item){
                return function(item){
                    return (item.commission_rate/100).toFixed(2);
                }
            }
        },
        watch: {

        },
        methods: {
            toSearch(){
                this.list = [];
                this.search.page = 1;
                this.loadData();
            },
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            importSuccess(data){
                this.importList = [];
                this.importGoodsIds = data.goods_id_list;
                this.importMode = 2;
            },
            importIt(item){
                this.importMode = 1;
                item['is_edit'] = false;
                item['price'] = item.reserve_price;
                this.importList = [item];
            },
            loadData(){
                this.loading = true;
                let params = {
                    r: 'plugin/taobao/mall/goods/remote-search'
                };
                this.search['account_id'] = this.account;
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
