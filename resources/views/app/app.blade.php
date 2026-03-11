@extends('app.layout')
@section('content')

    <div class="row mb-5 mt-5">
        <div class="col-12 col-sm-12 col-md-7 col-lg-7">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-none bg-transparent border border-secondary mb-3">
                        <div class="card-body text-secondary">
                            <h5 class="card-title text-secondary">Olá, {{ Auth::user()->maskName() }}</h5>
                            <p class="card-text">Você está associado ao Grupo {{ Auth::user()->company ? Auth::user()->company->name : Auth::user()->name }}.</p>
                        </div>
                    </div>
                </div>
                <div class="col-7">
                    <div class="card shadow-none bg-transparent border border-secondary mb-3">
                        <div class="card-body text-secondary">
                            <h5 class="card-title text-secondary">DASHBOARD</h5>
                            <div class="btn-group mb-3">
                                <a href="{{ route('layouts') }}" class="btn btn-outline-dark">Etiquetas</a>
                                <a href="{{ route('data') }}" class="btn btn-outline-dark">Dados</a>
                                <a href="{{ route('exports') }}" class="btn btn-outline-dark">Remessas</a>
                            </div>

                            <div class="demo-inline-spacing mt-4">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Remessas
                                        <span class="badge badge-center bg-dark rounded-pill">{{ Auth::user()->labels()->count() }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Templates
                                        <span class="badge badge-center bg-dark rounded-pill">{{ Auth::user()->layouts()->count() }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Base de Dados
                                        <span class="badge badge-center bg-dark rounded-pill">{{ Auth::user()->datas()->count() }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Usuários
                                        <span class="badge badge-center bg-dark rounded-pill">{{ Auth::user()->affiliates()->count() }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-5">
                    <div class="card shadow-none bg-transparent border border-secondary mb-3">
                        <div class="card-body text-secondary">
                            <h5 class="card-title text-center">GRÁFICO</h5>
                            <canvas id="usageChart" height="120"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-5 col-lg-5">
            <div class="card shadow-none bg-transparent border border-secondary mb-3">
                <div class="card-body text-secondary">
                    <h5 class="card-title text-center">CONFIGURAÇÕES</h5>
                    <div class="btn-group mb-3">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#modalCreatedUser" class="btn btn-outline-dark"><i class="ri-user-add-line"></i> Novo Usuário</button>
                    </div>

                    <div class="demo-inline-spacing mt-4">
                        <div class="list-group">
                            @foreach (Auth::user()->affiliates()->get() as $affiliate)
                                <div class="list-group-item list-group-item-action d-flex align-items-center cursor-pointer waves-effect">
                                    <img src="{{ $affiliate->avatar ? asset('storage/'.$affiliate->avatar) : asset('assets/img/avatars/man.png') }}" alt="" class="rounded-circle me-3" width="40">
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="user-info">
                                            <h6 class="mb-1 fw-normal">{{ $affiliate->maskName() }}</h6>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-status me-2 d-flex align-items-center">
                                                        <span class="badge badge-dot bg-success me-1"></span>
                                                        <small>{{ $affiliate->email }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <form action="{{ route('deleted-account') }}" method="POST" class="text-center btn-group confirm">
                                                @csrf
                                                <input type="hidden" name="uuid" value="{{ $affiliate->uuid }}">
                                                <button type="button" class="btn btn-sm btn-outline-dark btn-sm waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalUpdatedUser{{ $affiliate->uuid }}"><i class="ri-edit-box-line me-2"></i></button>
                                                <button type="submit" class="btn btn-sm btn-outline-dark btn-sm waves-effect waves-light"><i class="ri-delete-bin-line me-2"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="modalUpdatedUser{{ $affiliate->uuid }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-simple">
                                        <div class="modal-content p-3">
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            <div class="modal-body p-0">
                                                <div class="text-center mb-6">
                                                    <h4 class="mb-2">ALTERAÇÃO DE DADOS</h4>
                                                    <p>Preencha os dados!</p>
                                                </div>
                                                <form action="{{ route('updated-account', ['uuid' => $affiliate->uuid]) }}" method="POST" class="row g-2">
                                                    @csrf
                                                    <div class="col-12">
                                                        <div class="form-floating form-floating-outline">
                                                            <input type="text" name="name" id="name" class="form-control" placeholder="Nome" value="{{ $affiliate->name }}"/>
                                                            <label for="name">Nome</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-floating form-floating-outline">
                                                            <input type="text" name="cpfcnpj" id="cpfcnpj" class="form-control cpfcnpj" oninput="maskCpfCnpj(this)" placeholder="CPF ou CNPJ" value="{{ $affiliate->cpfcnpj }}"/>
                                                            <label for="cpfcnpj">CPF ou CNPJ</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-floating form-floating-outline">
                                                            <input type="text" name="email" id="email" class="form-control" placeholder="E-mail" value="{{ $affiliate->email }}"/>
                                                            <label for="email">E-mail</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 d-flex flex-wrap justify-content-center gap-4 row-gap-4 mt-3">
                                                        <button type="submit" class="btn btn-success">Confirmar</button>
                                                        <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCreatedUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-simple">
            <div class="modal-content p-3">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">DADOS DO USUÁRIO</h4>
                        <p>Preencha os dados!</p>
                    </div>
                    <form action="{{ route('created-account') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="text" name="name" id="name" class="form-control" placeholder="Nome"/>
                                <label for="name">Nome</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" name="cpfcnpj" id="cpfcnpj" class="form-control cpfcnpj" oninput="maskCpfCnpj(this)" placeholder="CPF ou CNPJ"/>
                                <label for="cpfcnpj">CPF ou CNPJ</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating form-floating-outline">
                                <input type="text" name="email" id="email" class="form-control" placeholder="E-mail"/>
                                <label for="email">E-mail</label>
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-wrap justify-content-center gap-4 row-gap-4 mt-3">
                            <button type="submit" class="btn btn-success">Confirmar</button>
                            <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

        const ctx = document.getElementById('usageChart');
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Templates',
                    'Etiquetas',
                    'Usuários',
                    'Remessas'
                ],
                datasets: [{
                    data: [
                        {{ $stats['templates'] }},
                        {{ $stats['etiquetas'] }},
                        {{ $stats['usuarios'] }},
                        {{ $stats['remessas'] }}
                    ],
                    backgroundColor: [
                        '#28c76f',
                        '#00cfe8',
                        '#ff9f43',
                        '#ea5455'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '65%'
            }
        });
    </script>
@endsection