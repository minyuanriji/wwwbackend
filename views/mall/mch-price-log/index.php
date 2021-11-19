<div id="app" v-cloak>
    <el-tabs v-model="activeName" @tab-click="handleClick" >

        <el-tab-pane label="待确认" name="unconfirmed"></el-tab-pane>
        <el-tab-pane label="已确认" name="confirmed"></el-tab-pane>
        <el-tab-pane label="已结算" name="success"></el-tab-pane>
        <el-tab-pane label="已取消" name="canceled"></el-tab-pane>


        <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
            <div class="table-body">

                <div style="display: flex;">
                    <div style="width: 30%">
                        <div style="float: left;margin-top: 5px">打款时间：</div>
                        <el-date-picker size="small" v-model="date" type="datetimerange"
                                        style="float: left"
                                        value-format="yyyy-MM-dd HH:mm:ss"
                                        range-separator="至" start-placeholder="开始日期"
                                        @change="selectDateTime"
                                        end-placeholder="结束日期">
                        </el-date-picker>
                    </div>
                    <div style="margin-left:10px;">
                        <el-input @keyup.enter.native="goSearch" size="small" placeholder="请输入"
                                  v-model="search.keyword" clearable @clear="goSearch">
                            <el-select slot="prepend" v-model="search.kw_type" placeholder="请选择" size="small" style="width:120px;">
                                <el-option label="商家昵称" value="store_name"></el-option>
                                <el-option label="商家ID" value="mch_id"></el-option>
                                <el-option label="商家手机号" value="mch_mobile"></el-option>
                            </el-select>
                            <el-button slot="append" icon="el-icon-search" @click="goSearch"></el-button>
                        </el-input>
                    </div>
                </div>

                <el-table :data="list" size="small" border v-loading="loading" style="margin: 15px 0">
                    <el-table-column prop="id" label="ID" width="70" align="center"></el-table-column>
                    <el-table-column label="类型" width="80" align="center">
                        <template slot-scope="scope">
                            <span v-if="scope.row.source_type == 'giftpacks_order_item'">本地生活</span>
                            <span v-if="scope.row.source_type == 'order_detail'">商品订单</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="订单信息"  width="300">
                        <template slot-scope="scope">
                            <div v-if="scope.row.source_type == 'giftpacks_order_item'">
                                <div>订单编号：{{scope.row.order_item_info.giftpackOrder.order_sn}}</div>
                                <div>订单金额：<span style="color:darkred">¥{{scope.row.order_item_info.giftpackOrder.order_price}}</span></div>
                            </div>
                            <div v-if="scope.row.source_type == 'order_detail'">
                                <div>订单编号：{{scope.row.order.order_no}}</div>
                                <div>订单金额：
                                    <span style="color:darkred">¥{{scope.row.order.total_goods_original_price}}</span>（商品）
                                    +
                                    <span style="color:darkred">¥{{scope.row.order.express_original_price}}</span>（运费）
                                </div>
                            </div>
                            <div>下单时间：{{scope.row.created_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                            <div>更新时间：{{scope.row.updated_at|dateTimeFormat('Y-m-d H:i:s')}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="支付用户" width="200">
                        <template slot-scope="scope">
                            <div flex="cross:center">
                                <com-image :src="scope.row.user.avatar_url"></com-image>
                                <div style="margin-left: 10px;">
                                    <div style="width: 100px;overflow:hidden;text-overflow: ellipsis;">
                                        {{scope.row.user.nickname}}
                                    </div>
                                    <div>ID：{{scope.row.user.id}}</div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="商家信息" width="200">
                        <template slot-scope="scope">
                            <div flex="cross:center">
                                <com-image :src="scope.row.cover_url"></com-image>
                                <div style="margin-left: 10px;">
                                    <div style="width: 100px;overflow:hidden;text-overflow: ellipsis;">
                                        {{scope.row.store_name}}
                                    </div>
                                    <div>ID：{{scope.row.mch_id}}</div>
                                </div>
                             </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="支付信息" width="130" align="center">
                        <template slot-scope="scope">
                            <div v-if="scope.row.source_type == 'giftpacks_order_item'">
                                <div><span style="color:darkgreen">¥{{scope.row.order_item_info.giftpackOrder.pay_price}}</span></div>
                                <div><span style="color:darkred">{{scope.row.order_item_info.giftpackOrder.integral_deduction_price}}红包 </span></div>
                            </div>
                            <div v-if="scope.row.source_type == 'order_detail'">
                                <div><span style="color:darkgreen">¥{{scope.row.order.total_price}}</span></div>
                                <div><span style="color:darkred">{{scope.row.order.integral_deduction_price}}红包</span></div>
                                <div>{{scope.row.order.shopping_voucher_decode_price}}购物券</div>
                                <div>{{scope.row.order.score_deduction_price}}积分</div>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column label="结算金额" width="130" align="center">
                        <template slot-scope="scope">
                            <span style="color:darkred;">¥{{scope.row.price}}</span>
                            <div>（服务费{{scope.row.transfer_rate}}%）</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="备注" prop="remark" width="230"></el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <div v-if="scope.row.status == 'unconfirmed'">
                                <el-button @click="doConfirm('confirmed', scope.row.id)" type="text" size="mini" circle >
                                    <el-tooltip class="item" effect="dark" content="确认" placement="top">
                                        <img src="statics/img/mall/pass.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button @click="doConfirm('canceled', scope.row.id)" type="text" size="mini" circle>
                                    <el-tooltip class="item" effect="dark" content="取消" placement="top">
                                        <img src="statics/img/mall/nopass.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </div>
                            <div v-else>-</div>
                        </template>
                    </el-table-column>
                </el-table>

                <div style="text-align: center">
                    <el-pagination
                            v-if="list.length > 0"
                            style="margin-top:20px;"
                            background :page-size="pagination.pageSize"
                            @current-change="pageChange"
                            layout="prev, pager, next" :current-page="pagination.current_page"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </el-card>
    </el-tabs>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                date: '',
                search: {
                    kw_type: 'store_name',
                    keyword: '',
                    status: '',
                    start_date: '',
                    end_at: ''
                },
                loading: false,
                activeName: 'unconfirmed',
                list: [],
                page: 1,
                pagination: null,
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                },
                searchData: {
                    keyword: '',
                    start_date: '',
                    end_date: '',
                    status: '',
                    address: null,
                },
            };
        },
        mounted() {
            this.loadData(this.activeName);
        },
        methods: {
            doConfirm(act, id){
                this.$prompt('请输入备注', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '执行中...';
                            request({
                                params: {
                                    r: 'mall/mch-price-log/apply',
                                },
                                method: 'post',
                                data: {
                                    id: id,
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
                        } else {
                            done();
                        }
                    }
                });
            },
            goSearch() {
                if (this.date == null) {
                    this.date = ''
                }
                this.page = 1;
                this.loadData(this.activeName)
            },
            selectDateTime(e) {
                if (e != null) {
                    this.search.start_date = e[0];
                    this.search.end_date = e[1];
                } else {
                    this.search.start_date = '';
                    this.search.end_date = '';
                }
                this.goSearch();
            },
            loadData(status = -1) {
                this.loading = true;
                this.pagination = null;
                request({
                    params: {
                        r: 'mall/mch-price-log/index',
                        status: status,
                        page: this.page,
                        start_date: this.search.start_date,
                        end_date: this.search.end_date,
                        kw_type: this.search.kw_type,
                        keyword: this.search.keyword
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
            pageChange(page) {
                this.page = page;
                this.loadData(this.activeName);
            },
            handleClick(tab, event) {
                this.page = 1;
                this.loadData(this.activeName)
            }
        }
    })
</script>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .export-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
    }

    #assets {
        font-size: 18px;
        color: #1ed0ff;
        margin-left: 10px;
    }

    .table-body {
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }
</style>