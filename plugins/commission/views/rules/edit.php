<?php
echo $this->render('../components/com-commission-rule-edit');
?>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/commission/mall/rules/index'})">规则列表</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>编辑分佣规则</el-breadcrumb-item>
            </el-breadcrumb>
        </div>

        <div class="form_box">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-card v-loading="cardLoading" style="margin-top: 10px" shadow="never">
                    <el-col>
                        <el-form-item label="对象类型" prop="item_type">
                            <el-radio-group v-model="ruleForm.item_type">
                                <el-radio :label="'goods'">商品</el-radio>
                                <el-radio :label="'checkout'">门店</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <el-form-item v-if="ruleForm.item_type != ''" :label="ruleForm.item_type == 'goods' ? '全部商品' : '全部门店'" prop="apply_all_item">
                            <el-switch v-model="ruleForm.apply_all_item"
                                       active-text="是"
                                       inactive-text="否">
                            </el-switch>
                        </el-form-item>

                        <!-- 选择一个商品或门店 -->
                        <template v-if="!ruleForm.apply_all_item">
                            <el-form-item :label="ruleForm.item_type == 'goods' ? '选择商品' : '选择门店'" prop="item_id">

                                <div v-if="ruleForm.item_id > 0" flex="box:first" style="margin-bottom:5px;width:350px;padding:10px 10px;border:1px solid #ddd;">
                                    <div style="padding-right: 10px;">
                                        <com-image mode="aspectFill" :src="ruleForm.item_type == 'goods' ? ChooseGoods.goods_pic : ChooseStore.store_pic"></com-image>
                                    </div>
                                    <div flex="cross:top cross:center">
                                        <div style="display:block;">{{ruleForm.item_type == 'goods' ? ChooseGoods.goods_name : ChooseStore.store_name}}</div>
                                    </div>
                                </div>

                                <el-button v-if="ruleForm.item_type == 'goods'" @click="chooseGoodsDialog" icon="el-icon-edit" type="primary" size="small">设置</el-button>
                                <el-button v-else @click="chooseStoreDialog" icon="el-icon-edit" type="primary" size="small">设置</el-button>
                            </el-form-item>
                        </template>

                        <el-form-item label="设置规则">
                            <com-commission-rule-edit @update="updateCommissionRule" :ctype="commissionType" :chains="commissionRuleChains"></com-commission-rule-edit>
                        </el-form-item>

                        <el-form-item label="">
                            <el-button @click="saveCommissionRule" type="primary" size="medium">保存规则</el-button>
                        </el-form-item>

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

    <!-- 选择门店对话框 -->
    <el-dialog title="设置门店" :visible.sync="ChooseStore.dialog_visible" width="30%">
        <el-input @keyup.enter.native="loadStoreList"
                  size="small" placeholder="搜索门店"
                  v-model="ChooseStore.search.keyword"
                  clearable @clear="toStoreSearch"
                  style="width:300px;">
            <el-button slot="append" icon="el-icon-search" @click="toStoreSearch"></el-button>
        </el-input>
        <el-table v-loading="ChooseStore.loadding" :data="ChooseStore.list">
            <el-table-column label="" width="100">
                <template slot-scope="scope">
                    <el-link @click="confirmChooseStore(scope.row)" icon="el-icon-edit" type="primary">选择</el-link>
                </template>
            </el-table-column>
            <el-table-column property="id" label="门店ID" width="90"></el-table-column>
            <el-table-column label="门店名称">
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
                    v-if="ChooseStore.pagination.page_count > 1"
                    style="display: inline-block;"
                    background :page-size="ChooseStore.pagination.pageSize"
                    @current-change="storePageChange"
                    layout="prev, pager, next" :current-page="ChooseStore.pagination.current_page"
                    :total="ChooseStore.pagination.total_count">
            </el-pagination>
        </div>

    </el-dialog>


</div>
<script>

    const app = new Vue({
        el: '#app',
        data() {
            var validateItemId = (rule, value, callback) => {
                if(!this.ruleForm.apply_all_item){
                    if(isNaN(value) || value <= 0){
                        callback(new Error(this.ruleForm.item_type == 'goods' ? '请选择商品' : '请选择门店'));
                    }
                }else{
                    return callback();
                }
            };

            return {
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
                ChooseStore: {
                    store_name: '',
                    store_pic:'',
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
                    item_id: 0
                },
                rules: {
                    item_type: [
                        {message: '请选择对象类型', trigger: 'change', required: true}
                    ]
                },
                cardLoading: false,
                commissionType: 1,
                commissionRuleChains: []
            }
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
        },
        methods: {

            updateCommissionRule(data){
                if(data['type'] != null && typeof data.type != "undefined"){
                    this.commissionType = data.type;
                }
                if(data['chains'] != null && typeof data.chains == "object"){
                    this.commissionRuleChains = data.chains;
                }
            },

            getDetail(){
                var self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/commission/mall/rules/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        var data = e.data.data;
                        self.ruleForm.item_type      = data.rule.item_type;
                        self.ruleForm.apply_all_item = data.rule.apply_all_item == 1 ? true : false;
                        self.ruleForm.item_id        = data.rule.item_id;
                        self.commissionType          = data.rule.commission_type;
                        self.commissionRuleChains    = data.chains;
                        self.ChooseGoods.goods_name  = data.rule.goods_name;
                        self.ChooseGoods.goods_pic   = data.rule.goods_pic;
                        self.ChooseStore.store_name  = data.rule.store_name;
                        self.ChooseStore.store_pic   = data.rule.store_pic;
                    }
                }).catch(e => {
                })
            },

            //保存规则
            saveCommissionRule(){
                this.$refs['ruleForm'].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.loading = true;
                        request({
                            params: {
                                r: 'plugin/commission/mall/rules/edit'
                            },
                            method: 'post',
                            data: {
                                item_type: self.ruleForm.item_type,
                                apply_all_item: self.ruleForm.apply_all_item ? 1 : 0,
                                item_id: self.ruleForm.item_id,
                                commission_type: self.commissionType,
                                commission_chains_json: JSON.stringify(self.commissionRuleChains)
                            }
                        }).then(e => {
                            self.loading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.loading = false;
                        });
                    }
                });
            },



            //--------------选择商品-----------------------------
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
            },

            //--------------选择门店-----------------------------
            confirmChooseStore(row){
                this.ruleForm.item_id = row.id;
                this.ChooseStore.store_name = row.name;
                this.ChooseStore.store_pic = row.cover_pic;
                this.ChooseStore.dialog_visible = false;
            },
            chooseStoreDialog(){
                this.ChooseStore.dialog_visible = true;
                this.loadStoreList();
            },
            StorePageChange(page){
                this.ChooseStore.search.page = page;
                this.loadStoreList();
            },
            toStoreSearch(){
                this.ChooseStore.search.page = 1;
                this.loadStoreList();
            },
            loadStoreList(){
                let self = this;
                self.ChooseStore.loadding = true;
                request({
                    params: {
                        r: "plugin/commission/mall/rules/search-store"
                    },
                    method: 'post',
                    data: {
                        page: self.ChooseStore.search.page,
                        keyword: self.ChooseStore.search.keyword
                    }
                }).then(e => {
                    self.ChooseStore.loadding = false;
                    if (e.data.code === 0) {
                        self.ChooseStore.list = e.data.data.list;
                        self.ChooseStore.pagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.ChooseStore.loadding = false;
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