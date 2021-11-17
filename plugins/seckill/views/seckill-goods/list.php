<?php
Yii::$app->loadComponentView('com-rich-text');
?>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>秒杀商品列表</span>
        </div>

        <div class="table-body">
            <div class="input-item">
                <el-input style="width: 300px" v-model="searchData.keyword" placeholder="请输入搜索内容" clearable
                          @clear="clearSearch"
                          @change="search"
                          @input="triggeredChange">
                    <el-select style="width: 100px" slot="prepend" v-model="searchData.keyword_type">
                        <el-option v-for="item in selectList" :key="item.value"
                                   :label="item.name"
                                   :value="item.value">
                        </el-option>
                    </el-select>
                </el-input>
            </div>
            <div style="float: right">
                <el-button type="primary"  @click="addGoods()">添加商品</el-button>
            </div>

            <el-table :data="list"  border style="width: 100%" v-loading="loading" >
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column label="秒杀专题（归属）" width="300">
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px;">
                                <com-image mode="aspectFill" :src="scope.row.seckill.pic_url"></com-image>
                            </div>
                            <div flex="cross:top cross:center">
                                <div flex="dir:left">
                                    <el-tooltip class="item" effect="dark" placement="top">
                                        <template slot="content">
                                            <div style="width: 320px;">{{scope.row.seckill.name}}</div>
                                        </template>
                                        <com-ellipsis :line="2">{{scope.row.seckill.name}}</com-ellipsis>
                                    </el-tooltip>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column label="商品" width="500">
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <el-popover placement="top-start"  trigger="hover">
                                <el-image :src="scope.row.cover_pic" style="width:350px;"></el-image>
                                <el-image slot="reference" style="width: 60px; height: 60px"
                                          :src="scope.row.cover_pic"
                                          :preview-src-list="[scope.row.cover_pic]">
                                </el-image>
                            </el-popover>
                            <div flex="cross:top cross:center">
                                <div flex="dir:left">
                                    <el-tooltip class="item" effect="dark" placement="top">
                                        <template slot="content">
                                            <div style="width: 320px;">{{scope.row.name}}（ID：{{scope.row.goods_id}}）</div>
                                        </template>
                                        <com-ellipsis :line="2">{{scope.row.name}}（ID：{{scope.row.goods_id}}）</com-ellipsis>
                                    </el-tooltip>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="buy_limit" width="140" label="限单">
                    <template slot-scope="scope">
                        <div style="width: 320px;" v-if="scope.row.buy_limit == 0">不限制</div>
                        <div style="width: 320px;" v-else >{{scope.row.buy_limit}}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="virtual_seckill_num"  width="140" label="虚拟秒杀量"></el-table-column>
                <el-table-column prop="real_stock"  width="140" label="真实库存"></el-table-column>
                <el-table-column prop="virtual_stock"   width="140" label="虚拟库存"></el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="editGoodsAttr(scope.row, false)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="deleteIt(scope.row.id)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div style="text-align: center;margin-top:20px;">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        @current-change="pageChange"
                        v-if="pagination">
                </el-pagination>
            </div>
        </div>

        <el-dialog width="70%" title="添加商品" :visible.sync="goodsDialogVisible">
            <template>
                <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入商品ID或名称搜索"
                          v-model="mallGoodsSearch" clearable
                          @clear="toSearch">
                    <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                </el-input>
                <el-table v-loading="mallGoodsLoading" :data="mallGoods" border style="width: 100%;margin-top: 10px" @selection-change="handleSelectionChange">
                    <el-table-column width="75" >
                        <template slot-scope="scope">
                            <el-button @click="mallGoodsImport(scope.row)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="选择" placement="top">
                                    <img src="statics/img/mall/plus.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                    <el-table-column prop="id" width="90" label="ID"></el-table-column>
                    <el-table-column label="商品" >
                        <template slot-scope="scope">
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.goodsWarehouse.cover_pic"></com-image>
                                </div>
                                <div>
                                    <div flex="dir:left">
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{scope.row.goodsWarehouse.name}}</div>
                                            </template>
                                            <com-ellipsis :line="1">{{scope.row.goodsWarehouse.name}}</com-ellipsis>
                                        </el-tooltip>
                                    </div>
                                </div>
                            </div>

                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_stock" width="90" label="库存"></el-table-column>
                    <el-table-column prop="goodsWarehouse.original_price" width="90" label="售价"></el-table-column>
                    <el-table-column prop="forehead_score" width="150" label="可抵扣积分"></el-table-column>
                    <el-table-column label="状态" width="100">
                        <template slot-scope="scope">
                            <el-tag size="small" type="success" v-if="scope.row.status">销售中</el-tag>
                            <el-tag size="small" type="warning" v-else>下架中</el-tag>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
            <div style="display: flex;justify-content: space-between;margin-top:20px;">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        :page-size="mallGoodsPagination.pageSize"
                        :total="mallGoodsPagination.total_count"
                        style="float:right;margin:15px"
                        @current-change="mallGoodsPagina"
                        v-if="mallGoodsPagination && mallGoodsPagination.pageSize > 1">
                </el-pagination>
            </div>
        </el-dialog>

        <el-dialog width="70%" title="商品设置" :visible.sync="editGoodsAttrParams.dialogVisible" v-loading="editGoodsAttrParams.btnLoading">
            <el-alert title="说明：每人限购： 0 代表不限制购买" type="info" :closable="false" style="margin-bottom: 20px;color: red"></el-alert>
            <div v-if="openTheme">
                秒杀专题：<el-select v-model="specialKeyword"  filterable @change="specialChange"
                                reserve-keyword
                                placeholder="请输入关键词"
                                :remote-method="remoteMethod"
                                :loading="loading">
                    <el-option
                            v-for="item in specialList"
                            :key="item.id"
                            :label="item.name"
                            :value="item.id">
                    </el-option>
                </el-select>
            </div>
            <template v-if="editGoodsAttrParams.formData">
                <el-table :data="editGoodsAttrParams.formData" border style="margin-top:20px;width: 100%">
                    <el-table-column prop="id" label="ID" width="90"></el-table-column>
                    <el-table-column label="商品" >
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
                    <el-table-column prop="original_price" width="150" label="原价">
                        <template slot-scope="scope">
                            <el-input disabled v-model="scope.row.original_price"></el-input>
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_stock" width="150" label="商品原库存">
                        <template slot-scope="scope">
                            <el-input disabled v-model="scope.row.goods_stock"></el-input>
                        </template>
                    </el-table-column>
                    <el-table-column prop="buy_limit" width="150" label="每人限购">
                        <template slot-scope="scope">
                            <el-input type="number" v-model="scope.row.buy_limit"></el-input>
                        </template>
                    </el-table-column>

                    <el-table-column prop="virtual_seckill_num" width="150" label="虚拟秒杀量">
                        <template slot-scope="scope">
                            <el-input v-model="scope.row.virtual_seckill_num"></el-input>
                        </template>
                    </el-table-column>
                    <el-table-column prop="real_stock" width="150" label="真实库存">
                        <template slot-scope="scope">
                            <el-input v-model="scope.row.real_stock" @change="compare(scope.row.real_stock,scope.row.goods_stock)"></el-input>
                        </template>
                    </el-table-column>
                    <el-table-column width="150" label="虚拟库存">
                        <template slot-scope="scope">
                            <el-input v-model="scope.row.virtual_stock"></el-input>
                        </template>
                    </el-table-column>
                </el-table>

                <el-card class="box-card" style="margin-top:20px;">
                    <el-alert title="说明：积分抵扣+秒杀价格和购物券抵扣只能二选一" type="info" :closable="false" style="margin-bottom: 20px;color: red"></el-alert>
                    <el-table :data="editGoodsAttrParams.formData[0].seckillGoodsPrice" height="400" border style="margin-top:20px;width: 100%">
                        <el-table-column prop="attr_id" label="规格ID" width="100"></el-table-column>
                        <el-table-column property="spec_name" label="商品SKU" width="250">
                            <template slot-scope="scope">
                                <span v-for="(item,index) in scope.row.spec_name" :key="index">{{item.key}}：{{item.val}} </span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="attr_price" width="180" label="原价">
                            <template slot-scope="scope">
                                <el-input disabled v-model="scope.row.attr_price"></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column prop="seckill_price" width="180" label="秒杀价格（运费）">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.seckill_price"></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column prop="score_deduction_price" width="180" label="积分抵扣金额">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.score_deduction_price"></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column prop="shopping_voucher_deduction_price" width="180" label="购物券抵扣金额">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.shopping_voucher_deduction_price"></el-input>
                            </template>
                        </el-table-column>

                        <el-table-column label="操作" >
                            <template slot-scope="scope">
                                <el-button @click="skuDelete(scope.row.id, scope.$index, editGoodsAttrParams.formData[0].seckillGoodsPrice)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </template>
                        </el-table-column>

                    </el-table>
                </el-card>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="editGoodsAttrParams.dialogVisible = false">取 消</el-button>
                    <el-button type="primary" @click="singleEditConfirm">确 定</el-button>
                </div>
            </template>

        </el-dialog>

    </el-card>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                goodsDialogVisible: false,
                activeName: 'first',
                searchData: {
                    keyword: '',
                    keyword_type: '',
                },
                date: '',
                list: [],
                pagination: null,
                loading: false,
                batchSetForm: {
                    category:[],
                    dialogVisible: false,
                    categorys: [],
                    loading: false,
                    selections: [],
                    singleSetSel: 'freight_price',
                    singleSetValue: 0,
                    singleEditGoods: null
                },
                editGoods:{
                    dialogVisible: false,
                    btnLoading: false,
                    formData:{
                        name:"",
                        ali_product_info:{
                            info:{
                                description:"",
                                image:{
                                    images:[]
                                }
                            }
                        }
                    }
                },
                export_list: [],
                selectList: [
                    {value: 'goods_name', name: '商品名'},
                ],
                editGoodsAttrParams:{
                    dialogVisible: false,
                    btnLoading: false,
                    formData:'',
                },
                mallGoods:'',
                mallGoodsLoading:false,
                mallGoodsPagination:null,
                mallGoodsPage:1,
                mallGoodsSearch:'',
                openTheme:false,
                specialList:'',
                specialKeyword:'',
                specialId:0,
            };
        },
        methods: {
            compare(real, primary){
                let surplus;
                surplus = real-primary;
                if (surplus > 0) {
                    alert('真实库存不能大于原库存');
                    this.editGoodsAttrParams.formData[0].real_stock=0;
                }
            },
            triggeredChange (){
                if (this.searchData.keyword.length>0 && this.searchData.keyword_type.length<=0) {
                    alert('请选择搜索方式');
                    this.searchData.keyword='';
                }
            },
            clearSearch() {
                this.page = 1;
                this.searchData.keyword = '';
                this.searchData.keyword_type = '';
                this.getList();
            },
            handleSelectionChange(selection) {
                this.batchSetForm.selections = selection;
            },
            skuDelete(id, index, seckillGoodsPrice){
                if (seckillGoodsPrice.length == 1) {
                    alert('规格不能全部删除！');
                    return;
                }
                if (id==undefined) {
                    this.editGoodsAttrParams.formData[0].seckillGoodsPrice.splice(index, 1);
                    return;
                }
                this.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/seckill/mall/goods/seckill-goods/seckill-goods-sku-del'
                        },
                        method: 'post',
                        data: {id:id}
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.$message.success(e.data.msg);
                            this.editGoodsAttrParams.formData[0].seckillGoodsPrice.splice(index, 1)
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.$message.error(e.data.msg);
                    });
                }).catch(() => {

                });
            },
            deleteIt(id){
                this.deleteWithIds(id);
            },
            deleteWithIds(id){
                this.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/seckill/mall/goods/seckill-goods/seckill-goods-del'
                        },
                        method: 'post',
                        data: {id:id}
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.$message.success(e.data.msg);
                            this.getList();
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.$message.error(e.data.msg);
                    });
                }).catch(() => {

                });
            },
            editSave(goods, fn){
                request({
                    params: {
                        r: 'plugin/seckill/mall/goods/seckill-goods/seckill-goods-save'
                    },
                    method: 'post',
                    data: {
                        goods:goods
                    }
                }).then(e => {
                    if(typeof fn == "function"){
                        fn.call(this, e);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                });
            },
            singleEditConfirm(){
                let that = this;
                if (this.specialId == 0) {
                    alert('请选择秒杀专题');
                    return;
                }
                if (this.editGoodsAttrParams.formData[0].real_stock <= 0) {
                    alert('请填写真实库存');
                    return;
                }
                this.editGoodsAttrParams.btnLoading = true;
                this.editGoodsAttrParams.formData[0].seckill_id = this.specialId;
                this.editSave(this.editGoodsAttrParams.formData[0], function(e){
                    that.editGoodsAttrParams.btnLoading = false;
                    if (e.data.code == 0) {
                        that.$message.success(e.data.msg);
                        that.editGoodsAttrParams.dialogVisible=false;
                        that.getList();
                        that.specialKeyword ='';
                    } else {
                        that.$message.error(e.data.msg);
                    }
                });
            },
            pageChange(page){
                this.page = page;
                this.getList();
            },
            editGoodsAttr(row, openTheme){
                this.editGoodsAttrParams.dialogVisible = true;
                this.editGoodsAttrParams.formData = [row];
                this.openTheme = openTheme;
                this.specialId = row.seckill_id;
            },
            search() {
                this.page = 1;
                this.getList();
            },
            getList() {
                let that = this;
                let params = Object.assign(that.searchData, {
                    r: 'plugin/seckill/mall/goods/seckill-goods/list',
                    page: that.page,
                });
                request({
                    params
                }).then(e => {
                    if (e.data.code === 0) {
                        let {list, pagination} = e.data.data;
                        that.list = list;
                        that.pagination = pagination;
                    } else {
                        that.$message.error(e.data.msg);
                    }
                    that.loading = false;
                }).catch(e => {
                    that.loading = false;
                });
                that.loading = true;
            },
            addGoods() {
                let self = this;
                self.goodsDialogVisible=true;
                self.mallGoodsLoading=true;
                request({
                    params: {
                        r: 'plugin/seckill/mall/goods/seckill-goods/mall-goods',
                    },
                    method: 'post',
                    data:{
                        page:self.mallGoodsPage,
                        keyword:self.mallGoodsSearch
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        self.mallGoods = e.data.data.list;
                        self.mallGoodsPagination = e.data.data.pagination;
                        self.mallGoodsLoading=false;
                    } else {
                        self.$message.error(e.data.msg);
                        self.mallGoodsLoading=false;
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            mallGoodsPagina (page) {
                this.mallGoodsPage = page;
                this.addGoods();
            },
            toSearch(){
                this.mallGoodsPage = 1;
                this.addGoods();
            },
            mallGoodsImport(row){
                if (row.status == 0) {
                    alert('该商品已下架');
                    return;
                }
                let that = this;
                that.mallGoodsLoading = true;
                request({
                    params: {
                        r: 'plugin/seckill/mall/goods/seckill-goods/search-mall-goods-sku'
                    },
                    method: 'post',
                    data: {
                        goods_id:row.id
                    }
                }).then(e => {
                    if (e.data.code == 0) {
                        that.$message.success(e.data.msg);
                        that.editGoodsAttrParams.formData = e.data.data;
                        that.goodsDialogVisible = false;
                        that.editGoodsAttrParams.dialogVisible = true;
                        that.mallGoodsLoading = false;
                        that.openTheme=true;
                        that.getSpecialList();
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.$message.error(e.data.msg);
                });
            },
            getSpecialList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/seckill/mall/special/special',
                        page: 1,
                        keyword: self.specialKeyword,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.specialList = e.data.data.list;
                }).catch(e => {
                    console.log(e);
                });
            },
            specialChange(e){
                this.specialId = e;
            }
        },
        directives:{
            focus:{
                inserted:function(el){
                    el.querySelector('input').focus();
                }
            }
        },
        mounted: function() {
            this.getList();
        }
    });
</script>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin: 0 0 20px 0;
    }

    .el-dialog {
        border-radius: 15px;
    }
</style>