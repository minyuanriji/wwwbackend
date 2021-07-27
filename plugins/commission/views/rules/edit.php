<?php
echo $this->render('../components/com-commission-rule-edit');
echo $this->render('../components/com-commission-store-rule-edit');
echo $this->render('../components/com-commission-hotel-rule-edit');
echo $this->render('../components/com-commission-hotel_3r-rule-edit');
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
                                <el-radio :disabled="ruleForm.item_type == 'goods' || radioDisabled == false ? false : true" :label="'goods'">商品</el-radio>
                                <el-radio :disabled="ruleForm.item_type == 'checkout' || radioDisabled == false ? false : true" :label="'checkout'">二维码收款</el-radio>
                                <el-radio :disabled="ruleForm.item_type == 'store' || radioDisabled == false ? false : true" :label="'store'">门店</el-radio>
                                <el-radio :disabled="ruleForm.item_type == 'hotel' || radioDisabled == false ? false : true" :label="'hotel'">酒店直推分佣</el-radio>
                                <el-radio :disabled="ruleForm.item_type == 'hotel_3r' || radioDisabled == false ? false : true" :label="'hotel_3r'">酒店上级（3r）分佣</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <el-form-item v-if="ruleForm.item_type != ''" :label="'全部'" prop="apply_all_item">
                            <el-switch v-model="ruleForm.apply_all_item"
                                       active-text="是"
                                       inactive-text="否">
                            </el-switch>
                        </el-form-item>

                        <!-- 选择一个商品/门店/酒店 -->
                        <template v-if="!ruleForm.apply_all_item">

                            <el-form-item v-if="ruleForm.item_type == 'goods'" :label="'选择商品'" prop="item_id">
                                <div v-if="ruleForm.item_id > 0" flex="box:first" style="margin-bottom:5px;width:350px;padding:10px 10px;border:1px solid #ddd;">
                                    <div style="padding-right: 10px;">
                                        <com-image mode="aspectFill" :src="ChooseGoods.goods_pic"></com-image>
                                    </div>
                                    <div flex="cross:top cross:center">
                                        <div style="display:block;">{{ChooseGoods.goods_name}}</div>
                                    </div>
                                </div>
                                <el-button @click="chooseGoodsDialog" icon="el-icon-edit" type="primary" size="small">设置</el-button>
                            </el-form-item>

                            <el-form-item v-if="ruleForm.item_type == 'store'"  :label="'选择门店'" prop="item_id">
                                <div v-if="ruleForm.item_id > 0" flex="box:first" style="margin-bottom:5px;width:350px;padding:10px 10px;border:1px solid #ddd;">
                                    <div style="padding-right: 10px;">
                                        <com-image mode="aspectFill" :src="ChooseStore.store_pic"></com-image>
                                    </div>
                                    <div flex="cross:top cross:center">
                                        <div style="display:block;">{{ChooseStore.store_name}}</div>
                                    </div>
                                </div>
                                <el-button @click="chooseStoreDialog" icon="el-icon-edit" type="primary" size="small">设置</el-button>
                            </el-form-item>

                            <el-form-item v-if="ruleForm.item_type == 'checkout'"  :label="'选择门店'" prop="item_id">
                                <div v-if="ruleForm.item_id > 0" flex="box:first" style="margin-bottom:5px;width:350px;padding:10px 10px;border:1px solid #ddd;">
                                    <div style="padding-right: 10px;">
                                        <com-image mode="aspectFill" :src="ChooseStore.store_pic"></com-image>
                                    </div>
                                    <div flex="cross:top cross:center">
                                        <div style="display:block;">{{ChooseStore.store_name}}</div>
                                    </div>
                                </div>
                                <el-button @click="chooseStoreDialog" icon="el-icon-edit" type="primary" size="small">设置</el-button>
                            </el-form-item>

                            <el-form-item v-if="ruleForm.item_type == 'hotel' || ruleForm.item_type == 'hotel_3r'" :label="'选择酒店'" prop="item_id">
                                <div v-if="ruleForm.item_id > 0" flex="box:first" style="margin-bottom:5px;width:350px;padding:10px 10px;border:1px solid #ddd;">
                                    <div style="padding-right: 10px;">
                                        <com-image mode="aspectFill" :src="ChooseHotel.hotel_pic"></com-image>
                                    </div>
                                    <div flex="cross:top cross:center">
                                        <div style="display:block;">{{ChooseHotel.hotel_name}}</div>
                                    </div>
                                </div>
                                <el-button @click="chooseHotelDialog" icon="el-icon-edit" type="primary" size="small">设置</el-button>
                            </el-form-item>

                        </template>

                        <el-form-item label="设置规则">

                            <com-commission-store-rule-edit v-if="ruleForm.item_type == 'store'" @number = "newNumber" @update="updateCommissionRule" :ctype="commissionType" :chains="commissionRuleChains" :commiss_value = "commissonValue"></com-commission-store-rule-edit>

                            <com-commission-rule-edit v-if="ruleForm.item_type == 'goods' || ruleForm.item_type == 'checkout'" @update="updateCommissionRule" :ctype="commissionType" :chains="commissionRuleChains"></com-commission-rule-edit>

                            <com-commission-hotel-rule-edit v-if="ruleForm.item_type == 'hotel'" @update="updateCommissionRule" :ctype="commissionType" :chains="commissionRuleChains"  @levelparam = "newLevelParam"  :commission_hotel_value = "commissionHotelValue"></com-commission-hotel-rule-edit>

                            <com-commission-hotel_3r-rule-edit v-if="ruleForm.item_type == 'hotel_3r'"  @update="updateCommissionRule" :ctype="commissionType" :chains="commissionRuleChains"></com-commission-hotel_3r-rule-edit>

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

    <!-- 选择酒店对话框 -->
    <el-dialog title="设置酒店" :visible.sync="ChooseHotel.dialog_visible" width="30%">
        <el-input @keyup.enter.native="loadHotelList"
                  size="small" placeholder="搜索酒店"
                  v-model="ChooseHotel.search.keyword"
                  clearable @clear="toHotelSearch"
                  style="width:300px;">
            <el-button slot="append" icon="el-icon-search" @click="toHotelSearch"></el-button>
        </el-input>
        <el-table v-loading="ChooseHotel.loadding" :data="ChooseHotel.list">
            <el-table-column label="" width="100">
                <template slot-scope="scope">
                    <el-link @click="confirmChooseHotel(scope.row)" icon="el-icon-edit" type="primary">选择</el-link>
                </template>
            </el-table-column>
            <el-table-column property="id" label="酒店ID" width="90"></el-table-column>
            <el-table-column label="酒店名称">
                <template slot-scope="scope">
                    <div flex="box:first">
                        <div style="padding-right: 10px;">
                            <com-image mode="aspectFill" :src="scope.row.thumb_url"></com-image>
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
                ChooseHotel: {
                    hotel_name: '',
                    hotel_pic:'',
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
                commissonValue: 0,
                commissionRuleChains: [],
                commissionHotelValue:[],
                radioDisabled:false,
            }
        },
        mounted: function () {
            if (getQuery('id')) {
                this.radioDisabled = true;
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
            newNumber (data) {
                if (data['value'] != null && typeof data.value != "undefined"){
                    this.commissonValue = data.value;
                }
            },
            newLevelParam (data) {
                if (data != null && typeof data != "undefined"){
                    this.commissionHotelValue = data;
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
                        self.commissonValue          = data.chains[0].commisson_value;
                        self.commissionRuleChains    = data.chains;
                        self.ChooseGoods.goods_name  = data.rule.goods_name;
                        self.ChooseGoods.goods_pic   = data.rule.goods_pic;
                        self.ChooseStore.store_name  = data.rule.store_name;
                        self.ChooseStore.store_pic   = data.rule.store_pic;
                        self.ChooseHotel.hotel_name  = data.rule.hotel_name;
                        self.ChooseHotel.hotel_pic   = data.rule.hotel_thumb_url;
                    }
                }).catch(e => {
                })
            },

            //保存规则
            saveCommissionRule(){
                this.$refs['ruleForm'].validate((valid) => {
                    let self = this;
                    if (self.ruleForm.item_type == 'store') {
                        self.commissionRuleChains = [{
                            "role_type":"user",
                            "level":1,
                            "commisson_value": self.commissonValue,
                            "unique_key":"user#all"
                        }]
                    } else if (self.ruleForm.item_type == 'hotel') {
                        for (let i=0;i<this.commissionHotelValue.length;i++) {
                            this.commissionHotelValue[i].level = 1;
                            if (this.commissionHotelValue[i].name == '普通会员') {
                                this.commissionHotelValue[i].role_type = 'user';
                                this.commissionHotelValue[i].unique_key = "user#all";
                            } else if (this.commissionHotelValue[i].name == '分公司') {
                                this.commissionHotelValue[i].role_type = 'branch_office';
                                this.commissionHotelValue[i].unique_key = "branch_office#all";
                            } else if (this.commissionHotelValue[i].name == '店主') {
                                this.commissionHotelValue[i].role_type = 'store';
                                this.commissionHotelValue[i].unique_key = "store#all";
                            } else if (this.commissionHotelValue[i].name == '合伙人') {
                                this.commissionHotelValue[i].role_type = 'partner';
                                this.commissionHotelValue[i].unique_key = "partner#all";
                            }
                        }
                        self.commissionRuleChains = this.commissionHotelValue;
                    }
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
            },

            //--------------选择酒店-----------------
            confirmChooseHotel(row){
                this.ruleForm.item_id = row.id;
                this.ChooseHotel.hotel_name = row.name;
                this.ChooseHotel.hotel_pic = row.thumb_url;
                this.ChooseHotel.dialog_visible = false;
            },
            chooseHotelDialog(){
                this.ChooseHotel.dialog_visible = true;
                this.loadHotelList();
            },
            HotelPageChange(page){
                this.ChooseHotel.search.page = page;
                this.loadHotelList();
            },
            toHotelSearch(){
                this.ChooseHotel.search.page = 1;
                this.loadHotelList();
            },
            loadHotelList(){
                let self = this;
                self.ChooseHotel.loadding = true;
                request({
                    params: {
                        r: "plugin/commission/mall/rules/search-hotel"
                    },
                    method: 'post',
                    data: {
                        page: self.ChooseHotel.search.page,
                        keyword: self.ChooseHotel.search.keyword
                    }
                }).then(e => {
                    self.ChooseHotel.loadding = false;
                    if (e.data.code === 0) {
                        self.ChooseHotel.list = e.data.data.list;
                        self.ChooseHotel.pagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.ChooseHotel.loadding = false;
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