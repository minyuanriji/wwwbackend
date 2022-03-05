<template id="com-taobao-goods-import-edit">
    <div>
        <el-form  label-width="110px">
            <el-form-item label="编辑商品">
                <el-table :data="list" border v-loading="listLoading" size="small">
                    <el-table-column prop="id" label="ID" width="100"></el-table-column>
                    <el-table-column prop="sort" width="150" label="排序">
                        <template slot-scope="scope">
                            <div v-if="sort_goods_id != scope.row.id" flex="dir:left cross:center">
                                <span>{{scope.row.sort}}</span>
                                <el-button class="edit-sort" type="text" @click="editSort(scope.row)">
                                    <img src="statics/img/mall/order/edit.png" alt="">
                                </el-button>
                            </div>
                            <div style="display: flex;align-items: center" v-else>
                                <el-input style="min-width: 70px" type="number" size="mini" class="change"
                                          v-model="sort"
                                          autocomplete="off"></el-input>
                                <el-button class="change-quit" type="text" style="color: #F56C6C;padding: 0 5px"
                                           icon="el-icon-error"
                                           circle @click="quit()"></el-button>
                                <el-button class="change-success" type="text"
                                           style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                           icon="el-icon-success" circle @click="changeSortSubmit(scope.row)">
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="分类"  width="100">
                        <template slot-scope="scope">
                            <div class="goods-cat">
                                <el-tag v-if="scope.row.cats && scope.row.cats.length > 0" size="mini">
                                    {{scope.row.cats[0].name}}
                                </el-tag>
                                <el-tooltip v-if="scope.row.cats && scope.row.cats.length > 1" placement="top">
                                    <div slot="content">
                                        <span v-for="item in scope.row.cats" :key="item.id">{{item.name}}&nbsp;</span>
                                    </div>
                                    <span>...</span>
                                </el-tooltip>
                            </div>

                        </template>
                    </el-table-column>
                    <el-table-column label="商品名称" width="220">
                        <template slot-scope="scope">
                            <div flex="box:first">
                                <div style="padding-right: 10px;">
                                    <com-image mode="aspectFill" :src="scope.row.goodsWarehouse.cover_pic"></com-image>
                                </div>
                                <div flex="cross:top cross:center">
                                    <div v-if="goodsId != scope.row.id" flex="dir:left">
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{scope.row.goodsWarehouse.name}}</div>
                                            </template>
                                            <com-ellipsis :line="2">{{scope.row.goodsWarehouse.name}}</com-ellipsis>
                                        </el-tooltip>
                                        <el-button style="padding: 0;" type="text"  @click="editGoodsName(scope.row)">
                                            <img src="statics/img/mall/order/edit.png" alt="">
                                        </el-button>
                                    </div>
                                    <div style="display: flex;align-items: center" v-else>
                                        <el-input style="min-width: 70px"
                                                  type="text"
                                                  size="mini"
                                                  class="change"
                                                  v-model="goodsName"
                                                  maxlength="100"
                                                  show-word-limit
                                                  autocomplete="off"
                                        ></el-input>
                                        <el-button class="change-quit" type="text"
                                                   style="color: #F56C6C;padding: 0 5px"
                                                   icon="el-icon-error"
                                                   circle @click="quit()"></el-button>
                                        <el-button class="change-success" type="text"
                                                   style="margin-left: 0;color: #67C23A;padding: 0 5px"
                                                   icon="el-icon-success" circle
                                                   @click="changeGoodsName(scope.row)">
                                        </el-button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="price" label="售价" width="160"></el-table-column>
                    <el-table-column prop="goods_stock" label="库存"  width="100">
                        <template slot-scope="scope">
                            <div v-if="scope.row.goods_stock > 0">{{scope.row.goods_stock}}</div>
                            <div v-else style="color: red;">售罄</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="virtual_sales" width="110" label="虚拟销量" ></el-table-column>
                    <el-table-column prop="real_sales" width="90" label="真实销量" ></el-table-column>
                    <el-table-column label="状态" width="110" >
                        <template slot-scope="scope">
                            <el-tag size="small" type="success" v-if="scope.row.status">销售中</el-tag>
                            <el-tag size="small" type="warning" v-else>下架中</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button @click="edit(scope.row)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button @click="detail(scope.row)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="查看" placement="top">
                                    <img src="statics/img/mall/detail.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-form-item>
            <el-form-item label=" ">
                <el-button @click="finish" type="primary">完成</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>
<script>
    Vue.component('com-taobao-goods-import-edit', {
        template: '#com-taobao-goods-import-edit',
        props: {
            goodsIdList: {
                type: Array
            }
        },
        data() {
            return {
                loading: false,
                listLoading: false,
                list: [],

                sort_goods_id: null,
                sort: 0,

                goodsName: null,
                goodsId: null,
            };
        },
        created() {
            this.getList();
        },
        computed: {

        },
        watch: {

        },
        methods: {
            finish(){
                this.$emit("finish");
            },

            edit(row){
                var path = window.location.origin + window.location.pathname + '?r=mall%2Fgoods%2Fedit&id=' + row.id + '&mch_id=' + row.mch_id + '&page=' + this.page;
                window.open(path, '_blank');
            },

            detail(row){
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/taobao/mall/goods/detail'
                    },
                    data: {goods_id: row.id},
                    method: 'post'
                }).then((e) => {
                    self.listLoading = false;
                    if (e.data.code == 0) {
                        window.open(e.data.data.detail.url, '_blank');
                    }else {
                        self.$message.error(e.data.msg);
                    }
                }).catch((e) => {
                    self.listLoading = false;
                    console.log(e);
                });
            },

            //编辑标题 START
            editGoodsName(row) {
                this.goodsId = row.id;
                this.goodsName = row.goodsWarehouse.name;
            },
            changeGoodsName(row) {
                let self = this;
                request({
                    params: {
                        r: 'mall/goods/update-goods-name'
                    },
                    data: {
                        goods_id: self.goodsId,
                        goods_name: self.goodsName
                    },
                    method: 'post'
                }).then((e) => {
                    if (e.data.code == 0) {
                        self.goodsId = null;
                        self.$message.success(e.data.msg);
                        self.getList();
                    }else {
                        self.$message.error(e.data.msg);
                    }
                }).catch((e) => {
                    self.$message.error(e.data.msg);
                });
            },
            //编辑标题 END

            //编辑排序 START
            changeSortSubmit(row) {
                let self = this;
                row.sort = self.sort;
                if (!row.sort || row.sort < 0) {
                    self.$message.warning('排序值不能小于0')
                    return;
                }
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/goods/edit-sort'
                    },
                    method: 'post',
                    data: {
                        id: row.id,
                        sort: row.sort,
                    }
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                        self.sort_goods_id = null;
                        self.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.listLoading = false;
                });
            },
            quit() {
                this.sort_goods_id = null;
                this.goodsId = null;
            },
            editSort(row) {
                this.sort_goods_id = row.id;
                this.sort = row.sort;
            },
            //编辑排序 END

            getList() {
                let self = this;
                self.listLoading = true;
                let search = {
                    goods_id_list: self.goodsIdList
                };
                request({
                    params: {
                        r: 'mall/goods/index',
                        page: 1,
                        search: search
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                }).catch((e) => {
                    self.listLoading = false;
                })
            },
        }
    });
</script>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    #com-taobao-goods-import-edit .table-body .edit-sort-img {
        width: 14px;
        height: 14px;
        margin-left: 5px;
        cursor: pointer;
    }

    #com-taobao-goods-import-edit .goods-cat .el-tag--mini {
        max-width: 60px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    #com-taobao-goods-import-edit .export-dialog .el-dialog {
        min-width: 350px;
    }

    #com-taobao-goods-import-edit .export-dialog .el-dialog__body {
        padding: 20px 20px;
    }

    #com-taobao-goods-import-edit .export-dialog .el-button--submit {
        color: #FFF;
        background-color: #409EFF;
        border-color: #409EFF;
    }

</style>