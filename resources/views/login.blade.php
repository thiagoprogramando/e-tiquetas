@extends('layout')
@section('content')

    <div class="authentication-wrapper authentication-cover">
        <div class="authentication-inner row m-0">
            
            <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2" style="background-image: url({{ asset('assets/img/backgrounds/bg.png') }}); background-size: cover; background-position: center; background-repeat: no-repeat;">
                {{-- <img src="{{ asset('assets/img/illustrations/auth-login-illustration-light.png') }}" class="auth-cover-illustration w-100" alt="auth-illustration" data-app-light-img="illustrations/auth-login-illustration-light.png" data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
                <img src="{{ asset('assets/img/illustrations/auth-cover-login-mask-light.png') }}" class="authentication-image" alt="mask" data-app-light-img="illustrations/auth-cover-login-mask-light.png" data-app-dark-img="illustrations/auth-cover-login-mask-dark.png" /> --}}
            </div>

            <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
                <div class="w-px-400 mx-auto pt-5 pt-lg-0">
                    <h4 class="mb-1">Bem-vindo(a) ao E-tiquetas! 👋</h4>
                    <p class="mb-5">Acesse sua conta para obter acesso aos benefícios</p>
                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                {!! $error !!}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endforeach
                    @endif

                    <form class="mb-5" action="{{ route('logon') }}" method="POST">
                        @csrf
                        <div class="form-floating form-floating-outline mb-5">
                            <input type="text" class="form-control" id="email" name="email" placeholder="Entre com seu E-mail" autofocus/>
                            <label for="email">E-mail</label>
                        </div>
                        <div class="mb-5">
                            <div class="form-password-toggle">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="password" id="password" class="form-control" name="password" placeholder="Entre com sua senha" aria-describedby="password"/>
                                        <label for="password">Senha</label>
                                    </div>
                                    <span class="input-group-text cursor-pointer"><i class="ri-eye-off-line"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-5 d-flex justify-content-between mt-5">
                            <div class="form-check mt-2"></div>
                            <a href="{{ route('forgout') }}" class="float-end mb-1 mt-2">
                                <span>Esqueceu sua senha?</span>
                            </a>
                        </div>
                        <button class="btn btn-primary d-grid w-100">Acessar</button>
                    </form>

                    <p class="text-center">
                        <span>Não tem uma Conta?</span>
                        <a href="{{ route('register') }}">
                            <span>Cadastre-se agora!</span>
                        </a>
                    </p>
                </div>
            </div>

        </div>
    </div>
    
@endsection

       