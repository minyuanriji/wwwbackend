<style type="text/css">
    .form-body{}
    .form-body .detail-info{}
    .form-body .detail-info-hd{font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:3px;}
    .form-body .detail-info-body{}
    .form-body .g-col{padding:10px 10px;border-bottom:1px solid #ddd;}
    .form-body .flex-2{ display: flex;}
    .form-body .flex-2 .flex-column1{width:150px;text-align:right;border-right:1px solid #ddd;}
    .form-body .flex-2 .flex-column2{flex-grow:1}
</style>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/baopin/mall/clerk/list'})">核销记录</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>详情</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">

            <div class="detail-info">
                <div class="detail-info-hd">核销员信息</div>
                <div class="detail-info-body">
                    <div class="flex-2">
                        <div class="g-col flex-column1">用户</div>
                        <div class="g-col flex-column2">
                            <com-image mode="aspectFill" style="float: left;margin-right: 8px"
                                       :src="user.avatar_url"></com-image>
                            {{user.nickname}}</div>
                    </div>
                    <div class="flex-2">
                        <div class="g-col flex-column1">订单号</div>
                        <div class="g-col flex-column2">{{order.order_no}}</div>
                    </div>
                    <div class="flex-2">
                        <div class="g-col flex-column1">商品</div>
                        <div class="g-col flex-column2">
                            <div v-for="(detail, key, index) in details" flex="box:first">
                                <div style="padding-right:10px;">
                                    <com-image mode="aspectFill" :src="detail.goods_info.goods_attr.cover_pic"></com-image>
                                </div>
                                <div>
                                    <div>
                                        <el-tooltip class="item" effect="dark" placement="top">
                                            <template slot="content">
                                                <div style="width: 320px;">{{detail.goods_info.goods_attr.name}}</div>
                                            </template>
                                            <com-ellipsis :line="2">{{detail.goods_info.goods_attr.name}}</com-ellipsis>
                                        </el-tooltip>
                                    </div>
                                    <div style="flex-grow: 1;font-weight:bold;color:#999">数量 x {{detail.num}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <el-button type="default" @click="onBack()" style="margin-top:10px;">返回</el-button>
            </div>
        </div>

    </el-card>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                user: {},
                order: {},
                details: []
            };
        },
        watch: {

        },
        methods: {

            onBack() {
                navigateTo({r: 'mall/order-clerk/index'});
            },

            getDetail() {
                this.cardLoading = true;
                var self = this;
                request({
                    params: {
                        r: 'mall/order-clerk/detail',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        self.user    = e.data.data.user;
                        self.order   = e.data.data.order;
                        self.details = e.data.data.details;
                    }else{
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error('request fail');
                });
            }
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>