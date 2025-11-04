@extends('adminlte::page')

@section('title', 'Editor de Tutorial - OntoForAll')

@section('content_header')
    <h1>
        Editor de Tutorial
        <small>Edite o conteúdo da página de tutorial</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{route('home', app()->getLocale())}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{route('tutorial', app()->getLocale())}}">Tutorial</a></li>
        <li class="active">Editor</li>
    </ol>
@stop

@section('content')
    <div class="tutorial-editor">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Editor de Tutorial</h3>
            </div>
            <div class="box-body">
                <!-- Quill Editor -->
                <div id="quill-editor" style="min-height: 400px;"></div>
            </div>
            <div class="box-footer">
                <button
                    class="btn btn-primary"
                    id="save-btn"
                    onclick="saveTutorial()"
                >
                    <i class="fa fa-save"></i>
                    Salvar Tutorial
                </button>
                <a
                    href="{{ route('tutorial', app()->getLocale()) }}"
                    class="btn btn-default"
                >
                    <i class="fa fa-times"></i>
                    Cancelar
                </a>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- Quill Editor Styles -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        /* Estilos adicionais para o editor */
        .box {
            box-shadow: 0 1px 1px rgba(0,0,0,.1);
        }
        .tutorial-editor {
            padding: 20px 0;
        }
        .box-footer {
            padding: 10px;
        }
        .box-footer .btn {
            margin-right: 10px;
        }
        #quill-editor {
            min-height: 400px;
        }
        .ql-container {
            min-height: 400px;
            font-size: 16px;
        }
        .ql-editor {
            min-height: 400px;
        }
    </style>
@stop

@section('js')
    <!-- Quill Editor Script -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    <script>
        var quill;
        var initialDelta = {!! json_encode(optional($currentTutorial)->content) !!};
        var initialHtml = {!! json_encode(optional($currentTutorial)->html_content) !!};
        var saveUrl = "{{ route('tutorial.save', ['locale' => app()->getLocale()]) }}";
        var csrfToken = "{{ csrf_token() }}";
        var cancelUrl = "{{ route('tutorial', app()->getLocale()) }}";

        // Configuração do editor Quill
        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'script': 'sub'}, { 'script': 'super' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            [{ 'direction': 'rtl' }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'font': [] }],
            [{ 'align': [] }],
            ['link', 'image', 'video'],
            ['clean']
        ];

        // Inicializa o Quill quando o DOM estiver pronto
        document.addEventListener('DOMContentLoaded', function() {
            quill = new Quill('#quill-editor', {
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: 'Escreva o conteúdo do tutorial aqui...',
                theme: 'snow'
            });

            // Carrega o conteúdo inicial se existir
            if (initialDelta) {
                try {
                    quill.setContents(JSON.parse(initialDelta));
                    return;
                } catch (error) {
                    console.warn('Conteúdo inicial não está em formato Delta, tentando colar HTML.', error);
                }
            }

            if (initialHtml) {
                quill.clipboard.dangerouslyPasteHTML(initialHtml);
            } else if (initialDelta) {
                quill.setText(initialDelta);
            }
        });

        // Função para salvar o tutorial
        function saveTutorial() {
            var saveBtn = document.getElementById('save-btn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Salvando...';

            // Pega o HTML gerado pelo Quill
            var html = quill.root.innerHTML;

            // Pega o conteúdo Delta (formato interno do Quill para salvar)
            var delta = JSON.stringify(quill.getContents());

            axios.post(saveUrl, {
                content: delta,
                html_content: html,
                _token: csrfToken
            })
            .then(function(response) {
                if (response.data.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: response.data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.href = cancelUrl;
                    });
                }
            })
            .catch(function(error) {
                console.error('Erro ao salvar tutorial:', error);
                var errorMessage = 'Ocorreu um erro ao salvar o tutorial.';

                if (error.response && error.response.data && error.response.data.errors) {
                    errorMessage = Object.values(error.response.data.errors).flat().join('<br>');
                }

                Swal.fire({
                    title: 'Erro!',
                    html: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(function() {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fa fa-save"></i> Salvar Tutorial';
            });
        }
    </script>
@stop
