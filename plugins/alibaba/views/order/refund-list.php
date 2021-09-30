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
                            <div class="com-order-head" flex="cross:center">
                                <div class="com-order-time">创建时间：{{ scope.row.created_at|dateTimeFormat('Y-m-d H:i:s') }}</div>
                                <div class="com-order-user">
                                    <span class="com-order-time">订单号：</span>{{scope.row.order_no}}
                                    <span class="com-order-pay-user" style="margin-left: 20px">支付用户：</span>{{scope.row.nickname}}(ID:{{scope.row.user_id}})
                                </div>
                            </div>
                            <div>
                                <com-image style="float: left;margin-right: 10px;height: 80px;width: 80px"
                                           :src="scope.row.cover_url"></com-image>
                                <div style="margin: 10px 0">{{ scope.row.goods_name }}（ID：{{scope.row.goods_id}}）</div>
                                小计：<span style="color: red">{{ scope.row.unit_price }}</span>
                                <span style="margin-left: 30px">数量：×
                                    <span style="font-size: 16px;color: green">{{scope.row.num}}</span>
                                </span>
                                <div style="margin-top: 10px">
                                    规格：{{ scope.row.sku_labels[0] }}
                                </div>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="支付状态" width="130">
                        <template slot-scope="scope">
                            <div v-if="scope.row.refund_status=='finished' && scope.row.is_refund == 1" style="color: red">已退款</div>
                            <div v-if="scope.row.refund_status=='apply'" style="color: red">申请中</div>
                            <div v-if="scope.row.refund_status=='agree'" style="color: red">同意退款</div>
                            <div v-if="scope.row.refund_status=='refused'" style="color: red">拒绝退款</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="退款金额" width="130">
                        <template slot-scope="scope">
                            <div v-if="scope.row.is_pay==1">
                                <div v-if="scope.row.pay_type==1">现金：{{scope.row.total_price ?? 0}}</div>
                                <div v-if="scope.row.pay_type==2">货到付款</div>
                                <div v-if="scope.row.pay_type==3">购物券：
                                    <span >
                                    {{scope.row.shopping_voucher_decode_price ?? 0}}
                                </span>
                                </div>
                                <div>运费：<span>{{ scope.row.shopping_voucher_express_use_num }}</span></div>
                                <div>总计：<span>{{ scope.row.total_shopping_voucher_price }}</span></div>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="支付时间" width="180">
                        <template slot-scope="scope">
                            {{ scope.row.pay_at|dateTimeFormat('Y-m-d H:i:s') }}
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

                            <el-button @click="apply(scope.row, 'paid')" type="text"  v-if="activeName == 'agree'" size="mini" circle>
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

    <com-refund-agree :visible="agreeEdit.dialogVisible" @close="agreeEdit.dialogVisible=false"></com-refund-agree>
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
                    dialogVisible: false
                }
            };
        },
        mounted() {
            this.loadData();
        },
        methods: {
            //同意退款操作
            agree(row){
                this.agreeEdit.dialogVisible = true;
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

            apply(row, act) {
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
                                    this.loadData(this.activeName);
                                    done();
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