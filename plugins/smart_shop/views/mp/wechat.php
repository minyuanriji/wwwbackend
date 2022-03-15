
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>小程序管理</span>
                <div style="float: right;margin-top: -5px"></div>
            </div>
        </div>
        <div class="table-body">

            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="微信" name="wechat"></el-tab-pane>
                <el-tab-pane label="支付宝" name="alipay"></el-tab-pane>
            </el-tabs>

            <el-table size="small" v-loading="loading" :data="list" border style="margin-top:20px;width: 100%">
                <el-table-column prop="merchant_id" label="商户编号" width="90" align="center"></el-table-column>
                <el-table-column label="商户信息" width="200" >
                    <template slot-scope="scope">
                        <div style="display:flex;align-items: center;">
                            <com-image width="25" height="25" :src="scope.row.merchant_image"></com-image>
                            <div style="padding-left:10px;">
                                <div>{{scope.row.merchant_name}}</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="小程序信息" width="200" align="center"></el-table-column>
                <el-table-column label="版本" width="150" align="center"></el-table-column>
                <el-table-column label="状态" width="150" align="center"></el-table-column>
                <el-table-column label="备注" width="200" align="center"></el-table-column>
                <el-table-column label="操作" fixed="right">
                    <template slot-scope="scope">

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



    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                dialogVisible: false,
                list: [],
                pagination: null,
                loading: false,
                search: {
                    page: 1
                },
                activeName: 'wechat'
            };
        },
        computed: {

        },
        methods: {
            handleClick(tab, event) {
                if(tab.name == "wechat"){
                    location.href = "?r=plugin/smart_shop/mall/mp/wechat";
                }else{
                    location.href = "?r=plugin/smart_shop/mall/mp/alipay";
                }
            },
            searchGo(){
                this.page = 1;
                this.getList();
            },
            getList() {
                let self = this;
                self.loading = true;
                let params = {
                    r: 'plugin/smart_shop/mall/order/index'
                };
                params = Object.assign(params, this.search);
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        self.pagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.loading = false;
                });
            },
            pageChange(page) {
                this.search.page = page;
                this.getList();
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
<style>

</style>