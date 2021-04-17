<style>
    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 20px;
    }

    .open-img .el-dialog {
        margin-top: 0 !important;
    }

    .click-img {
        width: 100%;
    }

    .el-input-group__append {
        background-color: #fff
    }
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

        </div>

    </el-card>

</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {

            };
        },
        watch: {

        },
        methods: {

            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/baopin/mall/clerk/detail',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {

                    }
                }).catch(e => {
                });
            }
        },
        mounted: function () {

        }
    });
</script>