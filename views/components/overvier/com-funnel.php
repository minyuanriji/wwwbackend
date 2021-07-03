<style scoped>
    .item{
        margin-bottom: 2px;
        display: flex;
        align-items: center;
    }
    .item .center{
        height: 100%;
        text-align: center;
    }

    .right{
        width: 0;
        height: 0;
        border-style: solid;
    }

    .left{
        width: 0;
        height: 0;
        border-style: solid;
    }
</style>
<template id="com-funnel">
    <div class="com-funnel"flex="dir:top cross:center">
        <div class="item" style="height: 53px">
            <div class="left" style="border-color: transparent #1e60ff transparent transparent;border-width: 0 33.5px 53px 0;"></div>
            <div class="center" style="background-color: #1e60ff;width: 180px;line-height: 53px;font-size: 20px">{{`${dataList[0].text}: ${dataList[0].num}`}}</div>
            <div class="right" style="border-color: #1e60ff transparent transparent transparent;border-width: 53px 33.5px 0 0;"></div>
        </div>
        <div class="item" style="height: 60px;">
            <div class="left" style="border-color: transparent #0032c1 transparent transparent;border-width: 0 36px 60px 0;"></div>
            <div class="center" style="background-color: #0032c1;width: 108px;line-height: 60px;font-size: 17px">{{`${dataList[1].text}: ${dataList[1].num}`}}</div>
            <div class="right" style="border-color: #0032c1 transparent transparent transparent;border-width: 60px 36px 0 0;"></div>
        </div>
<!--        <div class="item" style="height: 88px">-->
<!--            <div class="left" style="border-color: transparent #002390 transparent transparent;border-width: 0 54px 88px 0;"></div>-->
<!--            <div class="center" style="background-color: #002390">{{`${dataList[2].text}: ${dataList[2].num}`}}</div>-->
<!--            <div class="right" style="border-color: #002390 transparent transparent transparent;border-width: 88px 54px 0 0;"></div>-->
<!--        </div>-->

        <div class="item" style="height: 88px">
            <div class="center" style="position: relative;width: 67px">
                <div class="left" style="border-color: transparent #002390 transparent transparent;border-width: 0 54px 88px 0;position: absolute;left: -20.5px;top: 0;"></div>
                <div class="text" style="position: absolute;z-index: 3;top: 15px;width:65px;font-size: 14px;left:50%;transform:translateX(-50%);">{{`${dataList[2].text}: ${dataList[2].num}`}}</div>
                <div class="right" style="border-color: #002390 transparent transparent transparent;border-width: 88px 54px 0 0;position: absolute;right: -20.5px;top: 0"></div>
            </div>
        </div>
    </div>
</template>
<script>
    Vue.component('com-funnel', {
        template: '#com-funnel',
        props: {
            dataList: {
                type: Array,
                default: () => []
            }
        },
        computed: {

        },
        data() {
            return {
                data: '',
            };
        },
        created() {
            // this.data = JSON.parse(JSON.stringify(this.count));
        },
        methods: {

        },
    });
</script>
