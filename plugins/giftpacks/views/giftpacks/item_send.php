<div id="item_send_app" v-cloak>
    <el-dialog width="80%" :title="dialogTitle" :visible.sync="dialogFormVisible">
        <el-card shadow="hover" class="box-card">
            <div style="display:flex;flex-direction:column">
                <div>商品名称：{{itemData.name}}</div>
                <div style="margin-top:10px;">商品价格：{{itemData.goods_price}}元</div>
                <div style="margin-top:10px;">结算价格：{{itemData.item_price}}元</div>

            </div>
        </el-card>

        <el-tabs v-model="status" @tab-click="switchTab" style="margin-top:30px;">
            <el-tab-pane label="待发放" name="wait_send"> </el-tab-pane>
            <el-tab-pane label="已发放的订单" name="has_send"></el-tab-pane>
        </el-tabs>

        <div style="color:gray;font-size:22px;" v-if="status == 'wait_send'">
            剩余库存：<span style="color:cornflowerblue">{{itemData.stock}}</span>
            <div >
                <el-button :loading="btnLoading" @click="sendIt" type="primary" style="margin-top:10px;">点击发放</el-button>
            </div>
        </div>

        <el-table :data="list" @selection-change="handleSelectionChange" size="small" border v-loading="loading" style="margin: 15px 0">

            <el-table-column type="selection" width="55"></el-table-column>

            <el-table-column prop="id" label="ID" width="100"></el-table-column>

            <el-table-column label="订单信息" width="350">
                <template slot-scope="scope">
                    <div>订单编号：{{scope.row.order_sn}}</div>
                    <div>下单日期：{{ scope.row.created_at|dateTimeFormat('Y-m-d H:i:s') }}</div>
                    <div>礼包：<div>{{ scope.row.title }}</div></div>
                </template>
            </el-table-column>

            <el-table-column label="用户信息" >
                <template slot-scope="scope">
                    <div>支付用户：{{scope.row.nickname}}({{scope.row.user_id}})</div>
                </template>
            </el-table-column>

            <el-table-column label="支付状态">
                <template slot-scope="scope">
                    <div v-if="scope.row.pay_status=='refund'" style="color: red">已退款</div>
                    <div v-if="scope.row.pay_status=='refunding'" style="color: red">退款中</div>
                    <div v-if="scope.row.pay_status=='paid'" style="color: green">已支付</div>
                    <div v-if="scope.row.pay_status=='unpaid'">未支付</div>
                </template>
            </el-table-column>

            <el-table-column label="支付信息">
                <template slot-scope="scope">
                    <div v-if="scope.row.pay_status != 'unpaid'">
                        <div>支付日期：{{scope.row.pay_at}}</div>
                        <div>使用红包：{{scope.row.integral_deduction_price ?? 0}}</div>
                        <div>使用现金：{{scope.row.pay_price ?? 0}}</div>
                    </div>
                    <div v-else>-</div>
                </template>
            </el-table-column>

            <el-table-column label="赠送" >
                <template slot-scope="scope">
                    <div>红 包：{{scope.row.integral_give_num}}</div>
                    <div>购物券：{{scope.row.got_shopping_voucher_num}}</div>
                    <div v-if="scope.row.score_enable == 1 && scope.row.pay_status == 'paid'">
                        <span v-if="scope.row.score_give_settings.is_permanent == 1">永久积分：{{scope.row.score_give_settings.integral_num}}</span>
                        <span v-else>
                            限时积分：{{scope.row.score_give_settings.integral_num}}<br/>
                            {{scope.row.score_give_settings.period}}月<br/>
                            {{scope.row.score_give_settings.expire}}（天）有效期<br/>
                        </span>
                    </div>
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
    </el-dialog>
</div>
<script>
    const itemSendApp = new Vue({
        el: '#item_send_app',
        data: {
            dialogTitle: '礼包商品发放',
            dialogFormVisible: false,
            itemData: {name: '', goods_price:0, item_price:0, stock: 0},
            status: 'wait_send',
            loading: false,
            btnLoading: false,
            list: [],
            page: 1,
            pagination: null,
            multipleSelection: []
        },
        methods: {
            show(item_data){
                this.dialogFormVisible = true;
                this.itemData = item_data;
                this.dialogTitle = "礼包商品[ID:"+item_data.id+"]发放";
                this.getOrders();
            },
            switchTab(){
                this.page = 1;
                this.getOrders();
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            pageChange(page) {
                this.page = page;
                this.getOrders();
            },
            sendIt(){
                if(this.multipleSelection.length <= 0){
                    this.$message.error("请选择要发放的订单");
                    return;
                }
                let i, orderIds = [];
                for(i=0; i < this.multipleSelection.length; i++){
                    orderIds.push(this.multipleSelection[i].id);
                }
                let self = this;
                self.$confirm('确定要发放礼包商品吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.btnLoading = true;
                    request({
                        params: {
                            r: 'plugin/giftpacks/mall/giftpacks-order/send-pack-item',
                        },
                        method: 'post',
                        data: {
                            pack_id: this.itemData.pack_id,
                            pack_item_id: this.itemData.id,
                            order_ids: orderIds
                        }
                    }).then(e => {
                        self.btnLoading = false;
                        if (e.data.code === 0) {
                            self.itemData.stock = e.data.data.stock;
                            this.getOrders();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.btnLoading = false;
                        console.log(e);
                    });
                }).catch(() => {

                });
            },
            getOrders(){
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/giftpacks/mall/giftpacks-order/index',
                        pack_id: this.itemData.pack_id,
                        pack_item_id: this.itemData.id,
                        status: this.status,
                        page: this.page
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.list = e.data.data.list;
                        console.log(this.list);
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            }
        }
    });
</script>