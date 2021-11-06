<div id="item_app" v-cloak>
   <el-dialog width="80%" :title="dailogTitle" :visible.sync="dialogFormVisible">


       <el-row>
           <el-col :span="12">
               <el-input @keyup.enter.native="loadData" size="small" placeholder="请输入搜索内容" v-model="search.keyword"
                         clearable @clear="toSearch" style="width:300px;">
                   <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
               </el-input>
           </el-col>
           <el-col :span="12" style="text-align:right;">
               <el-button @click="editItem(null)" type="primary" size="small" style="padding: 9px 15px !important;">添加商品</el-button>
           </el-col>
       </el-row>

       <el-table :data="list" border v-loading="loading" style="margin-top:20px;width: 100%">
            <el-table-column prop="id" label="编号ID"  width="90"></el-table-column>
            <el-table-column label="商品信息" width="150">
                <template slot-scope="scope">
                    <div flex="box:first">
                        <div style="padding-right: 10px;">
                            <com-image mode="aspectFill" :src="scope.row.cover_pic"></com-image>
                        </div>
                        <div flex="cross:top cross:center">
                            <div flex="dir:left">
                                <el-link @click="editItem(scope.row)" type="primary" :underline="true">
                                    <el-tooltip class="item" effect="dark" placement="top">
                                        <template slot="content">
                                            <div style="width: 320px;">{{scope.row.name}}</div>
                                        </template>
                                        <com-ellipsis :line="2">{{scope.row.name}}</com-ellipsis>
                                    </el-tooltip>
                                </el-link>
                            </div>
                        </div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="store_name" label="门店信息" width="150"></el-table-column>
            <el-table-column prop="goods_price" label="独立价格" width="90"></el-table-column>
            <el-table-column prop="item_price" label="结算价格" width="90"></el-table-column>
            <el-table-column label="有效期" width="150">
                <template slot-scope="scope">
                    <div v-if="scope.row.expired_at != ''"  style="color:darkred">{{scope.row.expired_at}}</div>
                    <div v-else style="color:green">永久有效</div>
                </template>
            </el-table-column>
           <el-table-column label="时限" width="150">
               <template slot-scope="scope">
                   <div v-if="scope.row.expired_at == '' || scope.row.limit_time <= 0" style="color:green">
                       不限制
                   </div>
                   <div v-else style="color:darkred">
                       {{scope.row.limit_time}}天
                   </div>
               </template>
           </el-table-column>
            <el-table-column prop="max_stock" label="库存" width="90">
                <template slot-scope="scope">
                    <div>{{scope.row.order_item_num}}/{{scope.row.max_stock}}</div>
                </template>
            </el-table-column>
            <el-table-column label="次数限制" width="100">
                <template slot-scope="scope">
                    <div v-if="scope.row.usable_times > 0">{{scope.row.usable_times}}次</div>
                    <div v-else style="color:green">不限次</div>
                </template>
            </el-table-column>
            <el-table-column label="操作">
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
            </el-table-column>
        </el-table>
       <div style="padding:30px 0;text-align:left;">
           <el-pagination
                   v-if="list.length > 0"
                   style="display: inline-block;float: right;"
                   background :page-size="pagination.pageSize"
                   @current-change="pageChange"
                   layout="prev, pager, next" :current-page="pagination.current_page"
                   :total="pagination.total_count">
           </el-pagination>
       </div>
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
            pageChange(page) {
                this.search.page = page;
                this.loadData();
            },
            show(pack_data){
                this.dailogTitle = pack_data.title;
                this.dialogFormVisible = true;
                this.pack_id = pack_data.id;
                this.loadData();
            },
            editItem(item_data){
                var self = this;
                itemEditApp.show(this.pack_id, item_data, function(){
                    self.loadData();
                });
            },
            deleteItem(item_data){
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.loading = true;
                    request({
                        params: {
                            r: 'plugin/giftpacks/mall/giftpacks/delete-item',
                        },
                        method: 'post',
                        data: {
                            id: item_data.id,
                        }
                    }).then(e => {
                        self.loading = false;
                        if (e.data.code === 0) {
                            self.loadData();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {

                });
            },
            loadData() {
                this.loading = true;
                let params = {
                    r: 'plugin/giftpacks/mall/giftpacks/item-list',
                    pack_id: this.pack_id
                };
                params = Object.assign(params, this.search);
                request({
                    params: params,
                    method: 'get',
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
                this.search.page = 1;
                this.loadData();
            }
        }
    });
</script>