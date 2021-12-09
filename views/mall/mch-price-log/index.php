<div id="app" v-cloak>
    <el-tabs v-model="activeName" @tab-click="handleClick">

        <el-tab-pane label="待确认" name="unconfirmed"></el-tab-pane>
        <el-tab-pane label="已确认" name="confirmed"></el-tab-pane>
        <el-tab-pane label="已结算" name="success"></el-tab-pane>
        <el-tab-pane label="已取消" name="canceled"></el-tab-pane>

        <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;position: relative;">
            <div class="table-body">

                <div style="display: flex;">
                    <div>
                        <div style="float: left;margin-top: 5px">下单时间：</div>
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
                            <el-select slot="prepend" v-model="search.kw_type" placeholder="请选择" size="small"
                                       style="width:120px;">
                                <el-option v-for="item in item_type_options"
                                           :key="item.value"
                                           :label="item.label"
                                           :value="item.value">
                                </el-option>
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
                    <el-table-column label="订单信息" width="300">
                        <template slot-scope="scope">
                            <div v-if="scope.row.source_type == 'giftpacks_order_item'">
                                <div>订单编号：{{scope.row.order_item_info.giftpackOrder.order_sn}}</div>
                                <div>订单金额：<span style="color:darkred">¥{{scope.row.order_item_info.giftpackOrder.order_price}}</span>
                                </div>
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
                    <el-table-column label="礼包信息" width="260">
                        <template slot-scope="scope">
                            <div flex="cross:center">
                                <com-image :src="scope.row.order_item_info.giftpackOrder.giftpacks.cover_pic"></com-image>
                                <div style="margin-left: 10px;">
                                    <div>
                                        {{scope.row.order_item_info.giftpackOrder.giftpacks.title}}
                                    </div>
                                    <div>
                                        ID：{{scope.row.order_item_info.giftpackOrder.giftpacks.id}}
                                        <el-button type="primary" size="mini" @click="getGiftDetails(scope.row)">详 情
                                        </el-button>
                                    </div>
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
                                <div><span style="color:darkgreen">¥{{scope.row.order_item_info.giftpackOrder.pay_price}}</span>
                                </div>
                                <div><span style="color:darkred">{{scope.row.order_item_info.giftpackOrder.integral_deduction_price}}红包 </span>
                                </div>
                            </div>
                            <div v-if="scope.row.source_type == 'order_detail'">
                                <div><span style="color:darkgreen">¥{{scope.row.order.total_price}}</span></div>
                                <div><span style="color:darkred">{{scope.row.order.integral_deduction_price}}红包</span>
                                </div>
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
                    <el-table-column label="备注" prop="remark" v-if="activeName != 'unconfirmed'">
                        <template slot-scope="scope">
                            <div>{{scope.row.remark}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" v-if="activeName == 'unconfirmed'">
                        <template slot-scope="scope">
                            <el-button @click="doConfirm('confirmed', scope.row.id)" type="text" size="mini" circle>
                                <el-tooltip class="item" effect="dark" content="确认" placement="top">
                                    <img src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button @click="doConfirm('canceled', scope.row.id)" type="text" size="mini" circle>
                                <el-tooltip class="item" effect="dark" content="取消" placement="top">
                                    <img src="statics/img/mall/nopass.png" alt="">
                                </el-tooltip>
                            </el-button>
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

    <el-dialog width="50%" title="礼包详情" :visible.sync="dialogVisible"  @close="close">
        <div v-loading="loading">
            <table class="grid-i" style="width:100%;">
                <tr class="c4">
                    <td class="label">标题：</td>
                    <td>
                        <div flex="cross:center">
                            <com-image :src="details.cover_pic"></com-image>
                            <div style="margin-left: 10px;">
                                <div>
                                    {{ details.title }}
                                </div>
                            </div>
                        </div>

                    </td>
                    <td class="label">到期时间：</td>
                    <td>{{ details.expired_at|dateTimeFormat('Y-m-d H:i:s') }}</td>
                </tr>
                <tr class="c2">
                    <td class="label">描述：</td>
                    <td colspan="3">{{ details.descript }}</td>
                </tr>
                <tr class="c4">
                    <td class="label">价格：</td>
                    <td>{{ details.price }}</td>
                    <td class="label">利润：</td>
                    <td>{{ details.profit_price }}</td>
                </tr>
                <tr class="c4">
                    <td class="label">库存：</td>
                    <td>{{ details.max_stock }}</td>
                    <td class="label">限购：</td>
                    <td>{{ details.purchase_limits_num > 0 ? details.purchase_limits_num : '不限购' }}</td>
                </tr>
                <tr class="c4">
                    <td class="label">支付模式：</td>
                    <td>
                        <span
                              v-if="details.allow_currency == 'money'">
                            现金
                        </span>
                        <span v-else>
                            <span
                                  v-if="details.allow_currency == 'integral'">
                                红包
                            </span>
                        </span>
                    </td>
                    <td class="label"><span v-if="details.integral_enable == 0" style="color: red">(未开启)</span>返红包：</td>
                    <td>{{ details.integral_give_num }}</td>
                </tr>
                <tr class="c2" >
                    <td class="label"><span v-if="details.score_enable == 0" style="color: red">(未开启)</span>返积分：</td>
                    <td colspan="3">
                        <div v-if="details.score_enable > 0">
                            <span v-if="details.score_give_settings.is_permanent>0" class="spacing">
                                永久积分
                            </span>
                            <span v-else class="spacing">
                                限时积分
                            </span>
                            <span class="spacing">数量：{{ details.score_give_settings.integral_num }}</span>
                            <span class="spacing">
                                时长：{{ details.score_give_settings.period }}
                                <span v-if="details.score_give_settings.period_unit=='month'">月</span>
                            </span>
                            <span class="spacing">有效期：{{ details.score_give_settings.expire }}天</span>
                        </div>
                    </td>
                </tr>
                <tr class="c2">
                    <td class="label"><span v-if="details.group_enable == 0" style="color: red">(未开启)</span>拼团：</td>
                    <td colspan="3">
                        <div v-if="details.group_enable > 0">
                            <span class="spacing">
                                拼团价：{{ details.group_price }}
                            </span>
                            <span class="spacing">
                                拼团人数：{{ details.group_need_num }}
                            </span>
                            <span class="spacing">有效期：{{ details.group_expire_time/60/60 }}时</span>
                        </div>
                    </td>
                </tr>
                <tr class="c4">
                    <td class="label">总结算价：</td>
                    <td>{{ gift_goods_info.summary_price_calculation }}</td>
                    <td class="label">商品数量：</td>
                    <td>{{ gift_goods_info.goods_count}}</td>
                </tr>
            </table>
        </div>
    </el-dialog>

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
                item_type_options: [
                    {
                        value: 'store_name',
                        label: '商家昵称'
                    },
                    {
                        value: 'mch_id',
                        label: '商家ID'
                    },
                    {
                        value: 'mch_mobile',
                        label: '商家手机号'
                    },
                    {
                        value: 'order_no',
                        label: '订单编号'
                    },
                    {
                        value: 'pay_user_id',
                        label: '支付用户ID'
                    },
                ],
                dialogVisible: false,
                details: {},
                gift_goods_info:{
                    goods_count: 0,
                    summary_price_calculation: 0,
                },
            };
        },
        mounted() {
            this.loadData(this.activeName);
        },
        methods: {
            getGiftDetails(row) {
                this.details = row.order_item_info.giftpackOrder.giftpacks;
                this.dialogVisible = true;
                this.gift_goods_info.goods_count = row.goods_count;
                this.gift_goods_info.summary_price_calculation = row.summary_price_calculation;
            },
            doConfirm(act, id) {
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
                                    this.$message.success(e.data.msg);
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

    .grid-i th{padding:5px 0px 5px 0px;}
    .grid-i th,.grid-i td{text-align:left;}
    .grid-i td{padding:10px 10px;border:1px solid #ddd;border-bottom:none;}
    .grid-i tr:last-child td{border-bottom:1px solid #ddd;}
    .grid-i .label{border-left:none;font-weight:bold;padding:6px 6px 6px 0px;border-right:none;text-align:right;background:#f1f1f1;}
    .grid-i td:first-child{border-left:1px solid #ddd;}
    .grid-i .c4 td{width:30%}
    .grid-i .c2 td{width:80%}
    .grid-i .label{width:20% !important;}

    .spacing {
        margin-left: 20px;
    }

</style>