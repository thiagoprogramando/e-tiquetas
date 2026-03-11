@extends('app.layout')
@section('content')

    <div class="row mb-5 mt-5">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-3">
            <div class="card shadow-none bg-transparent border border-secondary">
                <div class="card-header">
                    <h5 class="card-title">BASE DE DADOS</h5>
                    <div class="card-subtitle mb-3">Dados importados ficam armazenados para uso e reutilização em diferentes layouts.</div>
                </div>
                <div class="body">
                    <div class="btn-group p-3">
                        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#addNewCCModal"><i class="ri-filter-3-line"></i> Pesquisar</button>
                        <a href="{{ route('create-data') }}" class="btn btn-outline-dark"><i class="ri-download-line"></i> Importar</a>
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
                                <th>NOME</th>
                                <th>IMPORTADO POR</th>
                                <th class="text-center">ORIGEM</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-center">OPÇÕES</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($data as $row)
                                <tr>
                                    <td>{!! $row->labelIcon() !!} <span class="fw-medium">{{ $row->name }}</span></td>
                                    <td>
                                        {{ $row->user->name }} <br>
                                        <span class="text-muted">Em {{ $row->created_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="text-center">
                                        {{ $row->labelMethod() }} <br>
                                    </td>
                                    <td class="text-center">
                                        {!! $row->labelStatus() !!} <br>
                                        <span class="text-muted">{{ $row->message }}</span>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('deleted-data') }}" method="POST" class="btn-group">
                                            @csrf
                                            <input type="hidden" name="uuid" value="{{ $row->uuid }}">
                                            <a href="{{ route('exports', ['layout' => null, 'data' => $row->uuid]) }}" class="btn btn-outline-dark" title="Gerar Remessa"><i class="ri-file-copy-2-line"></i></a>
                                            <button type="submit" class="btn btn-outline-dark" title="Excluir Base de dados"><i class="ri-delete-bin-line me-2"></i>Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addNewCCModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">PESQUISAR</h4>
                        <p>Filtre os parâmetros da sua busca!</p>
                    </div>
                    <form action="{{ route('data') }}" method="GET" class="row g-5">
                        @csrf
                        <input type="hidden" name="method" value="file">
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="name" name="name" id="name" class="form-control" placeholder="Nome do arquivou ou Lote"/>
                                <label for="name">Nome do arquivou ou Lote</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating form-floating-outline">
                                <input type="date_start" date_start="date_start" id="date_start" class="form-control" placeholder="Data Inicial"/>
                                <label for="date_start">Data Inicial</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating form-floating-outline">
                                <input type="date_end" date_end="date_end" id="date_end" class="form-control" placeholder="Data Final"/>
                                <label for="date_end">Data Final</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <select name="method" class="form-select" tabindex="0" id="method">
                                    <option value="  ">Todos</option>
                                    <option value="api">API</option>
                                    <option value="FILE">Excel</option>
                                    <option value="documento">PDF/Documentos</option>
                                </select>
                                <label for="method">Método:</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <select name="status" class="form-select" tabindex="0" id="status">
                                    <option value="  ">Todos</option>
                                    <option value="processing">Processando</option>
                                    <option value="failed">Falha</option>
                                    <option value="full">Completo</option>
                                </select>
                                <label for="status">Status:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="trash" class="form-check-input" id="trash" />
                                <label for="trash" class="text-heading">Pesquisar na Lixeira</label>
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" class="btn btn-success">Pesquisar</button>
                            <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection