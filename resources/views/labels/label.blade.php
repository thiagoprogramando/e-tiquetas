<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style>

            body{
                margin:0;
                padding:0;
            }

            .page{

                width: {{ $config['paperWidth'] }}mm;
                height: {{ $config['paperHeight'] }}mm;

                padding-left: {{ $config['marginLeft'] }}mm;
                padding-top: {{ $config['marginTop'] }}mm;

                page-break-after:always;

            }

            .labels-table{

                border-collapse:separate;
                border-spacing: {{ $config['gapX'] }}mm {{ $config['gapY'] }}mm;

            }

            tr{
                page-break-inside: avoid;
            }

            .label{

                width: {{ $config['labelWidth'] }}mm;
                height: {{ $config['labelHeight'] }}mm;

                border:1px solid #000;
                box-sizing:border-box;

            }
        </style>

    </head>
    <body>

        @php
            $columns    = $layout->columns;
            $rowsLayout = $layout->rows;
            $perPage    = $columns * $rowsLayout;
            $total      = count($rows);
            $pages      = ceil($total / $perPage);
            $index      = 0;
        @endphp


        @for($p = 0; $p < $pages; $p++)
            <div class="page">
                <table class="labels-table">
                    @for($r = 0; $r < $rowsLayout; $r++)
                        <tr>
                            @for($c = 0; $c < $columns; $c++)
                                <td class="label">
                                    @if(isset($rows[$index])) @endif
                                </td>
                                @php $index++; @endphp
                            @endfor
                        </tr>
                    @endfor
                </table>
            </div>
        @endfor
    </body>
</html>