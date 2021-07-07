<style scoped>
    .chartNum {
        position: relative;
        display: flex;
        text-align: center;
        justify-content: center;
    }

    /*滚动数字设置*/
    .box-item {
        position: relative;
        font-size:42px;
        line-height: 41px;
        text-align: center;
        list-style: none;
        color: #FFFFFF;
        writing-mode: vertical-lr;
        text-orientation: upright;
        /*文字禁止编辑*/
        -moz-user-select: none;
        /*火狐*/
        -webkit-user-select: none;
        /*webkit浏览器*/
        -ms-user-select: none;
        /*IE10*/
        -khtml-user-select: none;
        /*早期浏览器*/
        user-select: none;
        /* overflow: hidden; */
    }

    /* 默认逗号设置 */
    .mark-item {
        width: 10px;
        margin-right: 5px;
        line-height: 10px;
        font-size:19px;
        position: relative;
    }

    .mark-item > span {
        position: absolute;
        width: 100%;
        bottom: 0;
        writing-mode: vertical-rl;
        text-orientation: upright;
    }

    /*滚动数字设置*/
    .number-item {
        width: 40px;
        list-style: none;
        margin-right: 5px;
        background: #0A54EA;
    }

    .number-item > span {
        position: relative;
        display: inline-block;
        height: 100%;
        writing-mode: vertical-rl;
        text-orientation: upright;
        overflow: hidden;
    }

    .number-item > span > i {
        font-style: normal;
        position: absolute;
        top: 11px;
        left: 50%;
        transform: translate(-50%, 0);
        transition: transform 1s ease-in-out;
        letter-spacing: 10px;
    }

    .number-item:last-child {
        margin-right: 0;
    }
    .end-datetime{
        position: absolute;
        left: 0;
        bottom: -25px;
        color: #959595;
        font-size: 12px;
    }

</style>
<template id="com-count-up">
    <div class="com-count-up">
        <div class="chartNum">
            <div class="box-item">
<!--                <li :class="{'number-item': !isNaN(item) }" v-for="(item,index) in orderNum" :key="index">-->
<!--                    <span v-if="!isNaN(item)">-->
<!--                      <i ref="numberItem">0123456789</i>-->
<!--                    </span>-->
<!--                </li>-->
                <div :class="{'number-item': !isNaN(item) }" v-for="(item,index) in orderNum" :key="index" :style="(index+1)%3==0 ? 'margin-right:14px':''">
                    <span>{{item}}</span>
                    <div class="mark-item" v-if="(index+1)%3==0 && (index+1)!=9">
                        <span>,</span>
                    </div>
                </div>
            </div>
            <div class="end-datetime">{{endDateTime}}</div>
        </div>
    </div>
</template>
<script>
    Vue.component('com-count-up', {
        template: '#com-count-up',
        props: {
            count: '',
            end_datetime: ''
        },
        computed: {
            endDateTime(){
                return `截止时间${this.end_datetime}`;
            }
        },
        watch: {
            data: function(newVal) {
                this.toOrderNum(newVal);
            },
            orderNum: function(newVal) {
                // this.setNumberTransform();
            }
        },
        data() {
            return {
                orderNum: [], // 默认订单总数,
                data: '',
            };
        },
        created() {
            this.data = JSON.parse(JSON.stringify(this.count));
        },
        methods: {
            updateData() {
                setInterval(() => {
                    this.changeNumber(this.count);
                    setTimeout(() => {
                        this.toOrderNum(this.count);
                    }, 700);
                }, 10000);
            },
            changeNumber(num) {
                let half = Math.floor(num * Math.random());
                // console.log(num, half);
                if (!isNaN(half)) {
                    this.toOrderNum(half);
                }
            },
            // 处理数字
            toOrderNum(num) {
                num = num.toString();
                if (num.length < 9) {
                    num = "0" + num; // 如未满9位数，添加"0"补位
                    this.toOrderNum(num); // 递归添加"0"补位
                } else if (num.length === 9) {
                    this.orderNum = num.split("");
                } else {
                    console.log("超过9位数");
                }
            },
            // 设置文字滚动
            setNumberTransform() {

                this.$nextTick(() => {
                    let numberItems = this.$refs.numberItem;
                    this.numberArr = this.orderNum.filter(item => !isNaN(item));
                    // 结合CSS 对数字字符进行滚动
                    for (let index = 0; index < numberItems.length; index++) {
                        let elem = numberItems[index];
                        elem.style.transform = `translate(-50%, -${this.numberArr[index] *
                        10}%)`;
                    }
                })

            }
        },
    });
</script>
