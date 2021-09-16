<template id="com-alibaba-goods">
    <div class="com-alibaba-goods">
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">

            <el-tabs v-model="searchData.biztype"   @tab-click="search">
                <el-tab-pane label="生产加工" name="1"></el-tab-pane>
                <el-tab-pane label="经销批发" name="2"></el-tab-pane>
                <el-tab-pane label="招商代理" name="3"></el-tab-pane>
                <el-tab-pane label="商业服务" name="4"></el-tab-pane>
            </el-tabs>

            <div style="margin-top:6px;">

                <el-select v-model="searchData.mode" placeholder="请选择" size="small" style="width:90px;">
                    <el-option value="0" label="关键词"></el-option>
                    <el-option value="1" label="编号"></el-option>
                </el-select>

                <div class="input-item" style="width:350px;">
                    <el-input v-if="searchData.mode == 0" @keyup.enter.native="search" size="small" placeholder="请输入关键词搜索" v-model="searchData.keyword"
                              clearable @clear="search">
                        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                    </el-input>
                    <el-input v-if="searchData.mode == 1" @keyup.enter.native="search" size="small" placeholder="请输入商品id搜索，多个id用逗号分割" v-model="searchData.offerIds"
                              clearable @clear="search">
                        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                    </el-input>
                </div>
            </div>


            <el-table v-loading="loading" :data="list" border style="width: 100%" @selection-change="handleSelectionChange">
                <el-table-column width="75" >
                    <template slot-scope="scope">
                        <el-button @click="aliGoodsImport(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="选择" placement="top">
                                <img src="statics/img/mall/plus.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
                <el-table-column label="标题" >
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px;">
                                <com-image mode="aspectFill" :src="scope.row.imgUrl"></com-image>
                            </div>
                            <div>
                                <div>编号：{{scope.row.offerId}}</div>
                                <div flex="dir:left">
                                    <el-tooltip class="item" effect="dark" placement="top">
                                        <template slot="content">
                                            <div style="width: 320px;">{{scope.row.title}}</div>
                                        </template>
                                        <com-ellipsis :line="1">{{scope.row.title}}</com-ellipsis>
                                    </el-tooltip>
                                </div>
                            </div>
                        </div>

                    </template>
                </el-table-column>
                <el-table-column prop="currentPrice" width="90" label="分销价"></el-table-column>
                <el-table-column prop="channelPrice" width="90" label="渠道价"></el-table-column>
                <el-table-column prop="superBuyerPrice" width="90" label="超买价"></el-table-column>
                <el-table-column prop="soldOut" width="90" label="销量"></el-table-column>
                <el-table-column prop="profit" width="90" label="利润空间"></el-table-column>
                <el-table-column prop="enable" width="90" label="是否有效"></el-table-column>

            </el-table>

            <div v-if="!loading" style="display: flex;justify-content: space-between;margin-top:20px;">
                <!--
                <div style="margin: 7.5px 0px;"><el-button @click="aliGoodsImport" :disabled="selections.length <= 0" :loading="btnLoading" type="danger">一键添加</el-button></div>
                -->
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


        </el-dialog>
    </div>
</template>

<script>
    Vue.component('com-alibaba-goods', {
        template: '#com-alibaba-goods',
        props: {
            visible: Boolean
        },
        data() {
            return {
                dialogTitle: "1688选品库",
                activeName: "first",
                dialogVisible: false,
                btnLoading: false,
                searchData: {
                    biztype: "1",
                    keyword: "",
                    mode: "0",
                    offerIds: ''
                },
                page: 1,
                list: [],
                pagination: null,
                loading: false,
                selections: []
            };
        },
        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
                if(this.dialogVisible){
                    this.getList();
                }
            }
        },
        methods: {
            aliGoodsImport(row){
                let selections = [];
                selections.push(row);
                let that = this;
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/alibaba/mall/distribution/ali-goods-import'
                    },
                    method: 'post',
                    data: {
                        app_id:getQuery("app_id"),
                        goods_array:selections
                    }
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        that.$emit('import', e.data.data);
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.$message.error(e.data.msg);
                    that.loading = false;
                });
            },
            search(){
                this.page = 1;
                this.getList();
            },
            pageChange(page){
                this.page = page;
                this.getList();
            },
            handleSelectionChange(selection) {
                this.selections = selection;
            },
            getList(){
                if(this.searchData.mode == 0){
                    this.searchData.offerIds = '';
                }else{
                    this.searchData.keyword = '';
                }
                let params = Object.assign(this.searchData, {
                    r: 'plugin/alibaba/mall/distribution/alibaba-goods-search',
                    page: this.page,
                    app_id:getQuery("app_id")
                });
                this.loading = true;
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
            },
            close(){
                this.$emit('close');
            }
        }
    });
</script>