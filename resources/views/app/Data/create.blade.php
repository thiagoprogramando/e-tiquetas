@extends('app.layout')
@section('content')

    <div class="row mb-5 mt-5">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-3">
            <div class="card shadow-none bg-transparent border border-secondary">
                <div class="card-header">
                    <h5 class="card-title">IMPORTAÇÃO</h5>
                    <div class="card-subtitle">Armazene dados via Excel ou API para utilizar com Layouts.</div>
                </div>
                <div class="card-body">
                    <div class="btn-group p-3">
                        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#addNewCCModal"> <i class="ri-file-excel-2-line"></i> Via Excel</button>
                        <button type="button" class="btn btn-outline-dark"> <i class="ri-webhook-line"></i> Via Api</button>
                    </div>
                </div>
            </div>
        </div>

        @if (isset($data))
            <div class="col-12 mb-3">
                <form action="{{ route('created-data') }}" method="POST" id="importForm" class="card">
                    @csrf
                    <input type="hidden" name="name" value="{{ $data['name'] ?? 'Importação'.now() }}">
                    <input type="hidden" name="method" value="{{ $data['method'] ?? 'file' }}">

                    @foreach($data['rows'] as $index => $row)
                        <input type="hidden" name="rows[{{ $index }}]" value='@json($row)'>
                    @endforeach

                    <div class="row">
                        <div class="col-12 col-md-6 offset-md-6 col-lg-4 offset-lg-8">
                            <div class="btn-group import-display text-end mt-3 mb-3" style="display:none;">
                                <button type="button" class="btn btn-dark">SELEÇÃO: <span class="selected-total">0</span></button>
                                <button type="submit" class="btn btn-outline-dark">IMPORTAR</button>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAll">
                                            </th>
                                            @foreach($data['headers'] as $header)
                                                <th>{{ strtoupper($header) }}</th>
                                            @endforeach
                                            <th>OPÇÕES</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        @foreach($data['rows'] as $index => $row)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_rows[]" value="{{ $index }}" class="row-checkbox">
                                                </td>
                                                @foreach($data['headers'] as $header)
                                                <td>
                                                    {{ $row[$header] ?? '-' }}
                                                </td>
                                                @endforeach
                                                <td>
                                                    <button type="button" onclick="btnDelete(this)" class="btn btn-outline-danger">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="text-center">
                                    <p>TOTAL DE <b>{{ $data['total'] }}</b> REGISTROS</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 offset-md-6 col-lg-4 offset-lg-8">
                            <div class="btn-group import-display text-end mt-3 mb-3" style="display:none;">
                                <button type="button" class="btn btn-dark">
                                    SELEÇÃO: <span class="selected-total">0</span>
                                </button>

                                <button type="submit" class="btn btn-outline-dark">
                                    IMPORTAR
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <div class="modal fade" id="addNewCCModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0">
                    <div class="text-center mb-6">
                        <h4 class="mb-2">IMPORTAR VIA EXCEL</h4>
                        <p>Arquivos longos podem levar maior tempo de processamento!</p>
                    </div>
                    <form action="{{ route('process-data') }}" method="POST" class="row g-5" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="method" value="file">
                        <div class="col-12">
                            <div class="form-floating form-floating-outline">
                                <input type="file" name="file" id="file" class="form-control" placeholder="Arquivo (xlsx, xls, csv)" accept=".xlsx,.xls,.csv" required/>
                                <label for="file">Arquivo (xlsx, xls, csv)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="futureAddress" />
                                <label for="futureAddress" class="text-heading">Arquivo longo (+200 Linhas)?</label>
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-wrap justify-content-center gap-4 row-gap-4">
                            <button type="submit" class="btn btn-success">Importar</button>
                            <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const selectAll = document.getElementById('selectAll');

        function getCheckboxes() {
            return document.querySelectorAll('.row-checkbox');
        }

        function getCheckedCheckboxes() {
            return document.querySelectorAll('.row-checkbox:checked');
        }

        function getImportDisplays() {
            return document.querySelectorAll('.import-display');
        }

        function updateTotals() {
            const total = getCheckedCheckboxes().length;
            document.querySelectorAll('.selected-total').forEach(el => {
                el.textContent = total;
            });
        }

        function updateImportDisplays() {
            const anyChecked = getCheckedCheckboxes().length > 0;
            getImportDisplays().forEach(el => {
                el.style.display = anyChecked ? 'flex' : 'none';
            });
            updateTotals();
        }

        selectAll?.addEventListener('change', function () {
            getCheckboxes().forEach(cb => cb.checked = this.checked);
            updateImportDisplays();
        });

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('row-checkbox')) {
                const all           = getCheckboxes();
                const checked       = getCheckedCheckboxes().length;
                selectAll.checked   = (checked === all.length && all.length > 0);
                updateImportDisplays();
            }
        });

    });

    function btnDelete(button) {

    const row = button.closest('tr');
    const checkbox = row.querySelector('.row-checkbox');

    if (checkbox) {
        checkbox.checked = false;
    }

    row.remove();

    const checked = document.querySelectorAll('.row-checkbox:checked').length;

    document.querySelectorAll('.selected-total').forEach(el => {
        el.textContent = checked;
    });

    document.querySelectorAll('.import-display').forEach(el => {
        el.style.display = checked > 0 ? 'flex' : 'none';
    });

}
</script>
@endsection