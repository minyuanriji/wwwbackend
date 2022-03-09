<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" >
            <span>订单管理</span>
        </div>
        <div class="table-body">
            <el-tabs v-model="activeName" type="card">
                <el-tab-pane label="订单管理" name="first">

                    <el-table :data="list" border v-loading="loading" size="small" style="margin: 15px 0;">
                        <el-table-column prop="goods_id" width="90" label="商品ID" align="center"></el-table-column>
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
                        <el-table-column width="120" label="退款状态" align="center">
                            <template slot-scope="scope">
                                {{refundStatus(scope.row)}}
                            </template>
                        </el-table-column>
                        <el-table-column width="120" label="订单状态" align="center">
                            <template slot-scope="scope">
                                {{orderStatus(scope.row)}}
                            </template>
                        </el-table-column>
                        <el-table-column label="订单信息" width="350">
                            <template slot-scope="scope">
                                <el-table size="small" :show-header="false" :data="orderInfos(scope.row)"  border style="width: 100%">
                                    <el-table-column prop="label" width="120" align="right"></el-table-column>
                                    <el-table-column prop="content"></el-table-column>
                                </el-table>
                            </template>
                        </el-table-column>
                        <el-table-column prop="num" width="70" label="数量" align="center"></el-table-column>
                        <el-table-column prop="total_price" width="110" label="实付款" align="center"></el-table-column>

                        <el-table-column label="操作">
                            <template slot-scope="scope">
                                <el-button @click="edit(scope.row)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                        <img src="statics/img/mall/edit.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button @click="detail(scope.row)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="查看" placement="top">
                                        <img src="statics/img/mall/detail.png" alt="">
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

            },
            activeName: 'first',
        },
        mounted() {
            this.loadData();
        },
        computed: {
            orderStatus(row){
                return function(row){
                    let str = '';
                    switch(row.order_status){
                        case "0":
                            str = '未支付';
                            break;
                        case "1":
                            str = '待发货';
                            break;
                        case "2":
                            str = '待收货';
                            break;
                        case "3":
                            str = '待评价';
                            break;
                        case "4":
                            str = '取消待处理';
                            break;
                        case "5":
                            str = '已取消/已关闭';
                            break;
                        case "6":
                            str = '售后申请中';
                            break;
                        case "7":
                            str = '售后完成';
                            break;
                        case "8":
                            str = '已完成';
                            break;
                    }
                    return str;
                }
            },
            refundStatus(row){
                return function(row){
                    let str = '无';
                    if(row.is_refund == 1){
                        str = '已退款';
                    }else if(row.refund_status == 10){
                        str = '售后中,待处理';
                    }else if(row.refund_status == 11){
                        str = '售后中,仅退款,已同意';
                    }else if(row.refund_status == 12){
                        str = '售后中,退款退货,已同意';
                    }else if(row.refund_status == 20){
                        str = '完成售后,已退款';
                    }else if(row.refund_status == 21){
                        str = '完成售后,已拒绝';
                    }
                    return str;
                }
            },
            orderInfos(row){
                return function (row){
                    let infos = [];
                    infos.push({label: "日期", content: row.created_at});
                    infos.push({label: "订单号", content: row.order_no});
                    infos.push({label: "用户昵称", content: row.nickname + "("+row.user_id+")"});
                    infos.push({label: "购物券抵扣金额", content: row.shopping_voucher_decode_price});
                    infos.push({label: "购物券使用数量", content: row.shopping_voucher_num});
                    infos.push({label: "收件人", content: row.name});
                    infos.push({label: "联系手机", content: row.mobile});
                    infos.push({label: "收件地址", content: row.address});
                    return infos;
                }
            }
        },
        methods: {
            edit(row){
                var path = window.location.origin + window.location.pathname + '?r=mall%2Forder%2Fdetail&order_id=' + row.order_id;
                window.open(path, '_blank');
            },

            detail(row){
                window.open(row.url, '_blank');
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
                    r: 'plugin/taobao/mall/order/index'
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