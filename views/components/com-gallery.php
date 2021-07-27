<style>
.com-gallery .com-gallery-list {
    -webkit-flex-wrap: wrap;
    flex-wrap: wrap;
}

.com-gallery .com-gallery-item {
    width: 100px;
    height: 100px;
    border: 1px solid #e3e3e3;
    border-radius: 2px;
    margin-right: 10px;
    margin-bottom: 10px;
    position: relative;
}

.com-gallery .com-gallery-delete {
    position: absolute;
    right: -8px;
    top: -8px;
    padding: 4px 4px;
}

.com-gallery .com-gallery-img {
    max-width: 100%;
    max-height: 100%;
}

</style>
<template id="com-gallery">
    <div class="com-gallery">
        <!--<el-button @click="test">test</el-button>-->
        <div class="com-gallery-list" flex>
            <template v-for="(item, index) in defaultList">
                <div class="com-gallery-item" flex="main:center cross:center" :style="reversedStyle">
                    <el-button v-if="showDelete && item[urlKey ? urlKey : 'url']" class="com-gallery-delete"
                               size="mini" type="danger" icon="el-icon-close" circle
                               @click="deleted(item, index)"></el-button>
                    <img class="com-gallery-img" :src="item[urlKey ? urlKey : 'url']">
                </div>
            </template>
        </div>
    </div>
</template>
<script>
Vue.component('com-gallery', {
    template: '#com-gallery',
    props: {
        list: Array,
        urlKey: String,
        width: String,
        height: String,
        showDelete: Boolean,
        url: String
    },
    data() {
        return {};
    },
    created() {
    },
    computed: {
        reversedStyle() {
            return (this.height ? `height: ${this.height}; ` : '') + (this.width ? `width: ${this.width}; ` : '');
        },
        defaultList() {
            if (typeof this.url != 'undefined') {
                return [{
                    url: this.url
                }];
            } else {
                return this.list;
            }
        }
    },
    methods: {
        test() {
            console.log(this);
        },
        deleted(item, index) {
            this.$emit('deleted', item, index);
        },
    },
});
</script>
