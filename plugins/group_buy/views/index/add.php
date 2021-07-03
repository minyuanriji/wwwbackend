<!-- 引一个当前组件 -->
<?php
Yii::$app->loadPluginComponentView('group-com-goods');
?>			

<div id="app">
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer">添加商品</span></el-breadcrumb-item>
            </el-breadcrumb>
        </div>
		<!-- 这里是引用的内部组件-和vue一样的套路 -->
        <group-com-goods sign="group_buy"
                   ref="appGoods"
                   :is_attr="1"
                   :is_show="0"
                   store_url="plugin/group_buy/mall/index/store-or-edit"
                   @goods-success="goodsSuccess"
                   get_goods_detail_url="plugin/group_buy/mall/index/show"
                   :form="form"
                   :rule="rule"
                   referrer="plugin/group_buy/mall/index/index"
                   :goods-head="false"
                   :status_change_text="statusChangeText"
                   :preview-info="previewInfo"
                   @handle-preview="handlePreview">
        </group-com-goods>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
		
        data() {
            return {
                previewData: null,
                previewInfo: {
                    is_head: false,
                    is_cart: false,
                },
                is_add: 0,
                form: {
                    is_alone_buy: false,
                    end_at: '',
                    limit_num: -1,
                    is_sell_well: false,
                },
                cats: [],
                attrGroups: [],
                rule: {
                    is_alone_buy: [
                        {required: true, message: '请选择是否允许单独购买', trigger: 'change'},
                    ],
                    end_at: [
                        {required: true, message: '请选择拼团结束时间', trigger: 'change'},
                    ],
                    limit_num: [
                        {required: true, message: '请输入拼团次数限制', trigger: 'change'},
                    ],
                },
                isGroupsRestrictions: true,
                isBuyNumRestrictions: true,
                statusChangeText: '拼团商品至少需要添加一个拼团组,商品才可上架。'
            }
        },
        methods: {
            handlePreview(e) {
                const price = Number(e.price);
                const attr = e.attr;
                let arr = [];
                attr.map(v => {
                    arr.push(Number(v.price));
                });
                let max = Math.max.apply(null, arr);
                let min = Math.min.apply(null, arr);

                let actualPrice = -1;
                let type = 'text-price';
                if (max > min && min >= 0) {
                    actualPrice = min + '-' + max;
                } else if (max == min && min >= 0) {
                    actualPrice = min;
                } else if (price > 0) {
                    actualPrice = price;
                } else if (price == 0) {
                    actualPrice = '免费';
                    type = '';
                }

                this.previewData = Object.assign({},e,{
                    actualPrice,
                    tType:type,
                });
            },
            goodsSuccess(detail) {
                this.form = Object.assign(this.form, detail.plugin);
            },
            itemChecked(type) {
                if (type === 1) {
                    this.form.limit_num = this.isGroupsRestrictions ? -1 : 0
                }
            },
        },
        mounted(){

        }
    })
	
	
</script>
