<template id="com-ali-selects">
    <div class="com-ali-selects">
        <el-dialog width="80%" title="选择商品" :visible.sync="dialogVisible" :close-on-click-modal="false">
            <el-tabs v-model="activeName">
                <el-tab-pane label="淘宝联盟" name="ali">
                    <el-row v-for="datas in list" :gutter="20" style="margin-bottom:20px;">
                        <el-col v-for="data in datas" :span="4">
                            <el-card shadow="hover" :body-style="{ padding: '0px' }">
                                <img :src="data.pict_url" class="image">
                                <div style="padding:14px;">
                                    <div class="title-n2">
                                        <a :href="data.click_url" target="_blank" style="text-decoration: none;color:#333">{{data.title}}</a>
                                    </div>
                                </div>
                                <div class="info" style="margin-bottom:14px;">
                                    <div style="">
                                        <span style="color:darkred">{{data.reserve_price}}元</span>
                                        <div>一口价</div>
                                    </div>
                                    <div style="border-left:1px solid #eee;">
                                        <span style="color:royalblue">{{data.commission_rate}}%</span>
                                        <div>佣金比</div>
                                    </div>
                                    <div style="border-left:1px solid #eee;">
                                        <el-button type="primary" icon="el-icon-check" circle></el-button>
                                    </div>
                                </div>
                            </el-card>
                        </el-col>
                    </el-row>
                </el-tab-pane>
            </el-tabs>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('com-ali-selects', {
        template: '#com-ali-selects',
        props: {
            visible: Boolean
        },
        data() {
            return {
                dialogVisible: true,
                activeName: "ali",
                list: [
                    [
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                    ],
                    [
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                        {
                            "click_url": "//s.click.taobao.com/t?e=m%3D2%26s%3D25xMFBG%2Fw1dw4vFB6t2Z2ueEDrYVVa64Dne87AjQPk9yINtkUhsv0ArhMeOYG0vNuFWSfdANr%2FFDdnvI9cDku6BRXO3UQc5FElpY6wGRyCfvkV%2FoJoWbPo0lsKgFqaeNFBoMXOGuG5DkaqczTKGnOg1aTi71SL980yI9%2Fsl3c6E9mUV4TdlPHW9st%2BlANTzwlbCuICDGVlMIiHvTN9fcssYOae24fhW0&scm=1007.19011.125585.0_13366&pvid=9edab515-2829-428b-91e2-4e638cffa4a9&app_pvid=59590_33.8.165.42_759_1629786547002&ptl=floorId:13366;originalFloorId:13366;pvid:9edab515-2829-428b-91e2-4e638cffa4a9;app_pvid:59590_33.8.165.42_759_1629786547002&union_lens=lensId%3AMAPI%401629786547%402108a52a_08d7_17b76d9239b_214a%4001",
                            "pict_url": "//gw.alicdn.com/bao/uploaded/i2/2200554932955/O1CN01I80x5P1XhR4goBwjL_!!0-item_pic.jpg",
                            "title": "荣晟医用冷敷贴医美无菌敷料敏感肌肤晒后修护受损肌肤日常护理常护理常护理常护理常护理常护理常护理",
                            "commission_rate": "15.0", "reserve_price": "298"},
                    ]
                ]
            };
        },
        created() {

        },
        watch: {

        },
        methods: {

        }
    });
</script>

<style>
.image {
    width: 100%;
    display: block;
}
.title-n2{
    word-break: break-all;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
}
.info{color:gray;display:flex;}
.info > div{flex:1;text-align: center}
</style>
