<?php

echo $this->render('../components/com-goods');

$mchId = Yii::$app->mchAdmin->identity->mchModel->id;
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'mch/goods/index'})">
                        商品列表
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="form.goods_id > 0">详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>添加商品</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <com-goods :is_member="0"
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
                url: 'mch/goods/index',
                is_mch: 1,
                mch_id: <?= $mchId ?>,
            }
        },
        created() {
            if(getQuery('page') > 1) {
                this.url = {
                    r: 'mch/goods/index',
                    page: getQuery('page')
                }
            }
        },

    });
</script>
