<style>
    .com-image-upload {
        display: inline-block;
    }

    .com-image-upload .pic-box {
        width: 70px;
        height: 70px;
        border: 1px solid #ccc;
        cursor: pointer;
        background-color: #fff;
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
    }

    .com-image-upload .pic-box i {
        font-size: 22px;
        color: #909399;
    }

    .com-image-upload .pic-box .size-tip {
        line-height: 1.35;
        text-align: center;
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 12px;
        color: #fff;
        background: rgba(0, 0, 0, 0.2);
        letter-spacing: -1px;
    }

    .com-image-upload .image-delete {
        position: absolute;
        top: -10px;
        right: -10px;
        padding: 5px;
        visibility: hidden;
        z-index: 1;
    }

    .com-image-upload:hover .image-delete {
        visibility: visible;
    }

    .com-image-upload .image-delete i {
        font-size: 12px;
        color: #fff;
    }
</style>
<template id="com-image-upload">
    <div class="com-image-upload">
        <com-attachment v-model="url">
            <div class="pic-box" v-if="url" :style="'background-image: url('+url+');'">
                <div class="size-tip" v-if="cSizeTip">{{cSizeTip}}</div>
                <el-button @click.stop="imageDelete" class="image-delete" icon="el-icon-close" size="mini" circle type="danger"></el-button>
            </div>
            <div class="pic-box" v-else flex="main:center cross:center">
                <i class="el-icon-picture-outline"></i>
                <div class="size-tip" v-if="cSizeTip">{{cSizeTip}}</div>
            </div>
        </com-attachment>
    </div>
</template>
<script>
    Vue.component('com-image-upload', {
        template: '#com-image-upload',
        props: ['value', 'width', 'height'],
        data() {
            return {
                url: '',
            };
        },
        created() {
            this.url = this.value;
        },
        watch: {
            value: {
                handler(newVal, oldVal) {
                    this.url = newVal;
                },
            },
            url: {
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            },
        },
        computed: {
            cSizeTip() {
                if (!this.width && !this.height) {
                    return false;
                }
                return (this.width ? this.width : '') + ' × ' + (this.height ? this.height : '');
            },
        },
        methods: {
            imageDelete() {
                this.url = '';
            },
        },
    });
</script>
