

<div id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>编辑分佣规则</span>
            </div>
        </div>
        <div class="form_box">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-card style="margin-top: 10px" shadow="never">
                    <el-col>
                        <el-form-item label="分佣类型" prop="item_type">
                            <el-radio-group v-model="ruleForm.item_type">
                                <el-radio :label="'goods'">商品</el-radio>
                                <el-radio :label="'checkout'">二维码收款</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <!-- 商品类型 -->
                        <template v-if="ruleForm.item_type == 'goods'">
                            <el-form-item label="全部商品" prop="apply_all_item">
                                <el-switch v-model="ruleForm.apply_all_item"
                                        active-text="是"
                                        inactive-text="否">
                                </el-switch>
                            </el-form-item>

                            <!-- 如果不是全部商品 选择一个商品 -->
                            <template v-if="!ruleForm.apply_all_item">
                                <el-form-item label="选择商品" prop="item_id">

                                    <div v-if="ruleForm.item_type == 'goods' && ruleForm.item_id > 0" flex="box:first" style="margin-bottom:5px;width:350px;padding:10px 10px;border:1px solid #ddd;">
                                        <div style="padding-right: 10px;">
                                            <com-image mode="aspectFill" :src="ChooseGoods.goods_pic"></com-image>
                                        </div>
                                        <div flex="cross:top cross:center">
                                            <div style="display:block;">{{ChooseGoods.goods_name}}</div>
                                        </div>
                                    </div>

                                    <el-button @click="chooseGoodsDialog" icon="el-icon-edit" type="primary" size="small">设置</el-button>
                                </el-form-item>
                            </template>


                        </template>

                        <el-tabs type="border-card">
                            <el-tab-pane label="分公司">分公司</el-tab-pane>
                            <el-tab-pane label="合伙人">合伙人</el-tab-pane>
                            <el-tab-pane label="店主">店主</el-tab-pane>
                        </el-tabs>
                        
                    </el-col>

                </el-card>
            </el-form>
        </div>
    </el-card>


    <!-- 选择商品对话框 -->
    <el-dialog title="设置商品" :visible.sync="ChooseGoods.dialog_visible" width="30%">
        <el-input @keyup.enter.native="loadGoodsList"
                  size="small" placeholder="搜索商品"
                  v-model="ChooseGoods.search.keyword"
                  clearable @clear="toGoodsSearch"
                  style="width:300px;">
            <el-button slot="append" icon="el-icon-search" @click="toGoodsSearch"></el-button>
        </el-input>
        <el-table v-loading="ChooseGoods.loadding" :data="ChooseGoods.list">
            <el-table-column label="" width="100">
                <template slot-scope="scope">
                    <el-link @click="confirmChooseGoods(scope.row)" icon="el-icon-edit" type="primary">选择</el-link>
                </template>
            </el-table-column>
            <el-table-column property="id" label="商品ID" width="90"></el-table-column>
            <el-table-column label="商品名称">
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
        </el-table>

        <div style="text-align: right;margin-top:15px;">
            <el-pagination
                    v-if="ChooseGoods.pagination.page_count > 1"
                    style="display: inline-block;"
                    background :page-size="ChooseGoods.pagination.pageSize"
                    @current-change="goodsPageChange"
                    layout="prev, pager, next" :current-page="ChooseGoods.pagination.current_page"
                    :total="ChooseGoods.pagination.total_count">
            </el-pagination>
        </div>

    </el-dialog>

</div>
<script>

    const app = new Vue({
        el: '#app',
        data() {
            return {
                RuleSet:{
                    tabPosition:'left'
                },
                ChooseGoods: {
                    goods_name: '',
                    goods_pic:'',
                    dialog_visible: false,
                    loadding: false,
                    list: [],
                    search: {
                        keyword: '',
                        page: 1,
                    },
                    pagination: {
                        pageSize: 10,
                        current_page: 1,
                        total_count: 0,
                        page_count: 0
                    }
                },
                loading: false,
                ruleForm: {
                    item_type: '',
                    apply_all_item: false,
                    item_id: 0,
                },
                rules: {
                    ruleForm: [
                        {message: '请选择分佣类型', trigger: 'blur', required: true}
                    ]
                },
            }
        },
        mounted: function () {

        },
        methods: {
            confirmChooseGoods(row){
                this.ruleForm.item_id = row.id;
                this.ChooseGoods.goods_name = row.name;
                this.ChooseGoods.goods_pic = row.cover_pic;
                this.ChooseGoods.dialog_visible = false;
            },
            chooseGoodsDialog(){
                this.ChooseGoods.dialog_visible = true;
                this.loadGoodsList();
            },
            goodsPageChange(page){
                this.ChooseGoods.search.page = page;
                this.loadGoodsList();
            },
            toGoodsSearch(){
                this.ChooseGoods.search.page = 1;
                this.loadGoodsList();
            },
            loadGoodsList(){
                let self = this;
                self.ChooseGoods.loadding = true;
                request({
                    params: {
                        r: "plugin/commission/mall/rules/search-goods"
                    },
                    method: 'post',
                    data: {
                        page: self.ChooseGoods.search.page,
                        keyword: self.ChooseGoods.search.keyword
                    }
                }).then(e => {
                    self.ChooseGoods.loadding = false;
                    if (e.data.code === 0) {
                        self.ChooseGoods.list = e.data.data.list;
                        self.ChooseGoods.pagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.ChooseGoods.loadding = false;
                    self.$message.error("request fail");
                });
            }
        }
    });
</script>

<style>
    .form_box {
        background-color: #f3f3f3;
        padding: 0 0 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .el-input-group__append {
        background-color: #fff;
        color: #353535;
    }

    .commission-batch-set-box{
        border-top: 1px solid #E8EAEE;
        border-left: 1px solid #E8EAEE;
        border-right: 1px solid #E8EAEE;
        padding: 16px;
    }

    .commission-batch-set-box .batch {
        margin-left: -10px;
        margin-right: 20px;
    }

    .form_box .el-select .el-input {
        width: 130px;
    }

    .form_box .detail {
        width: 100%;
    }

    .form_box .detail .el-input-group__append {
        padding: 0 10px;
    }

    .form_box input::-webkit-outer-spin-button,
    .form_box input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }

    .form_box input[type="number"] {
        -moz-appearance: textfield;
    }

    .form_box .el-table .cell {
        text-align: center;
    }

    .form_box .el-table thead.is-group th {
        background: #ffffff;
    }
</style>