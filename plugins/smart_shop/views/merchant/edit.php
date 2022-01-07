<?php
echo $this->render("com-choose-mch");
echo $this->render("com-add-smartshop");
?>
<div id="app" v-cloak v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>设置分账商户</span>
            </div>
        </div>
        <div class="form-body">

            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="200px" size="small">
                <el-form-item label="选择商户" prop="bsh_mch_id">
                    <el-card class="box-card" style="width:45%">
                        <div v-if="ruleForm.bsh_mch_id" style="display:flex;margin-bottom:20px;">
                            <image :src="MchSet.logo" style="width:95px;height:95px;"/>
                            <div style="margin-left:20px;display:flex;flex-direction: column;justify-content: space-between">
                                <span>{{MchSet.name}}</span>
                                <span>手机：{{MchSet.mobile}}</span>
                                <span>ID：{{ruleForm.bsh_mch_id}}</span>
                            </div>
                        </div>
                        <com-choose-mch @confirm="chooseMch" style=""></com-choose-mch>
                    </el-card>
                </el-form-item>
                <el-form-item label="智慧经营门店">
                    <el-table border style="width: 100%">
                        <el-table-column label="ID" width="100" align="center"></el-table-column>
                        <el-table-column label="手机" width="150" align="center"></el-table-column>
                        <el-table-column label="商户" width="350" ></el-table-column>
                        <el-table-column label="门店"></el-table-column>
                    </el-table>
                    <com-add-smartshop style="margin-top:20px;"></com-add-smartshop>
                </el-form-item>
            </el-form>

        </div>



    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: "first",
                ruleForm: {
                    bsh_mch_id: ''
                },
                rules: {

                },
                MchSet: {
                    name: '',
                    logo: '',
                    mobile: ''
                }
            };
        },
        mounted: function () {

        },
        methods: {
            chooseMch(data){
                this.ruleForm.bsh_mch_id = data.id;
                this.MchSet.name = data.store.name;
                this.MchSet.mobile = data.mobile;
                this.MchSet.logo = data.store.cover_url;
            }
        }
    });
</script>
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
    }

    .img-type .el-form-item__content {
        width: 100% !important;
    }
</style>
