<?php
Yii::$app->loadComponentView('goods/com-add-cat');
?>
<template id="com-taobao-goods-import">
    <div>
        <el-form  label-width="110px">
            <el-form-item label="类别">
                <el-tag style="margin-right: 5px;margin-bottom:5px" v-for="(item,index) in cats" :key="index" type="warning" closable disable-transitions @close="destroyCat(index)">{{item.label}}
                </el-tag>
                <el-button type="default" @click="$refs.cats.openDialog();">选择分类</el-button>
                <com-add-cat ref="cats" :new-cats="cats" @select="selectCat"></com-add-cat>
            </el-form-item>
            <el-form-item label="商品">
                <el-table :data="importList" border v-loading="loading" size="small">
                    <el-table-column width="110" label="淘宝类别" prop="category_name"></el-table-column>
                    <el-table-column sortable="custom" label="商品名称" width="320">
                        <template slot-scope="scope">
                            <a :href="scope.row.url" target="_blank" v-if="editIndex != scope.$index">
                                <div flex="box:first">
                                    <div style="padding-right: 10px;">
                                        <com-image mode="aspectFill" :src="scope.row.pict_url"></com-image>
                                    </div>
                                    <div flex="cross:top cross:center">
                                        <div flex="dir:left">
                                            <el-tooltip class="item" effect="dark" placement="top">
                                                <template slot="content">
                                                    <div style="width: 320px;">{{scope.row.title}}</div>
                                                </template>
                                                <com-ellipsis :line="2">{{scope.row.title}}</com-ellipsis>
                                            </el-tooltip>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <el-input v-else placeholder="请输入内容" v-model="scope.row.title" ></el-input>
                        </template>
                    </el-table-column>
                    <el-table-column width="110" label="商城价（元）" >
                        <template slot-scope="scope">
                            <span v-if="editIndex != scope.$index">{{scope.row.price}}</span>
                            <el-input v-else type="number" min="0" placeholder="请输入" v-model="scope.row.price" ></el-input>
                        </template>
                    </el-table-column>
                    <el-table-column width="110" label="一口价（元）" prop="reserve_price"></el-table-column>
                    <el-table-column width="110" label="邮费（元）" prop="real_post_fee"></el-table-column>
                    <el-table-column width="110" label="佣金（%）" >
                        <template slot-scope="scope">
                            {{commissionRate(scope.row)}}
                        </template>
                    </el-table-column>
                    <el-table-column label="库存" prop="volume"></el-table-column>

                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <div v-if="editIndex != scope.$index">
                                <!--
                                <el-button @click="editIt(scope.$index)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                        <img src="statics/img/mall/edit.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                -->
                                <el-button @click="deleteIt(scope.$index)" type="text" circle size="mini">
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </div>
                            <el-button v-else @click="editIndex = -1" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="确定" placement="top">
                                    <img src="statics/img/mall/pass.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </template>
                    </el-table-column>

                </el-table>
            </el-form-item>
            <el-form-item label=" ">
                <el-button :loading="loading" @click="confirm" type="primary">确定</el-button>
                <el-button v-if="!loading" @click="cancel" type="default">取消</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>
<script>
    Vue.component('com-taobao-goods-import', {
        template: '#com-taobao-goods-import',
        props: {
            account: Number,
            show: Boolean,
            importList: {
                type: Array
            }
        },
        data() {
            return {
                loading: false,
                dialogVisible: false,
                editIndex: -1,
                cats: []
            };
        },
        created() {
            this.dialogVisible = this.show;
            this.list = this.importList;
        },
        computed: {
            commissionRate(item){
                return function(item){
                    return (item.commission_rate/100).toFixed(2);
                }
            }
        },
        watch: {
            show(newVal, oldVal) {
                this.dialogVisible = this.show;
            }
        },
        methods: {
            confirm(){
                if(this.cats.length <= 0){
                    this.$message.error("请选择一个分类");
                    return;
                }
                if(this.importList.length <= 0){
                    this.$message.error("请选择商品");
                    return;
                }
                let that = this, i, catIds = [];
                for(i=0; i < this.cats.length; i++){
                    catIds.push(this.cats[i].value);
                }
                this.loading = true;
                request({
                    params: {
                        r: "plugin/taobao/mall/goods/remote-import"
                    },
                    method: "post",
                    data: {
                        account_id: this.account,
                        cat_ids: catIds,
                        import_list: this.importList
                    }
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        that.$emit("success", {goods_id_list: e.data.data.goods_id_list});
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    that.loading = false;
                    that.$message.error(e.data.msg);
                });
            },
            selectCat(cats) {
                this.cats = cats;
            },
            destroyCat(value, index) {
                this.cats.splice(index, 1)
            },
            cancel() {
                this.$emit("close");
            },
            editIt(index){
                this.editIndex = index;
            },
            deleteIt(index){
                this.importList.splice(index, 1);
            }
        }
    });
</script>
