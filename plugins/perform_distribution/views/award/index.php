<?php

?>
<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>奖励明细</span>
            </div>
        </div>
        <div class="table-body">

            <el-tabs v-model="activeName" @tab-click="search">
                <el-tab-pane label="待结算" name="0"></el-tab-pane>
                <el-tab-pane label="已结算" name="1"></el-tab-pane>
                <el-tab-pane label="无效" name="2"></el-tab-pane>
            </el-tabs>

            <el-input style="width:400px;" @keyup.enter.native="search" placeholder="请输入关键词搜索" v-model="keyword" clearable @clear="search">
                <el-select slot="prepend" v-model="kw_type" placeholder="请选择" style="width:120px;">
                    <el-option label="用户昵称" value="nickname"></el-option>
                    <el-option label="用户ID" value="user_id"></el-option>
                    <el-option label="商品名称" value="goods_name"></el-option>
                    <el-option label="订单号" value="order_no"></el-option>
                </el-select>
                <el-button @click="search" slot="append" icon="el-icon-search"></el-button>
            </el-input>

            <el-table v-loading="listLoading" :data="list" border style="margin-top:20px;width: 100%">
                <el-table-column prop="id" label="ID" align="center" width="80"></el-table-column>
                <el-table-column label="用户" width="230" >
                    <template slot-scope="scope">
                        <div style="display: flex;align-items: center">
                            <com-image :src="scope.row.avatar_url" style="flex-shrink: 0"></com-image>
                            <div style="margin-left:10px;">
                                <div>{{scope.row.nickname}}（ID:{{scope.row.user_id}}）</div>
                                <div>电话：{{scope.row.mobile}}</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="price" label="获得收益" width="120" align="center"></el-table-column>
                <el-table-column prop="total_income" label="累计收益" width="120" align="center"></el-table-column>
                <el-table-column label="商品信息" width="350" >
                    <template slot-scope="scope">
                        <div style="display: flex;align-items: center">
                            <com-image :src="scope.row.cover_pic" style="flex-shrink: 0"></com-image>
                            <div style="margin-left:10px;">
                                <div>{{scope.row.goods_name}}</div>
                                <div>零售价：{{scope.row.goods_price}}元</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column label="订单信息" width="350" align="center">
                    <template slot-scope="scope">
                        <el-table :show-header="false" :data="cOrderInfo(scope.row)" border size="small">
                            <el-table-column prop="name" width="100" align="right"></el-table-column>
                            <el-table-column prop="value"></el-table-column>
                        </el-table>
                    </template>
                </el-table-column>

                <el-table-column label="奖励信息" width="350" align="center">
                    <template slot-scope="scope">
                        <el-table :show-header="false" :data="cAwardInfo(scope.row)" border size="small">
                            <el-table-column prop="name" width="100" align="right"></el-table-column>
                            <el-table-column prop="value"></el-table-column>
                        </el-table>
                    </template>
                </el-table-column>

                <el-table-column label="日期" width="200" fixed="right">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}
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
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: '0',
                list: [],
                keyword: '',
                kw_type: 'nickname',
                listLoading: false,
                page: 1,
                pageCount: 0
            };
        },
        computed:{
            cAwardInfo(item){
                return function(item){
                    let i, infos = [
                        {name: '奖励类型', value: item.award_info.award_type == 0 ? '按比例' : '按固定值'}
                    ], symbol = item.award_info.award_type == 0 ? '%' : '元';
                    for(i=0; i < item.award_info.award_rules.length; i++){
                        infos.push({name: item.award_info.award_rules[i].name, value: (item.award_info.award_rules[i].value + symbol)});
                    }
                    return infos;
                }
            },
            cOrderInfo(item){
                return function(item){
                    return [
                        {name: '订单号', value: item.order_no},
                        {name: '商品数量', value: item.num + '件'},
                        {name: '总利润', value: item.award_info.profit_price + '元'}
                    ];
                }
            }
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
                        r: 'plugin/perform_distribution/mall/award/order',
                        page: self.page,
                        kw_type: this.kw_type,
                        keyword: this.keyword,
                        status: this.activeName
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            }
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