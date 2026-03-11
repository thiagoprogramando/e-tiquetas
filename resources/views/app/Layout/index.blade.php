@extends('app.layout')
@section('content')

  <style>
    .label-preview{
        box-shadow:0 2px 8px rgba(0,0,0,0.15);
    }
  </style>

    <div class="row mt-5">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
            <div class="nav-align-top">
                <ul class="nav nav-pills flex-column flex-md-row gap-2 gap-lg-0">
                    <li class="nav-item">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#createModal" class="nav-link active waves-effect waves-light"><i class="ri-add-circle-line me-2"></i>Adicionar</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#filterModal" class="nav-link waves-effect waves-light"><i class="ri-filter-3-line me-2"></i>Filtrar</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="modal-onboarding modal fade animate__animated" id="createModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form action="{{ route('created-layout') }}" method="POST" class="modal-content text-center">
          @csrf
          <div class="modal-header border-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="modalCarouselControls" class="carousel slide pb-6 mb-2" data-bs-interval="false">
            <div class="carousel-indicators">
              <button type="button" data-bs-target="#modalCarouselControls" data-bs-slide-to="0" class="active"></button>
              <button type="button" data-bs-target="#modalCarouselControls" data-bs-slide-to="1"></button>
              <button type="button" data-bs-target="#modalCarouselControls" data-bs-slide-to="2"></button>
            </div>
            <div class="carousel-inner">
              <div class="carousel-item active">
                <div class="onboarding-media">
                  <div class="mx-2">
                    <img src="{{ asset('assets/img/illustrations/layout_start.png') }}" alt="girl-with-laptop-light" width="300" class="img-fluid"/>
                  </div>
                </div>
                <div class="onboarding-content">
                  <h4 class="onboarding-title text-body">Vamos construir seu Layout?</h4>
                  <div class="onboarding-info mb-4">
                    Nome e Papel, para começar!
                  </div>
                  <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="text" name="name" class="form-control" placeholder="Dê um nome:" id="name" value="{{ old('name') }}"/>
                        <label for="name">Dê um nome:</label>
                      </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <select name="paper_format" class="form-select" tabindex="0" id="paper_format">
                          <option value="A4" {{ old('paper_format') == 'A4' ? 'selected' : '' }}>A4 (210 × 297 mm)</option>
                          <option value="L" {{ old('paper_format') == 'L' ? 'selected' : '' }}>Carta (216 × 279 mm)</option>
                          <option value="ROLL" {{ old('paper_format') == 'ROLL' ? 'selected' : '' }}>Rolo (térmica)</option>
                          <option value="CUSTOM" {{ old('paper_format') == 'CUSTOM' ? 'selected' : '' }}>Personalizado</option>
                        </select>
                        <label for="paper_format">Escolha um Papel:</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carousel-item">
                <div class="onboarding-media">
                  <div class="mx-2">
                    <img src="{{ asset('assets/img/illustrations/layout_middle.png') }}" alt="boy-with-laptop-light" width="300" class="img-fluid"/>
                  </div>
                </div>
                <div class="onboarding-content">
                  <h4 class="onboarding-title text-body">Estamos quase lá!</h4>
                  <div class="onboarding-info mb-4">
                    Escolha as configurações baseadas na sua necessidade.
                  </div>
                  <div class="row d-none" id="custom">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="number" step="0.001" min="0" name="paper_width_mm" class="form-control" placeholder="Largura Papel (MM):" tabindex="0" id="paper_width_mm" value="{{ old('paper_width_mm') }}"/>
                        <label for="paper_width_mm">Largura Papel (MM):</label>
                      </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="number" step="0.001" min="0" name="paper_height_mm" class="form-control" placeholder="Altura Papel (MM):" tabindex="0" id="paper_height_mm" value="{{ old('paper_height_mm') }}"/>
                        <label for="paper_height_mm">Altura Papel (MM):</label>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="number" step="0.001" min="0" name="label_width_mm" class="form-control" placeholder="Largura Papel (MM):" tabindex="0" id="label_width_mm" value="{{ old('label_width_mm') }}"/>
                        <label for="label_width_mm">Largura Etiqueta (MM):</label>
                      </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="number" step="0.001" min="0" name="label_height_mm" class="form-control" placeholder="Altura Papel (MM):" tabindex="0" id="label_height_mm" value="{{ old('label_height_mm') }}"/>
                        <label for="label_height_mm">Altura Etiqueta (MM):</label>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="number" name="columns" class="form-control" placeholder="Colunas (Por página):" tabindex="0" id="columns" value="{{ old('columns') }}"/>
                        <label for="columns">Colunas (Por página):</label>
                      </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="number" name="rows" class="form-control" placeholder="Linhas (Por página):" tabindex="0" id="rows" value="{{ old('rows') }}"/>
                        <label for="rows">Linhas (Por página):</label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carousel-item">
                <div class="onboarding-media">
                  <div class="mx-2">
                    <img src="{{ asset('assets/img/illustrations/layout_advanced.png') }}" alt="girl-verify-password-light" width="300" class="img-fluid"/>
                  </div>
                </div>
                <div class="onboarding-content">
                  <h4 class="onboarding-title text-body">Avançado!</h4>
                  <div class="onboarding-info mb-4">
                    Altere essas configurações <b>apenas se necessário.</b>
                  </div>
                  <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="number" step="0.001" min="0" name="margin_top_mm" class="form-control" placeholder="Margem superior (MM)" tabindex="0" id="margin_top_mm" value="{{ old('margin_top_mm') }}"/>
                        <label for="margin_top_mm">Margem superior (MM)</label>
                      </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="number" step="0.001" min="0" name="margin_left_mm" class="form-control" placeholder="Margem esquerda (MM)" tabindex="0" id="margin_left_mm" value="{{ old('margin_left_mm') }}"/>
                        <label for="margin_left_mm">Margem esquerda (MM)</label>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="number" step="0.001" min="0" name="gap_x_mm" class="form-control" placeholder="Espaço horizontal (MM)" tabindex="0" id="gap_x_mm" value="{{ old('gap_x_mm') }}"/>
                        <label for="gap_x_mm">Espaço horizontal (MM)</label>
                      </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6">
                      <div class="form-floating form-floating-outline mb-4">
                        <input type="number" step="0.001" min="0" name="gap_y_mm" class="form-control" placeholder="Espaço vertical (MM)" tabindex="0" id="gap_y_mm" value="{{ old('gap_y_mm') }}"/>
                        <label for="gap_y_mm">Espaço vertical (MM)</label>
                      </div>
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 d-grid">
                      <button type="submit" class="btn btn-outline-dark">CONSTRUIR TEMPLATE!</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <a class="carousel-control-prev" href="#modalCarouselControls" role="button" data-bs-slide="prev">
              <i class="ri-arrow-left-double-line lh-1 me-1"></i><span>Anterior</span>
            </a>
            <a class="carousel-control-next" href="#modalCarouselControls" role="button" data-bs-slide="next">
              <span>Próximo</span><i class="ri-arrow-right-double-line lh-1 ms-1"></i>
            </a>
          </div>
        </form>
      </div>
    </div>

    <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-simple">
            <div class="modal-content p-3">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Filtros</h4>
                        <p>Escolha os dados!</p>
                    </div>
                    <form action="{{ route('layouts') }}" method="GET" class="row">
                        @csrf
                        <div class="col-sm-12 col-md-12 col-lg-12">
                          <div class="form-floating form-floating-outline mb-4">
                            <input type="text" name="name" class="form-control" placeholder="Nome | Título" tabindex="0" id="name" value="{{ old('name') }}"/>
                            <label for="name">Nome | Título</label>
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

    <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-6">
      @if ($layouts->count() < 1)
        <small class="text-light fw-medium">Você não tem nenhum Layout! <a href="#" data-bs-toggle="modal" data-bs-target="#createModal">Construa o seu primeiro Layout!</a></small>
      @else
        <small class="text-light fw-medium">Layouts disponíveis</small>
        <div class="accordion mt-4" id="accordionWithIcon">
          @foreach ($layouts as $layout)
            <div class="accordion-item">
              <h2 class="accordion-header d-flex align-items-center">
                <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionWithIcon-{{ $layout->uuid }}" aria-expanded="false"><i class="ri-price-tag-2-line ri-20px me-2"></i>{{ $layout->name }}</button>
              </h2>
              <div id="accordionWithIcon-{{ $layout->uuid }}" class="accordion-collapse collapse" style="">
                <div class="accordion-body">
                  <div class="row">
                    <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                      <div class="demo-inline-spacing mt-4">
                        <ul class="list-group">
                          <li class="list-group-item d-flex align-items-center">
                            <i class="ri-file-line ri-22px me-3"></i> {{ $layout->labelFormat() }} {{ $layout->paper_width_mm.' × '.$layout->paper_height_mm.'mm' }}
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                            <i class="ri-arrow-up-double-line ri-22px me-3"></i> {{ $layout->margin_top_mm }}
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                            <i class="ri-arrow-left-double-line ri-22px me-3"></i> {{ $layout->margin_left_mm }}
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                            <i class="ri-table-2 ri-22px me-3"></i> {{ $layout->columns.' × '.$layout->rows }}
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                            <i class="ri-expand-height-fill ri-22px me-3"></i> {{ $layout->gap_x_mm }}
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                            <i class="ri-expand-width-fill ri-22px me-3"></i> {{ $layout->gap_y_mm }}
                          </li>
                        </ul>
                      </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                      <div class="nav-align-top mt-4">
                        <ul class="nav nav-pills flex-column flex-md-row gap-2 gap-lg-0">
                            <li class="nav-item">
                                <a href="{{ route('layout', ['uuid' => $layout->uuid]) }}" class="btn btn-outline-dark waves-effect waves-light"><i class="ri-edit-box-line me-2"></i>Editar</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('exports', ['layout' => $layout->uuid]) }}" class="btn btn-outline-dark waves-effect waves-light"><i class="ri-file-copy-2-line"></i>Gerar Remessa</a>
                            </li>
                            <li class="nav-item">
                              <form action="{{ route('deleted-layout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="uuid" value="{{ $layout->uuid }}">
                                <button type="submit" class="btn btn-outline-dark waves-effect waves-light"><i class="ri-delete-bin-line me-2"></i>Excluir</button>
                              </form>
                            </li>
                        </ul>
                      </div>
                      <small class="text-light fw-medium mt-4">(Pré)visualização</small>
                      <div class="label-preview border mt-2" id="preview-{{ $layout->uuid }}" data-canvas='@json($layout->canvas_json)' style=" width: {{ $layout->label_width_mm * 5 }}px; height: {{ $layout->label_height_mm * 5 }}px; position: relative; background:white; overflow:hidden; border-radius:6px;"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <script src="{{ asset('assets/js/ui-modals.js') }}"></script>
    <script src="{{ asset('assets/js/preview.js') }}"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
          const paperSelect = document.getElementById('paper_format');
          const customFields = document.getElementById('custom');

          function toggleCustomFields() {
              if (paperSelect.value === 'CUSTOM') {
                  customFields.classList.remove('d-none');
              } else {
                  customFields.classList.add('d-none');
                  customFields.querySelectorAll('input').forEach(input => {
                      input.value = '';
                  });
              }
          }

          paperSelect.addEventListener('change', toggleCustomFields);
          toggleCustomFields();
      });
    </script>
@endsection