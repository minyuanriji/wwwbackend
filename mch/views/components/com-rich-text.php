<style>
    .com-rich-text {
        line-height: normal;
    }

    .com-rich-text textarea,
    .com-rich-text .edui-editor {
        width: 100% !important;
    }
</style>
<template id="com-rich-text">
    <div class="com-rich-text">
        <textarea style="width: 100%" :id="id"></textarea>
        <com-attachment style="height: 0"
                        :simple="simpleAttachment"
                        :open-dialog="attachmentDialogVisible"
                        :multiple="!simpleAttachment"
                        @closed="attachmentClosed"
                        @selected="attachmentSelected">
        </com-attachment>
    </div>
</template>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/ueditor/ueditor.config.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/ueditor/ueditor.all.js"></script>
<script>
    Vue.component('com-rich-text', {
        template: '#com-rich-text',
        props: {
            value: null,
            simpleAttachment: false,
        },
        data() {
            return {
                attachmentDialogVisible: false,
                id: 'com-rich-text-' + (Math.floor((Math.random() * 10000) + 1)),
                ue: null,
                tempContent: this.value,
                isInputChange: false,
            };
        },
        watch: {
            value(newVal, oldVal) {
                if (!this.isInputChange && newVal) {
                    if (this.ue && this.ue.isReady === 1) {
                        this.ue.setContent(newVal);
                    } else {
                        this.tempContent = newVal;
                    }
                }
                if (this.isInputChange) {
                    this.isInputChange = false;
                }
            },
        },
        mounted() {
            this.loadUe();
        },
        methods: {
            attachmentClosed() {
                this.attachmentDialogVisible = false;
            },
            attachmentSelected(e) {
                if (e.length) {
                    let html = '';
                    for (let i in e) {
                        html += '<img src="' + e[i].url + '" style="max-width: 100%;">';
                    }
                    this.ue.execCommand('inserthtml', html);
                }
            },
            loadUe() {
                const vm = this;
                this.ue = UE.getEditor(this.id);
                this.ue.addListener('ready', editor => {
                    if (this.tempContent) {
                        this.ue.setContent(this.tempContent);
                    }
                });
                this.ue.addListener('keyup', editor => {
                    this.isInputChange = true;
                    this.$emit('input', this.ue.getContent());
                });
                this.ue.addListener('contentChange', editor => {
                    this.isInputChange = true;
                    this.$emit('input', this.ue.getContent());
                });
                let self = this;
                UE.registerUI('appinsertimage', (editor, uiName) => {
                    return new UE.ui.Button({
                        name: uiName,
                        title: '插入图片',
                        //添加额外样式，指定icon图标，这里默认使用一个重复的icon
                        cssRules: 'background-position: -381px 0px;',
                        onclick() {
                            self.ue = editor
                            vm.attachmentDialogVisible = true;
                        },
                    });
                });
            }
        },
    });
</script>

