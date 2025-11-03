<template>
    <div class="tutorial-editor">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Editor de Tutorial</h3>
            </div>
            <div class="box-body">
                <!-- Quill Editor -->
                <quill-editor
                    v-model="content"
                    ref="tutorialQuillEditor"
                    :options="editorOption"
                    @change="onEditorChange($event)"
                >
                </quill-editor>
            </div>
            <div class="box-footer">
                <button
                    class="btn btn-primary"
                    @click="saveTutorial"
                    :disabled="saving"
                >
                    <i class="fa fa-save"></i>
                    {{ saving ? 'Salvando...' : 'Salvar Tutorial' }}
                </button>
                <a
                    :href="cancelUrl"
                    class="btn btn-default"
                >
                    <i class="fa fa-times"></i>
                    Cancelar
                </a>
            </div>
        </div>

        <!-- Preview do HTML gerado -->
        <div class="box box-solid" v-if="showPreview">
            <div class="box-header with-border">
                <h3 class="box-title">Preview</h3>
            </div>
            <div class="box-body">
                <div v-html="htmlContent"></div>
            </div>
        </div>
    </div>
</template>

<script>
import { quillEditor } from 'vue-quill-editor'
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'

export default {
    name: 'TutorialEditor',
    components: {
        quillEditor
    },
    props: {
        initialContent: {
            type: String,
            default: ''
        },
        initialHtml: {
            type: String,
            default: ''
        },
        saveUrl: {
            type: String,
            required: true
        },
        cancelUrl: {
            type: String,
            required: true
        },
        csrfToken: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            content: this.initialContent,
            htmlContent: this.initialHtml,
            saving: false,
            showPreview: false,
            editorOption: {
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                        ['blockquote', 'code-block'],

                        [{ 'header': 1 }, { 'header': 2 }],               // custom button values
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
                        [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
                        [{ 'direction': 'rtl' }],                         // text direction

                        [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

                        [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                        [{ 'font': [] }],
                        [{ 'align': [] }],

                        ['link', 'image', 'video'],

                        ['clean']                                         // remove formatting button
                    ]
                },
                placeholder: 'Escreva o conteúdo do tutorial aqui...',
                theme: 'snow'
            }
        }
    },
    methods: {
        onEditorChange({ quill, html, text }) {
            this.htmlContent = html;
        },
        saveTutorial() {
            this.saving = true;

            // Pega o HTML gerado pelo Quill
            const html = this.$refs.tutorialQuillEditor.quill.root.innerHTML;

            // Pega o conteúdo Delta (formato interno do Quill para salvar)
            const delta = JSON.stringify(this.$refs.tutorialQuillEditor.quill.getContents());

            axios.post(this.saveUrl, {
                content: delta,
                html_content: html,
                _token: this.csrfToken
            })
            .then(response => {
                if (response.data.success) {
                    // Mostra mensagem de sucesso
                    this.$swal({
                        title: 'Sucesso!',
                        text: response.data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Redireciona para a página de tutorial
                        window.location.href = this.cancelUrl;
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao salvar tutorial:', error);
                let errorMessage = 'Ocorreu um erro ao salvar o tutorial.';

                if (error.response && error.response.data && error.response.data.errors) {
                    errorMessage = Object.values(error.response.data.errors).flat().join('<br>');
                }

                this.$swal({
                    title: 'Erro!',
                    html: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                this.saving = false;
            });
        }
    },
    mounted() {
        // Se houver conteúdo inicial em formato Delta, carrega no editor
        if (this.initialContent) {
            try {
                const delta = JSON.parse(this.initialContent);
                this.$refs.tutorialQuillEditor.quill.setContents(delta);
            } catch (e) {
                console.warn('Conteúdo inicial não está em formato Delta, usando como texto puro');
                this.$refs.tutorialQuillEditor.quill.setText(this.initialContent);
            }
        }
    }
}
</script>

<style scoped>
.tutorial-editor {
    padding: 20px 0;
}

.box-footer {
    padding: 10px;
}

.box-footer .btn {
    margin-right: 10px;
}

/* Estilo para o editor Quill */
::v-deep .quill-editor {
    min-height: 400px;
}

::v-deep .ql-container {
    min-height: 400px;
    font-size: 16px;
}

::v-deep .ql-editor {
    min-height: 400px;
}
</style>