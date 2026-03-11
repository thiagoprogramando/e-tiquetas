@extends('app.layout')
@section('content')

    <div class="row mb-5 mt-5">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-3">
            <div class="card shadow-none bg-transparent border border-secondary">
                <div class="card-header">
                    <h5 class="card-title">GERAR REMESSA</h5>
                    <div class="card-subtitle mb-3">Os arquivos gerados ficam armazenados.</div>
                </div>
                <div class="body">
                    <div class="btn-group p-3">
                        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalCreated"><i class="ri-file-copy-2-line"></i> Gerar Remessa</button>
                        <button type="button" class="btn btn-outline-dark" onclick="location.reload(true)"><i class="ri-loop-left-line"></i> Atualizar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-3">
            <div class="card">
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>BASE DE DADOS / TEMPLATE</th>
                                <th>GERADO POR</th>
                                <th class="text-center">REMESSA</th>
                                <th class="text-center">OPÇÕES</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($labels as $row)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ $row->data->name }}</span> / <span class="fw-medium">{{ $row->layout->name }}</span>
                                    </td>
                                    <td>
                                        {{ $row->user->name }} <br>
                                        <span class="text-muted">Em {{ $row->created_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ $row->file_url }}" target="_blank">Arquivo</a>
                                    </td>
                                    <td>
                                       <form action="{{ route('deleted-export') }}" method="POST" class="text-center delete">
                                            @csrf
                                            <input type="hidden" name="uuid" value="{{ $row->uuid }}">
                                            <button type="submit" class="btn btn-outline-dark">
                                                <i class="ri-delete-bin-line me-2"></i> Excluir
                                            </button>
                                       </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                    {{ $labels->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCreated" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content p-3">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">GERAR REMESSA</h4>
                        <p>Escolha os dados!</p>
                    </div>
                    <form action="{{ route('render') }}" method="GET" target="_blank" class="row">
                        @csrf
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <select name="layout" class="form-select" tabindex="0" id="layout">
                                    <option value="  ">Escolha um Template</option>
                                    @foreach ($layouts as $layout)
                                        <option value="{{ $layout->uuid }}" @selected($layout->uuid == $layoutSelect)>{{ $layout->name }}</option>
                                    @endforeach
                                </select>
                                <label for="layout">Template:</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <select name="data" class="form-select" tabindex="0" id="data">
                                    <option value="  ">Escolha uma Base de dados</option>
                                    @foreach ($data as $data)
                                        <option value="{{ $data->uuid }}" @selected($data->uuid == $dataSelect)>{{ $data->name }}</option>
                                    @endforeach
                                </select>
                                <label for="data">Base de Dados:</label>
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" class="btn btn-success">Gerar</button>
                            <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection