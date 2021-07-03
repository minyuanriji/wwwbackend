<?php
/**
 * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */

Yii::$app->loadPluginComponentView('business-card-edit');
$mchId = Yii::$app->admin->identity->mch_id;
?>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/business_card/mall/business_card/index'})">
                        查看名片
                    </span>
                </el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <business-card-edit :is_member="is_mch ? 0 : 1"
                   :is_cats="1"
                   :is_show="1"
                   :is_info="1"
                   :form="form"
                   :is_detail="1"
                   :is_mch="is_mch"
                   :mch_id="mch_id"
                   :referrer="url"
                   ref="appGoods">
        </business-card-edit>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {},
                url: 'plugin/business_card/mall/business-card/index',
                is_mch: <?= $mchId > 0 ? 1 : 0 ?>,
                mch_id: <?= $mchId ?>,
                edit:{
                    show: false,
                    user_id:0,
                },
            }
        },
        created() {
            if(getQuery('id') > 1) {
                this.url = {
                    r: 'plugin/business_card/mall/business-card/detail',
                    id: getQuery('id')
                }
            }
        },

    });
</script>
