<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
        position: relative;
        z-index: 1;
    }

    .input-item {
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

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .table-body .el-form-item {
        margin-bottom: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>规则列表</span>
        </div>

        <div class="table-body">
            <div style="display:flex;">
                <div style="width:150px;">
                    <el-select @change="searchList" size="small" v-model="search.item_type" placeholder="请选择" style="margin-right:8px;">
                        <el-option v-for="item in item_type_options"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                        </el-option>
                    </el-select>
                </div>
                <div style="width:300px;">
                    <el-input @keyup.enter.native="searchList" size="small" placeholder="请输入关键词搜索" v-model="search.keyword" clearable @clear='searchList'>
                        <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                    </el-input>
                </div>
                <div style="flex-grow: 1;text-align:right;">
                    <el-button @click="addRule()" type="primary" size="small" style="padding: 9px 15px !important;">添加规则</el-button>
                </div>
            </div>

            <el-table v-loading="listLoading" :row-class-name="tableRowClassName" :data="list"  border style="margin-top:10px;width: 100%">
                <el-table-column prop="id" label="ID" width="90"></el-table-column>
                <el-table-column label="类型" width="180">
                    <template slot-scope="scope">
                        <div v-if="scope.row.item_type == 'checkout'">门店二维码收款</div>
                        <div v-if="scope.row.item_type == 'goods'">商品消费分佣</div>
                        <div v-if="scope.row.item_type == 'store'">门店直推分佣</div>
                        <div v-if="scope.row.item_type == 'hotel'">酒店直推分佣</div>
                        <div v-if="scope.row.item_type == 'hotel_3r'">酒店消费分佣</div>
                        <div v-if="scope.row.item_type == 'addcredit'">话费直推分佣</div>
                        <div v-if="scope.row.item_type == 'addcredit_3r'">话费消费分佣</div>
                        <div v-if="scope.row.item_type == 'giftpacks'">大礼包消费分佣</div>
                        <div v-if="scope.row.item_type == 'oil_3r'">加油消费分佣</div>
                    </template>
                </el-table-column>
                <el-table-column label="对象名称">
                    <template slot-scope="scope">
                        <div v-if="scope.row.item_id == '0'">
                            <div v-if="scope.row.item_type == 'checkout'">全部店铺</div>
                            <div v-if="scope.row.item_type == 'goods'">全部商品</div>
                            <div v-if="scope.row.item_type == 'hotel'">全部酒店</div>
                            <div v-if="scope.row.item_type == 'store'">全部门店</div>
                            <div v-if="scope.row.item_type == 'hotel_3r'">全部酒店上级（3r）分佣</div>
                            <div v-if="scope.row.item_type == 'addcredit'">全部话费直推</div>
                            <div v-if="scope.row.item_type == 'addcredit_3r'">全部话费消费分佣</div>
                            <div v-if="scope.row.item_type == 'giftpacks'">全部大礼包消费分佣</div>
                            <div v-if="scope.row.item_type == 'oil_3r'">全部加油消费分佣</div>
                        </div>
                        <div v-else>
                            <div v-if="scope.row.item_type == 'checkout' || scope.row.item_type == 'store'">
                                店铺：{{scope.row.store_name}}[ID:{{scope.row.item_id}}]
                            </div>
                            <div v-if="scope.row.item_type == 'goods'">
                                商品：{{scope.row.goods_name}}[ID:{{scope.row.item_id}}]
                            </div>
                            <div v-if="scope.row.item_type == 'hotel_3r' || scope.row.item_type == 'hotel'">
                                酒店：{{scope.row.hotel_name}}[ID:{{scope.row.item_id}}]
                            </div>
                            <div v-if="scope.row.item_type == 'addcredit' || scope.row.item_type == 'addcredit_3r'">
                                话费：{{scope.row.addcredit_name}}[ID:{{scope.row.item_id}}]
                            </div>
                            <div v-if="scope.row.item_type == 'giftpacks'">
                                大礼包：{{scope.row.giftpacks_name}}[ID:{{scope.row.item_id}}]
                            </div>
                            <div v-if="scope.row.item_type == 'oil_3r'">
                                加油：{{scope.row.oil_name}}[ID:{{scope.row.item_id}}]
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="日期" width="120">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="150">
                    <template slot-scope="scope">
                        <el-button type="text" size="mini" circle style="margin-left: 10px;margin-top: 10px"
                                   @click.native="editRule(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>

                        <el-button circle size="mini" type="text" @click="deleteRule(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>

            </el-table>

            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                        @current-change="pagination"
                        background
                        layout="prev, pager, next, jumper"
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
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                search:{
                    order_type: '',
                    keyword: ''
                },
                item_type_options: [
                    {
                        value: '',
                        label: '全部'
                    },
                    {
                        value: 'goods',
                        label: '商品'
                    },
                    {
                        value: 'checkout',
                        label: '二维码收款'
                    },
                    {
                        value: 'store',
                        label: 'VIP代理商'
                    },
                    {
                        value: 'hotel',
                        label: '酒店直推'
                    },
                    {
                        value: 'hotel_3r',
                        label: '酒店消费'
                    },
                    {
                        value: 'addcredit',
                        label: '话费直推'
                    },
                    {
                        value: 'addcredit_3r',
                        label: '话费消费'
                    },
                    {
                        value: 'giftpacks',
                        label: '大礼包消费'
                    },
                    {
                        value: 'oil_3r',
                        label: '加油消费'
                    },
                ]
            };
        },
        methods: {
            addRule(){
                navigateTo({
                    r: 'plugin/commission/mall/rules/edit',
                });
            },
            editRule(id){
                navigateTo({
                    r: 'plugin/commission/mall/rules/edit',
                    page: this.page,
                    id: id
                });
            },
            deleteRule(id){
                var self = this;
                this.$confirm('你确定要删除规则？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: "plugin/commission/mall/rules/delete"
                        },
                        method: 'post',
                        data: {
                            id: id
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            self.getList();
                            self.$message.success(e.data.msg);
                        } else {
                            self.listLoading = false;
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        self.listLoading = false;
                        self.$message.error("request fail");
                    });
                });
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            searchList() {
                this.page = 1;
                this.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/commission/mall/rules/index',
                        page: self.page,
                        keyword: self.search.keyword,
                        item_type: self.search.item_type
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            handleClick(tab, event) {

            },
            tableRowClassName({row, rowIndex}) {
                if (row.apply_all_item == 1) {
                    return 'main-row';
                } else{
                    return 'normal-row';
                }
                return '';
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>

<style>
    .el-table .main-row {
        background: #f5f8ff;
    }
</style>
