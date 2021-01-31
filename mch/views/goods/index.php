<?php

echo $this->render("../components/com-goods-list");
?>
<style>

</style>
<div id="app" v-cloak>
    <com-goods-list
            :is-show-export-goods="isShowExportGoods"
            @get-all-checked="getAllChecked"
            :is_edit_goods_name='true'
            ref="goodsList"
            :is-show-svip="isShowSvip"
            :is-show-integral="isShowIntegral"
            :batch-list="batchList">
        <template slot="batch" slot-scope="item">
            <div v-if="item.item === 'negotiable'">
                <el-form-item label="是否加入商品面议">
                    <el-tooltip effect="dark" content="如果开启面议，则商品无法在线支付" placement="top">
                        <i class="el-icon-info"></i>
                    </el-tooltip>
                    <el-switch @change="batch('negotiable')"
                               v-model="batchList[1].params.status"
                               :active-value="1"
                               :inactive-value="0">
                    </el-switch>
                </el-form-item>
            </div>
        </template>
    </com-goods-list>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                is_mch: 0,
                batchList: [],
                isShowIntegral: true,
                isShowSvip: true,
                isAllChecked: false,
                isShowExportGoods: true,
            };
        },
        methods: {
            // 加入快速购买
            switchQuickShop(row) {
                let self = this;
                request({
                    params: {
                        r: 'mch/goods/switch-quick-shop',
                    },
                    method: 'post',
                    data: {
                        id: row.id
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            batch(key) {
                let isAllChecked= this.isAllChecked;
                if (key === 'quick') {
                    this.batchList[0].content = isAllChecked ? '警告: 批量设置所有商品' + (this.batchList[0].params.status ? '加入' : '移除') + '快速购买,是否继续' : '批量' + (this.batchList[0].params.status ? '加入' : '移除') + '快速购买,是否继续';
                } else if (key === 'negotiable') {
                    this.batchList[1].content = isAllChecked ? '警告: 批量设置所有商品' + (this.batchList[1].params.status ? '加入' : '移除') + '商品面议,是否继续' : '批量' + (this.batchList[1].params.status ? '加入' : '移除') + '商品面议,是否继续';
                }
            },
            getAllChecked(isAllChecked) {
                this.batchList[0].content = isAllChecked ? '警告: 批量设置所有商品' + (this.batchList[0].params.status ? '加入' : '移除') + '快速购买,是否继续' : '批量' + (this.batchList[0].params.status ? '加入' : '移除') + '快速购买,是否继续';
                this.batchList[1].content = isAllChecked ? '警告: 批量设置所有商品' + (this.batchList[1].params.status ? '加入' : '移除') + '商品面议,是否继续' : '批量' + (this.batchList[1].params.status ? '加入' : '移除') + '商品面议,是否继续';
                this.isAllChecked = isAllChecked;
            }
        },
        mounted() {
            this.is_mch = this.$refs.goodsList.is_mch;
            if (this.is_mch) {
//                this.isShowExportGoods = false;
                this.batchList = [];
                this.isShowIntegral = false;
                this.isShowSvip = false;
            }
        }
    });
</script>
