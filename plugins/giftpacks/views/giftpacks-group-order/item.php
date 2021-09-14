<div id="item_app" v-cloak>
   <el-dialog width="80%" :title="dailogTitle" :visible.sync="dialogFormVisible">
       <el-table :data="list" border  v-loading="loading" style="margin-top:20px;width: 100%">
            <el-table-column prop="id" label="ID"  width="90"></el-table-column>
            <el-table-column prop="order_sn" label="订单编号"  width="200"></el-table-column>
            <el-table-column label="商品信息" width="300">
                <template slot-scope="scope">
                    <div flex="box:first">
                        <div style="padding-right: 10px;">
                            <com-image mode="aspectFill" :src="scope.row.cover_pic"></com-image>
                        </div>
                        {{scope.row.title}}
                    </div>
                </template>
            </el-table-column>
           <el-table-column label="用户信息" width="350">
               <template slot-scope="scope">
                   <div flex="box:first">
                       <div style="padding-right: 10px;">
                           <com-image mode="aspectFill" :src="scope.row.avatar_url"></com-image>
                       </div>
                       {{scope.row.nickname}}（ID：{{scope.row.user_id}}）
                   </div>
               </template>
           </el-table-column>
           <el-table-column label="身份" width="80">
                <template slot-scope="scope">
                    <div v-if="scope.row.head_user_id == scope.row.user_id">团长</div>
                    <div v-else>团员</div>
                </template>
            </el-table-column>
           <el-table-column label="支付状态" width="130">
               <template slot-scope="scope">
                   <div v-if="scope.row.pay_status=='refund'" style="color: red">已退款</div>
                   <div v-if="scope.row.pay_status=='refunding'" style="color: red">退款中</div>
                   <div v-if="scope.row.pay_status=='paid'" style="color: green">已支付</div>
                   <div v-if="scope.row.pay_status=='unpaid'">未支付</div>
               </template>
           </el-table-column>
           <el-table-column label="实付金额" width="130">
               <template slot-scope="scope">
                   <div>红包：{{scope.row.integral_deduction_price ?? 0}}</div>
                   <div>余额：{{scope.row.pay_price ?? 0}}</div>
               </template>
           </el-table-column>
           <el-table-column prop="pay_at" label="支付时间"  width="200"></el-table-column>
           <!--<el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button @click="editItem(scope.row)" type="text" circle size="mini">
                        <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                            <img src="statics/img/mall/edit.png" alt="">
                        </el-tooltip>
                    </el-button>
                    <el-button @click="deleteItem(scope.row, scope.$index)" type="text" circle size="mini">
                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                            <img src="statics/img/mall/del.png" alt="">
                        </el-tooltip>
                    </el-button>
                </template>
           </el-table-column>-->
        </el-table>
    </el-dialog>
</div>
<script>
    const itemApp = new Vue({
        el: '#item_app',
        data: {
            dailogTitle: '',
            dialogFormVisible: false,
            search: {
                keyword: '',
                page: 1,
                sort_prop: '',
                sort_type: '',
            },
            pack_id: 0,
            loading: false,
            list: [],
            pagination: null
        },
        methods: {
            show(id){
                this.id = id;
                this.loadData();
            },
            editItem(item_data){
                var self = this;
                itemEditApp.show(this.pack_id, item_data, function(){
                    self.loadData();
                });
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/giftpacks/mall/giftpacks-group-order/group-info',
                    id: this.id
                };
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    this.dialogFormVisible = true;
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
                this.search.page = 1;
                this.loadData();
            }
        }
    });
</script>