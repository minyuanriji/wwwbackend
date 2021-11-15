<?php
echo $this->render("com-refund-agree");
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="申请中" name="apply"></el-tab-pane>
            <el-tab-pane label="已同意" name="agree"></el-tab-pane>
            <el-tab-pane label="已拒绝" name="refused"></el-tab-pane>
            <el-tab-pane label="已完成" name="finished"></el-tab-pane>
            <div class="table-body">
                <span style="height: 32px;">申请时间：</span>
                <el-date-picker
                        class="item-box"
                        size="small"
                        @change="changeTime"
                        v-model="search.time"
                        type="datetimerange"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期">
                </el-date-picker>

                <div class="input-item" style="width:400px;margin-bottom:20px;display:inline-block;">
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="订单ID/订单编号/商品名称/昵称/手机号" v-model="search.keyword" clearable @clear="toSearch">
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>

                <el-table :data="list" size="small" border v-loading="loading" style="margin-bottom: 15px">

                    <el-table-column prop="id" label="ID" width="70"></el-table-column>

                    <el-table-column label="订单信息">
                        <template slot-scope="scope">
                            <div>订单日期：{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s') }}</div>
                            <div>订单编号：{{scope.row.order_no}}</div>
                            <div>商品费用：<span>￥{{scope.row.total_goods_price}}</span>（购物券抵：{{scope.row.shopping_voucher_decode_price}}）</div>
                            <div>运费：<span>￥{{scope.row.express_original_price}}</span>（购物券抵：{{scope.row.shopping_voucher_express_decode_price}}）</div>
                            <div>使用现金：{{scope.row.total_pay_price}}</div>
                            <div>使用购物券：{{scope.row.total_shopping_voucher_num}}（抵扣{{scope.row.total_shopping_voucher_price}}元）</div>
                            <div>支付用户：{{scope.row.nickname}}(ID:{{scope.row.user_id}})</div>
                            <div>支付日期：{{ scope.row.pay_at|dateTimeFormat('Y-m-d H:i:s') }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="商品" width="350">
                        <template slot-scope="scope">
                            <div style="display:flex;align-items:center ">
                                <div style="" >
                                    <com-image :src="scope.row.cover_url"></com-image>
                                </div>
                                <div style="margin-left:10px;">
                                    <div style="">{{scope.row.goods_name }}（ID：{{scope.row.goods_id}}）</div>
                                    <div>规格：{{ scope.row.sku_labels[0]}}</div>
                                </div>
                                <div style=";text-align:center;width:100px">X <span style="">{{scope.row.num}}</span></div>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="状态" width="100" align="center">
                        <template slot-scope="scope">
                            <div v-if="scope.row.refund_status=='finished' && scope.row.is_refund == 1" style="color: red">已退款</div>
                            <div v-if="scope.row.refund_status=='apply'" style="color: red">申请中</div>
                            <div v-if="scope.row.refund_status=='agree'" style="color: darkblue">同意退款</div>
                            <div v-if="scope.row.refund_status=='refused'" style="color: red">拒绝退款</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="退款原因" width="200" align="center">
                        <template slot-scope="scope">
                            <span style="color:gray">{{scope.row.refund_data.description}}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="退款信息" width="130">
                        <template slot-scope="scope">
                            <div>现金：<span >{{scope.row.total_pay_price}}</span></div>
                            <div>购物券：<span >{{scope.row.shopping_voucher_use_num}}</span></div>
                        </template>
                    </el-table-column>

                    <el-table-column label="操作" width="230">
                        <template slot-scope="scope">

                            <el-button @click="agree(scope.row)" type="text"  v-if="activeName == 'apply'" size="mini" circle >
                                <el-tooltip class="item" effect="dark" content="同意" placement="top">
                                    <img src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>

                            <el-button @click="apply(scope.row, 'refused')" type="text"  v-if="activeName == 'apply'" size="mini" circle>
                                <el-tooltip class="item" effect="dark" content="拒绝" placement="top">
                                    <img src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>

                            <el-button @click="doPayment(scope.row)" type="text" v-if="activeName == 'agree'" size="mini" circle>
                                <el-tooltip class="item" effect="dark" content="打款" placement="top">
                                    <img src="statics/img/mall/pay.png" alt="">
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
            </div>
        </el-tabs>
    </el-card>

    <com-refund-agree :order-detail-id="agreeEdit.orderDetailId" @close="close"></com-refund-agree>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                passengersData: [],
                search: {
                    keyword: '',
                    status: 'apply',
                    start_time: '',
                    end_time: '',
                    time: null,
                },
                loading: false,
                activeName: 'apply',
                list: [],
                pagination: null,
                exportList: [],
                agreeEdit: {
                    orderDetailId: 0
                }
            };
        },
        mounted() {
            this.loadData();
        },
        methods: {
            //同意退款操作
            agree(row){
                let that = this;
                this.apply(row, "agree", function (rs){
                    that.agreeEdit.orderDetailId = row.id;
                });
            },
            //打款操作展示详情
            doPayment(row){
                this.agreeEdit.orderDetailId = parseInt(row.id);
            },
            // 日期搜索
            changeTime() {
                if (this.search.time) {
                    this.search.start_time = this.search.time[0];
                    this.search.end_time = this.search.time[1];
                } else {
                    this.search.start_time = null;
                    this.search.end_time = null;
                }
                this.loadData();
            },
            loadData(status = 'apply', page = 1) {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/alibaba/mall/order/refund-list',
                        status: status,
                        page: page,
                        keyword: this.search.keyword,
                        start_time: this.search.start_time,
                        end_time: this.search.end_time,
                    },
                    method: 'get'
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
                this.page = 1;
                this.loadData(this.activeName);
            },

            showPassengers(row){
                this.passengersData = row.passengers;
                console.log(this.passengersData);
            },

            pageChange(page) {
                this.loadData(this.activeName, page);
            },

            handleClick(tab, event) {
                this.loadData(this.activeName)
            },

            toDetail(id){
                navigateTo({
                    r: 'plugin/baopin/mall/store/goods-list',
                    id: id
                })
            },

            apply(row, act, fn) {
                this.$prompt('请输入备注', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'plugin/alibaba/mall/order/apply',
                                },
                                method: 'post',
                                data: {
                                    id: row.id,
                                    act: act,
                                    content: instance.inputValue,
                                }
                            }).then(e => {
                                instance.confirmButtonLoading = false;
                                if (e.data.code === 0) {
                                    this.agreeEdit.agreeBackData = e.data.data;
                                    this.loadData(this.activeName);
                                    done();
                                    if(typeof fn == "function"){
                                        fn.call(this, e.data);
                                    }
                                } else {
                                    instance.confirmButtonText = '确定';
                                    this.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                done();
                                instance.confirmButtonLoading = false;
                            });
                        }else{
                            done();
                        }
                    }
                });
            },

            close(){
                this.agreeEdit.orderDetailId = 0;
            }
        }
    })
</script>
<style>
    .item {
        margin-top: 10px;
        margin-right: 40px;
    }
    .com-order-user {
        margin-left: 30px;
    }

    .com-order-head .com-order-time {
        color: #909399;
    }


    .table-body {
        padding: 20px;
        background-color: #fff;
    }

</style>