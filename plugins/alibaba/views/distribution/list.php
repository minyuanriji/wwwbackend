<?php
echo $this->render("com-alibaba-goods");
Yii::$app->loadComponentView('com-rich-text');
?>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item>
                     <span style="color: #409EFF;cursor: pointer"
                           @click="$navigate({r:'plugin/alibaba/mall/app/list'})">应用管理</span>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item >社交电商</el-breadcrumb-item>
                    <el-breadcrumb-item >商品管理</el-breadcrumb-item>
                </el-breadcrumb>
            </div>
        </div>

        <div class="table-body">

            <div class="input-item">
                <el-input @keyup.enter.native="search" placeholder="请输入关键词搜索" v-model="searchData.keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <div style="float: right">
                <el-button type="primary"  @click="aliGoodsDialogVisible = true">添加商品</el-button>
            </div>

            <el-table  @sort-change="sortReload"  :data="list" border style="width: 100%" v-loading="loading" @selection-change="handleSelectionChange">
                <el-table-column align='center' type="selection" width="60"></el-table-column>
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column label="类目" width="200">
                    <template slot-scope="scope">
                        <el-tag size="small" :key="item.label" v-for="item in scope.row.categorys" style="margin-left:10px;">
                            {{item.name}}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="标题" >
                    <template slot-scope="scope">
                        <div style="padding-bottom:3px;">编号：{{scope.row.ali_offerId}}</div>
                        <div flex="box:first">
                            <div style="padding-right: 10px;">
                                <com-image mode="aspectFill" :src="scope.row.cover_url"></com-image>
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
                <el-table-column prop="price_rate"  sortable="custom"  width="110" label="零售比（%）"></el-table-column>
                <!--
                <el-table-column prop="origin_price_rate" width="110" label="划线比（%）"></el-table-column>
                -->
                <el-table-column prop="price" width="110" label="零售价"></el-table-column>
                <!--
                <el-table-column prop="origin_price" width="110" label="划线价"></el-table-column>
                -->
                <el-table-column width="90" label="分销价">
                    <template slot-scope="scope">
                        {{scope.row.ali_data_json.currentPrice}}
                    </template>
                </el-table-column>
                <el-table-column width="90" label="渠道价">
                    <template slot-scope="scope">
                        {{scope.row.ali_data_json.channelPrice}}
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="150" label="添加时间">
                    <template slot-scope="scope">
                        {{scope.row.created_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="150" label="更新时间">
                    <template slot-scope="scope">
                        {{scope.row.updated_at|dateTimeFormat('Y-m-d')}}
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button @click="editIt(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="deleteIt(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <div style="display: flex;justify-content: space-between;margin-top:20px;">
                <div style="margin: 7.5px 0px;" v-if="batchSetForm.selections.length > 0">
                    <el-button @click="openBatchSetDialog" type="primary">批量设置</el-button>
                    <el-button @click="batchDeleteIt" type="danger">批量删除</el-button>
                </div>
                <el-pagination
                        background
                        layout="prev, pager, next"
                        :page-size="pagination.pageSize"
                        :total="pagination.total_count"
                        style="float:right;margin:15px"
                        @current-change="pageChange"
                        v-if="pagination">
                </el-pagination>
            </div>

        </div>

        <com-alibaba-goods @close="aliGoodsDialogVisible = false"
                           @import="aliGoodsImport"
                           :visible="aliGoodsDialogVisible"></com-alibaba-goods>

        <el-dialog width="70%" title="批量设置" :visible.sync="batchSetForm.dialogVisible" :close-on-click-modal="false">

            <template v-if="batchSetForm.singleEditGoods == null">
                <div style="display: flex;">
                    <el-cascader v-model="batchSetForm.category"
                                 :options="batchSetForm.categorys"></el-cascader>
                    <el-button type="primary" @click="setCommonCategory" style="margin-left:10px;">统一类目</el-button>
                </div>
                <el-table v-loading="batchSetForm.loading" :data="batchSetForm.selections" border style="margin-top:20px;width: 100%">
                    <el-table-column prop="id" label="ID" width="90"></el-table-column>
                    <el-table-column label="标题" >
                        <template slot-scope="scope">
                            <div style="padding-bottom:3px;">编号：{{scope.row.ali_offerId}}</div>
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.cover_url"></com-image>
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
                    <el-table-column width="190" label="类目">
                        <template slot-scope="scope">
                            <el-cascader v-model="scope.row.ali_category_id" :options="batchSetForm.categorys"></el-cascader>
                        </template>
                    </el-table-column>
                    <el-table-column width="110" label="零售比（%）">
                        <template slot-scope="scope">
                            {{scope.row.price_rate}}
                        </template>
                    </el-table-column>
                    <el-table-column width="110" label="零售价">
                        <template slot-scope="scope">
                            {{scope.row.price}}
                        </template>
                    </el-table-column>
                    <!--
                   <el-table-column prop="freight_price_rate" width="100" label="运费比（%）"></el-table-column>
                   -->
                   <el-table-column width="110" label="平台运费">
                       <template slot-scope="scope">
                           {{scope.row.freight_price}}
                       </template>
                   </el-table-column>
                   <!--
                   <el-table-column prop="origin_price_rate" width="110" label="划线比（%）"></el-table-column>
                   <el-table-column prop="origin_price" width="110" label="划线价"></el-table-column>
                   -->
                    <el-table-column width="110" label="分销价">
                        <template slot-scope="scope">
                            {{scope.row.ali_data_json.currentPrice}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="ali_freight_price" width="100" label="1688运费"></el-table-column>
                    <el-table-column width="110" label="渠道价">
                        <template slot-scope="scope">
                            {{scope.row.ali_data_json.channelPrice}}
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="75" >
                        <template slot-scope="scope">

                            <el-button @click="batchSingleEdit(scope.row)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div slot="footer" class="dialog-footer">
                    <el-button :loading="batchSetForm.loading" type="primary" @click="batchSetConfirm">确 定</el-button>
                </div>
            </template>

            <template v-if="batchSetForm.singleEditGoods != null">
                <el-table :data="batchSetForm.singleEditGoods" border style="margin-top:20px;width: 100%">
                    <el-table-column prop="id" label="ID" width="90"></el-table-column>
                    <el-table-column label="标题" >
                        <template slot-scope="scope">
                            <div style="padding-bottom:3px;">编号：{{scope.row.ali_offerId}}</div>
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.cover_url"></com-image>
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
                    <el-table-column width="190" label="类目">
                        <template slot-scope="scope">
                            <el-cascader v-model="scope.row.ali_category_id" :options="batchSetForm.categorys"></el-cascader>
                        </template>
                    </el-table-column>
                    <el-table-column prop="price_rate" width="110" label="零售比（%）">
                        <template slot-scope="scope">
                            <el-input :disabled="scope.row.free_edit == 1" v-focus @blur="priceRateChanged(scope.row)" type="number" min="100" v-model="scope.row.price_rate"></el-input>
                        </template>
                    </el-table-column>
                    <!--
                    <el-table-column prop="origin_price_rate" width="110" label="划线比（%）">
                        <template slot-scope="scope">
                            <el-input @blur="originOriceRateChanged(scope.row)" type="number" min="100" v-model="scope.row.origin_price_rate"></el-input>
                        </template>
                    </el-table-column>
                    -->
                    <el-table-column prop="price" width="110" label="零售价">
                        <template slot-scope="scope">
                            <el-input :disabled="scope.row.free_edit == 0" v-model="scope.row.price"></el-input>
                        </template>
                    </el-table-column>
                    <!--
                    <el-table-column prop="origin_price" width="110" label="划线价">
                        <template slot-scope="scope">
                            <el-input disabled v-model="scope.row.origin_price"></el-input>
                        </template>
                    </el-table-column>
                    -->
                    <!--
                    <el-table-column prop="freight_price_rate" width="110" label="运费比（%）">
                        <template slot-scope="scope">
                            <el-input :disabled="scope.row.free_edit == 1" v-model="scope.row.freight_price_rate"></el-input>
                        </template>
                    </el-table-column>
                    -->
                    <el-table-column prop="freight_price" width="110" label="平台运费">
                        <template slot-scope="scope">
                            <el-input v-model="scope.row.freight_price"></el-input>
                        </template>
                    </el-table-column>
                    <el-table-column width="100" label="分销价">
                        <template slot-scope="scope">
                            {{scope.row.ali_data_json.currentPrice}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="ali_freight_price" width="100" label="1688运费"></el-table-column>
                    <el-table-column width="100" label="渠道价">
                        <template slot-scope="scope">
                            {{scope.row.ali_data_json.channelPrice}}
                        </template>
                    </el-table-column>


                </el-table>

                <el-card class="box-card" style="margin-top:20px;">

                    <div style="display:flex;">
                        <el-input placeholder="请输入内容" v-model="batchSetForm.singleSetValue" style="width:300px;" class="input-with-select">
                            <el-select v-model="batchSetForm.singleSetSel" slot="prepend" placeholder="请选择" style="width:90px;">
                                <el-option value="freight_price" label="运费"></el-option>
                                <!--
                                <el-option value="price" label="零售价"></el-option>
                                <el-option value="origin_price" label="划线价"></el-option>
                                -->
                            </el-select>
                            <el-button @click="batchSingleSetInput" slot="append">修改</el-button>
                        </el-input>
                    </div>

                    <el-table :data="batchSetForm.singleEditGoods[0].sku_list" height="500" border style="margin-top:20px;width: 100%">
                        <el-table-column prop="ali_sku_id" label="规格ID" width="150"></el-table-column>
                        <el-table-column prop="ali_attributes_label" label="规格属性"></el-table-column>
                        <el-table-column prop="price" width="110" label="零售价">
                            <template slot-scope="scope">
                                <el-input :disabled="scope.row.free_edit == 0" v-model="scope.row.price"></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column prop="freight_price" width="110" label="平台运费">
                            <template slot-scope="scope">
                                <el-input v-model="scope.row.freight_price"></el-input>
                            </template>
                        </el-table-column>
                        <el-table-column prop="ali_num" width="110" label="数量">
                            <template slot-scope="scope">
                                <el-input type="number" min="1" v-model="scope.row.ali_num"></el-input>
                            </template>
                        </el-table-column>
                        <!--
                        <el-table-column prop="origin_price" width="110" label="划线价">
                            <template slot-scope="scope">
                                <el-input disabled v-model="scope.row.origin_price"></el-input>
                            </template>
                        </el-table-column>
                        -->
                        <el-table-column width="110" label="分销价">
                            <template slot-scope="scope">
                                {{scope.row.ali_price}}
                            </template>
                        </el-table-column>
                        <el-table-column width="110" label="销量">
                            <template slot-scope="scope">
                                {{scope.row.amount_on_sale}}
                            </template>
                        </el-table-column>


                    </el-table>
                </el-card>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="batchSetForm.singleEditGoods = null">取 消</el-button>
                    <el-button :loading="batchSetForm.loading" type="primary" @click="singleEditConfirm">确 定</el-button>
                </div>
            </template>

        </el-dialog>


        <el-dialog title="编辑商品" :visible.sync="editGoods.dialogVisible" :close-on-click-modal="false">
            <el-form :rules="editGoods.rules" ref="editGoodsFormData" label-width="20%" :model="editGoods.formData" size="small">
                <el-form-item label="标题" prop="name">
                    <el-input v-model="editGoods.formData.name" style="width:60%"></el-input>
                </el-form-item>
                <el-form-item label="轮播图" prop="name">
                    <template v-if="editGoods.formData.ali_product_info.info.image.images.length">
                        <draggable v-model="editGoods.formData.ali_product_info.info.image.images" flex="dif:left">
                            <div v-for="(item,index) in editGoods.formData.ali_product_info.info.image.images" :key="index" style="margin-right: 20px;position: relative;cursor: move;">
                                <com-attachment @selected="updatePicUrl" :params="{'currentIndex': index}">
                                    <com-image mode="aspectFill" width="100px" height='100px' :src="item"></com-image>
                                </com-attachment>
                                <el-button class="del-btn" size="mini" type="danger" icon="el-icon-close" circle @click="delPic(index)"></el-button>
                            </div>
                        </draggable>
                    </template>
                    <template v-if="editGoods.formData.ali_product_info.info.image.images.length < 5">
                        <com-attachment style="margin-bottom: 10px;" :multiple="true" :max="9" @selected="picUrl">
                            <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 750" placement="top">
                                <div flex="main:center cross:center" class="add-image-btn">
                                    + 添加图片
                                </div>
                            </el-tooltip>
                        </com-attachment>
                    </template>
                </el-form-item>
                <el-form-item label="详情" prop="detail">
                    <com-rich-text v-model="editGoods.formData.ali_product_info.info.description" :value="editGoods.formData.ali_product_info.info.description"></com-rich-text>
                </el-form-item>
            </el-form>

            <div slot="footer" class="dialog-footer">
                <el-button @click="editGoods.dialogVisible=false">取 消</el-button>
                <el-button :loading="editGoods.btnLoading" type="primary" @click="editSaveConfirm">确 定</el-button>
            </div>
        </el-dialog>

    </el-card>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                aliGoodsDialogVisible: false,
                activeName: 'first',
                searchData: {
                    keyword: '',
                    sort_prop: '',
                    sort_type: '',
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
                }
            };
        },
        methods: {
            updatePicUrl(e, params) {
                this.editGoods.formData.ali_product_info.info.image.images[params.currentIndex] = e[0].url;
            },
            // 商品轮播图
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    e.forEach(function(item) {
                        if (self.editGoods.formData.ali_product_info.info.image.images.length >= 5) {
                            return;
                        }
                        self.editGoods.formData.ali_product_info.info.image.images.push(
                            item.url
                        );
                    });
                }
            },
            delPic(index) {
                this.editGoods.formData.ali_product_info.info.image.images.splice(index, 1)
            },
            sortReload(column){
                console.log(column);
                this.searchData.sort_prop = column.prop;
                this.searchData.sort_type = column.order == "descending" ? 'DESC' : 'ASC';
                this.getList();
            },
            priceRateChanged(row){
                let rate = (parseFloat(row['price_rate'])/100);
                row['price'] = rate * parseFloat(row['ali_data_json']['currentPrice']);
                for(var i=0; i < row.sku_list.length; i++){
                    row['sku_list'][i]['price'] = rate * parseFloat(row['sku_list'][i]['ali_price']);
                }
            },
            originOriceRateChanged(row){
                let rate = (parseFloat(row['origin_price_rate'])/100);
                row['origin_price'] = rate * parseFloat(row['ali_data_json']['currentPrice']);
                for(var i=0; i < row.sku_list.length; i++){
                    row['sku_list'][i]['origin_price'] = rate * parseFloat(row['sku_list'][i]['ali_price']);
                }
            },
            editIt(row){
                this.editGoods.dialogVisible = true;
                this.editGoods.formData = row;
            },
            handleSelectionChange(selection) {
                this.batchSetForm.selections = selection;
            },
            openBatchSetDialog(){
                if(this.batchSetForm.selections.length == 1){
                    this.batchSetForm.singleEditGoods = [];
                    this.batchSetForm.singleEditGoods.push(this.batchSetForm.selections[0]);
                }else{
                    this.batchSetForm.singleEditGoods = null;
                }
                this.batchSetForm.dialogVisible = true;
            },
            batchDeleteIt(){
                let i, ids = [];
                for(i=0; i < this.batchSetForm.selections.length; i++){
                    ids.push(this.batchSetForm.selections[i].id);
                }
                this.deleteWithIds(ids);
            },
            deleteIt(row){
                let ids = [];
                ids.push(row.id);
                this.deleteWithIds(ids);
            },
            deleteWithIds(id_arr){
                this.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'plugin/alibaba/mall/distribution/delete-goods'
                        },
                        method: 'post',
                        data: {id:id_arr}
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
            setCommonCategory(){
                for(var i=0; i < this.batchSetForm.selections.length; i++){
                    this.batchSetForm.selections[i]['ali_category_id'] = this.batchSetForm.category;
                }
            },
            batchSingleSetInput(){
                let i;
                for(i=0; i < this.batchSetForm.singleEditGoods[0].sku_list.length; i++){
                    if(this.batchSetForm.singleSetSel == 'freight_price'){
                        this.batchSetForm.singleEditGoods[0].sku_list[i]['freight_price'] = this.batchSetForm.singleSetValue;
                        continue;
                    }
                    if(this.batchSetForm.singleSetSel == 'price'){
                        this.batchSetForm.singleEditGoods[0].sku_list[i]['price'] = this.batchSetForm.singleSetValue;
                    }else{
                        this.batchSetForm.singleEditGoods[0].sku_list[i]['origin_price'] = this.batchSetForm.singleSetValue;
                    }
                }
            },
            batchSingleEdit(row){
                this.batchSetForm.singleEditGoods = [];
                this.batchSetForm.singleEditGoods.push(row);
            },
            editSaveConfirm(){
                let that = this;
                this.editGoods.btnLoading = true;
                this.editSave(this.editGoods.formData, function(e){
                    this.editGoods.btnLoading = false;
                    if (e.data.code == 0) {
                        that.$message.success(e.data.msg);
                    } else {
                        that.$message.error(e.data.msg);
                    }
                });
            },
            editSave(goods, fn){
                request({
                    params: {
                        r: 'plugin/alibaba/mall/distribution/goods-save'
                    },
                    method: 'post',
                    data: {
                        goods:JSON.stringify(goods)
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
                this.batchSetForm.loading = true;
                this.editSave(this.batchSetForm.singleEditGoods[0], function(e){
                    that.batchSetForm.loading = false;
                    if (e.data.code == 0) {
                        that.$message.success(e.data.msg);
                    } else {
                        that.$message.error(e.data.msg);
                    }
                });
            },
            batchSetConfirm(){
                if(this.batchSetForm.selections.length <= 0){
                    this.$message.error("请选择要设置的商品");
                    return;
                }
                var i,price,currentPrice;
                for(i=0; i < this.batchSetForm.selections.length; i++){
                    if(parseInt(this.batchSetForm.selections[i]['category_id']) <= 0){
                        this.$message.error("请设置类目");
                        return;
                    }
                    price = this.batchSetForm.selections[i]['price'];
                    currentPrice = this.batchSetForm.selections[i]['ali_data_json']['currentPrice'];
                    if(price.length <=0 || isNaN(price)){
                        this.$message.error("零售价必须大于0");
                        return;
                    }
                    if(parseFloat(price) < parseFloat(currentPrice)){
                        this.$message.error("零售价建议大于分销价");
                        return;
                    }
                }

                this.batchSetForm.loading = true;
                request({
                    params: {
                        r: 'plugin/alibaba/mall/distribution/goods-batch-save'
                    },
                    method: 'post',
                    data: {
                        goods_list:JSON.stringify(this.batchSetForm.selections)
                    }
                }).then(e => {
                    this.batchSetForm.loading = false;
                    if (e.data.code == 0) {
                        this.$message.success(e.data.msg);
                        this.batchSetForm.dialogVisible = false;
                        this.getList();
                        this.batchSetForm.selections = [];
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error(e.data.msg);
                    this.batchSetForm.loading = false;
                });
            },
            aliGoodsImport(rows){
                this.aliGoodsDialogVisible = false;
                this.batchSetForm.dialogVisible = true;
                this.batchSetForm.selections = rows;
                this.batchSingleEdit(rows[0]);
            },
            search() {
                this.page = 1;
                this.getList();
            },
            getCategory(){
                let params = {
                    r: 'plugin/alibaba/mall/distribution/get-category',
                    app_id:getQuery("app_id")
                };
                request({
                    params
                }).then(e => {
                    if (e.data.code === 0) {
                        this.batchSetForm.categorys = e.data.data;
                    }
                }).catch(e => {

                });
            },
            pageChange(page){
                this.page = page;
                this.getList();
            },
            getList() {
                let params = Object.assign(this.searchData, {
                    r: 'plugin/alibaba/mall/distribution/goods-list',
                    page: this.page,
                    app_id:getQuery("app_id")
                });
                request({
                    params
                }).then(e => {
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.loading = false;
                }).catch(e => {
                    this.loading = false;
                });
                this.loading = true;
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
            this.getCategory();
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

    .add-image-btn {
        width: 100px;
        height: 100px;
        color: #419EFB;
        border: 1px solid #e2e2e2;
        cursor: pointer;
    }

</style>