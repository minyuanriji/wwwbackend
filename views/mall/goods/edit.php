<?php
Yii::$app->loadComponentView('com-goods');
$mchId = Yii::$app->admin->identity->mch_id;
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mall/goods/index'})">
                        商品列表
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="form.goods_id > 0">详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>添加商品</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <com-goods :is_member="is_mch ? 0 : 1"
                   :is_cats="1"
                   :is_show="1"
                   :is_info="1"
                   :form="form"
                   :is_detail="1"
                   :is_mch="is_mch"
                   :mch_id="mch_id"
                   :referrer="url"
                   ref="appGoods">
        </com-goods>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {},
                url: {
                    r: 'mall/goods/index',
                    sign:'mch',
                    page:1,
                },
                is_mch: <?= $mchId > 0 ? 1 : 0 ?>,
                mch_id: <?= $mchId ?>,
            }
        },
        created() {
            if(getQuery('page') > 1) {
                this.url = {
                    r: 'mall/goods/index',
                    page: getQuery('page'),
                    sign:'mch',
                }
            }
        },

    });
</script>
