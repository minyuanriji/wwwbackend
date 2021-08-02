<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: huangpan
 * Date: 2020-04-07
 * Time: 19:56
 */
?>
<template id="com-tinymce">
    <div :class="{fullscreen:fullscreen}" class="tinymce-container" :style="{width:containerWidth}">
        <textarea :id="tinymceId" class="tinymce-textarea" v-model="myValue"></textarea>
        <div class="editor-custom-btn-container">
            <com-attachment v-model="imgUrl" :multiple="false" :max="1">
                <el-tooltip effect="dark" content="建议尺寸200*200" placement="top">
                    <el-button style="margin-bottom: 10px;" size="mini">选择文件</el-button>
                </el-tooltip>
            </com-attachment>
        </div>
    </div>
</template>
<script src="https://cdn.jsdelivr.net/npm/tinymce-all-in-one@4.9.3/tinymce.min.js"></script>
<script>

    const plugins = ['lists advlist autoresize bbcode code image preview']
    const toolbar = ['fontsizeselect | undo redo | bold italic underline | bullist numlist | code | preview | alignleft aligncenter alignright alignjustify']

    Vue.component('com-tinymce', {
        template: '#com-tinymce',
        props: {
            id: {
                type: String,
                default: function () {
                    return (
                        'vue-tinymce-' + +new Date() + ((Math.random() * 1000).toFixed(0) + '')
                    )
                }
            },
            value: {
                type: String,
                default: ''
            },
            toolbar: {
                type: Array,
                required: false,
                default() {
                    return []
                }
            },
            menubar: {
                type: String,
                default: 'file edit insert view format table'
            },
            height: {
                type: [Number, String],
                required: false,
                default: 500
            },
            width: {
                type: [Number, String],
                required: false,
                default: 'auto'
            }
        },
        data() {
            return {
                hasChange: false,
                hasInit: false,
                tinymceId: this.id,
                fullscreen: false,
                languageTypeList: {
                    en: 'en',
                    zh: 'zh_CN',
                    es: 'es_MX',
                    ja: 'ja'
                },
                imgUrl: '',
                myValue: this.value,
            }
        },
        computed: {
            containerWidth() {
                const width = this.width
                if (/^[\d]+(\.[\d]+)?$/.test(width)) {
                    // matches `100`, `'100'`
                    return `${width}px`
                }
                return width
            }
        },
        watch: {
            value(val) {
                val = val.trim();
                if (!this.hasChange && this.hasInit || !val) {
                    this.$nextTick(() =>
                        window.tinymce.get(this.tinymceId).setContent(val || '')
                    )
                }
                this.myValue = val;
            },
            imgUrl() {
                if (this.imgUrl.trim().length === 0) return
                tinymce
                    .get(this.tinymceId)
                    .insertContent(`<img class="wscnph" src="${this.imgUrl}" >`)
                this.imgUrl = ''
            },
            myValue(newValue) {
                this.$emit("input", newValue);
            }
        },
        mounted() {
            this.initTinymce()
        },
        activated() {
            if (tinymce) {
                this.initTinymce()
            }
        },
        deactivated() {
            this.destroyTinymce()
        },
        destroyed() {
            this.destroyTinymce()
        },
        methods: {
            initTinymce() {
                const _this = this
                tinymce.init({
                    selector: `#${this.tinymceId}`,
                    language: this.languageTypeList['zh'],
                    height: this.height,
                    body_class: 'panel-body ',
                    object_resizing: false, // 是否禁用表格图片大小调整
                    toolbar: this.toolbar.length > 0 ? this.toolbar : toolbar, // 分组工具栏控件
                    menubar: this.menubar, // 菜单:指定应该出现哪些菜单
                    plugins: plugins, // 插件
                    end_container_on_empty_block: true, // enter键 分块
                    powerpaste_word_import: 'clean', // 是否保留word粘贴样式  clean | merge
                    code_dialog_height: 450, // 代码框高度 、宽度
                    code_dialog_width: 450,
                    advlist_bullet_styles: 'square', // 无序列表 有序列表
                    advlist_number_styles: 'default',
                    imagetools_cors_hosts: ['www.tinymce.com', 'codepen.io'],
                    default_link_target: '_blank',
                    link_title: false,
                    branding: false, // 隐藏右下角技术支持
                    fontsize_formats: '8px 10px 12px 14px 16px 18px 20px 24px 36px', // 字号选择
                    nonbreaking_force_tab: true, // inserting nonbreaking space &nbsp; need Nonbreaking Space Plugin
                    init_instance_callback: editor => {
                        if (_this.value) {
                            editor.setContent(_this.value)
                        }
                        _this.hasInit = true
                        editor.on('NodeChange Change KeyUp SetContent', () => {
                            this.hasChange = true
                            this.$emit('input', editor.getContent())
                        })
                    },
                    setup(editor) {
                        editor.on('FullscreenStateChanged', e => {
                            _this.fullscreen = e.state
                        })
                    }
                })
            },
            destroyTinymce() {
                const tinymce = tinymce.get(this.tinymceId)
                if (this.fullscreen) {
                    tinymce.execCommand('mceFullScreen')
                }

                if (tinymce) {
                    tinymce.destroy()
                }
            },
            setContent(value) {
                tinymce.get(this.tinymceId).setContent(value)
            },
            getContent() {
                tinymce.get(this.tinymceId).getContent()
            },
            imageSuccessCBK(arr) {
                const _this = this
                arr.forEach(v => {
                    tinymce
                        .get(_this.tinymceId)
                        .insertContent(`<img class="wscnph" src="${v.url}" >`)
                })
            }
        }
    });
</script>

<style scoped>
    .tinymce-container {
        position: relative;
        line-height: normal;
    }

    .tinymce-container >>> .mce-fullscreen {
        z-index: 10000;
    }

    .tinymce-textarea {
        visibility: hidden;
        z-index: -1;
    }

    .editor-custom-btn-container {
        position: absolute;
        right: 4px;
        top: 4px;
        /*z-index: 2005;*/
    }

    .fullscreen .editor-custom-btn-container {
        z-index: 10000;
        position: fixed;
    }

    .editor-upload-btn {
        display: inline-block;
    }
</style>