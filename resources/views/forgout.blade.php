@extends('layout')
@section('content')

    <div class="authentication-wrapper authentication-cover">
        <div class="authentication-inner row m-0">
            
            <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2" style="background-image: url({{ asset('assets/img/backgrounds/bg3.png') }}); background-size: cover; background-position: center; background-repeat: no-repeat;">
                {{-- <img src="{{ asset('assets/img/illustrations/auth-login-illustration-light.png') }}" class="auth-cover-illustration w-100" alt="auth-illustration" data-app-light-img="illustrations/auth-login-illustration-light.png" data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
                <img src="{{ asset('assets/img/illustrations/auth-cover-login-mask-light.png') }}" class="authentication-image" alt="mask" data-app-light-img="illustrations/auth-cover-login-mask-light.png" data-app-dark-img="illustrations/auth-cover-login-mask-dark.png" /> --}}
            </div>

            <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
                <div class="w-px-400 mx-auto pt-5 pt-lg-0">
                    <h4 class="mb-1">Esqueceu algo? 🔑</h4>
                    <p class="mb-5">Recupere seu acesso e volte aos benefícios</p>
                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                {!! $error !!}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endforeach
                    @endif
                    
                    @if (empty($code))
                        <form class="mb-5" action="{{ route('forgout-password') }}" method="POST">
                            @csrf
                            <div class="form-floating form-floating-outline mb-5">
                                <input type="text" class="form-control" id="email" name="email" placeholder="Qual o seu E-mail de acesso?" required/>
                                <label for="email">Qual o seu E-mail de acesso?</label>
                            </div>
                            <button class="btn btn-primary d-grid w-100">Cadastrar-me</button>
                        </form>
                    @else
                        <form class="mb-5" action="{{ route('forgout-password') }}" method="POST">
                            @csrf
                            <div class="form-floating form-floating-outline mb-5">
                                <input type="text" class="form-control" id="password" name="password" placeholder="Escolha uma nova senha:" required/>
                                <label for="password">Escolha uma nova senha:</label>
                            </div>
                             <div class="form-floating form-floating-outline mb-5">
                                <input type="text" class="form-control" id="password_confirmed" name="password_confirmed" placeholder="Confirme sua nova senha:" required/>
                                <label for="password_confirmed">Confirme sua nova senha:</label>
                            </div>
                            <button class="btn btn-primary d-grid w-100">Cadastrar-me</button>
                        </form>
                    @endif

                    <p class="text-center">
                        <span>Já tem uma Conta?</span>
                        <a href="{{ route('login') }}">
                            <span>Acesse agora!</span>
                        </a>
                    </p>
                </div>
            </div>

        </div>
    </div>
    
@endsection

       