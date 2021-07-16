<style type="text/css">
.form-body{}
.form-body .detail-info{}
.form-body .detail-info-hd{font-weight:bold;border-bottom:1px solid #ddd;padding-bottom:3px;}
.form-body .detail-info-body{padding:10px 10px;}
.form-body .flex-2{}
.form-body .flex-2 .flex-column1{flex-grow:1;}
.form-body .flex-2 .flex-column2{flex-grow:2;}
</style>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/baopin/mall/clerk/list'})">门店核销</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>核销详情</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">

            <div class="detail-info">
                <div class="detail-info-hd">店铺信息</div>
                <div class="detail-info-body">
                    <div class="flex-2">
                        <div class="flex-column1" style="flex-grow:1;">店铺名称</div>
                        <div class="flex-column2" style="flex-grow:2;">ddd</div>
                    </div>
                </div>
            </div>
        </div>

    </el-card>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                store: {},
                order: {},
                details: []
            };
        },
        watch: {

        },
        methods: {

            getDetail() {
                this.cardLoading = true;
                var self = this;
                request({
                    params: {
                        r: 'plugin/baopin/mall/clerk/detail',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        self.store   = e.data.data.store;
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