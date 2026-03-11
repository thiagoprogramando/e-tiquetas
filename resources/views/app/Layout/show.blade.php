@extends('app.layout')
@section('content')

    <link rel="stylesheet" href="{{ asset('assets/css/canvas.css') }}"/>

    <div class="row mt-4">
        <div class="col-12 col-sm-12 col-md-4 col-lg-4">
            <div class="card shadow-none bg-secondary-subtle">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">Elementos</h5>
                    </div>
                </div>
                <div class="card-body justify-content-between">
                    <button class="btn btn-sm btn-outline-dark mb-2" id="add-text-btn">
                        <i class="ri-t-box-line"></i> Adicionar Texto
                    </button>
                    <button class="btn btn-sm btn-outline-dark mb-2" id="add-qrcode-btn">
                        <i class="ri-qr-code-line"></i> Adicionar QrCode
                    </button>
                    <button class="btn btn-sm btn-outline-dark mb-2" id="add-barcode-btn">
                        <i class="ri-barcode-box-line"></i> Adicionar Código de Barras
                    </button>
                    <button class="btn btn-sm btn-outline-dark mb-2" id="add-image-btn">
                        <i class="ri-file-image-line"></i> Adicionar Imagem
                    </button>
                </div>
            </div>

            <div class="card mt-2">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">Propriedades</h5>
                    </div>
                </div>
                <div class="card-body" id="properties-panel">
                    <div class="row">
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" step="0.1" class="form-control" id="prop-x"/>
                                <label for="prop-x">V (mm):</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" step="0.1" class="form-control" id="prop-y"/>
                                <label for="prop-y">H (mm):</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" step="0.1" class="form-control" id="prop-width"/>
                                <label for="prop-width">L (mm):</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" step="0.1" class="form-control" id="prop-height"/>
                                <label for="prop-height">A (mm):</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-12">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="text" class="form-control" id="prop-content"/>
                                <label for="prop-content">Conteúdo:</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-5 col-lg-5">
                            <div class="form-floating form-floating-outline mb-4">
                                <select name="prop-mode" class="form-select" tabindex="0" id="prop-mode">
                                    <option value="static">Fixo</option>
                                    <option value="dynamic">Variável</option>
                                </select>
                                <label for="prop-mode">Modo:</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" class="form-control" id="prop-font-size"/>
                                <label for="prop-font-size">Fonte:</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4 col-lg-4">
                            <div class="form-floating form-floating-outline mb-4">
                                <select name="prop-text-align" class="form-select" tabindex="0" id="prop-text-align">
                                    <option value="left">Esquerdo</option>
                                    <option value="center">Centro</option>
                                    <option value="right">Direito</option>
                                    <option value="justify">Justificado</option>
                                </select>
                                <label for="prop-text-align">Alinhamento:</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-12">
                            <div class="form-floating form-floating-outline mb-4" id="barcode-type-wrapper">
                                <select name="prop-barcode-type" class="form-select" tabindex="0" id="prop-barcode-type">
                                    <option value="EAN13">EAN-13</option>
                                    <option value="C128">C128</option>
                                </select>
                                <label for="prop-barcode-type">Tipo de Código de Barras:</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-12 col-md-8 col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $layout->name }}</h5>
                        <small class="text-muted">
                            As alterações não são salvas automaticamente. Clique em <strong>Salvar</strong> para armazenar o layout.
                        </small>
                    </div>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <div id="label-canvas-wrapper">
                        <div id="label-canvas"></div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="button" title="Limpar" id="btn-clear-canvas" class="btn me-2 btn-outline-dark"><i class="ri-eraser-line"></i></button>
                    <button type="button" title="Salvar" id="btn-save-canvas" class="btn me-2 btn-outline-dark"><i class="ri-save-line"></i> Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="layout-uuid" value="{{ $layout->uuid }}">

    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    <script src="{{ asset('assets/js/editor.js') }}"></script>
    <script>
        window.editorConfig = @json($editorConfig);
        window.initialState = {
            elements: @json($initialElements)
        };

        document.addEventListener('DOMContentLoaded', function() {
            Editor.init(window.editorConfig, window.initialState);
        });
    </script>
@endsection