{{-- Tabel laporan material: A = Vendor Raw Material, B = Raw Material --}}
@php
    $fmt = function ($value) { return \App\Support\MaterialReport::number($value); };
@endphp

@foreach($report['sections'] as $section)
    @php
        $dateCount = max(1, count($section['dates']));
        $colCount  = 12 + $dateCount; // NO, MATERIAL, SPEC, TGL DIPERLUKAN, QTY, EA, WEIGHT, TOTAL, MILL CERT, ACTUAL, DEVIASI, [tanggal...], REMARK
    @endphp

    <div class="mr-section">
        <div class="mr-section-title"><span class="mr-letter">{{ $section['letter'] }}</span> {{ $section['title'] }}</div>

        <div class="mr-scroll">
            <table class="mr-table">
                <thead>
                    <tr>
                        <th rowspan="2">NO</th>
                        <th rowspan="2">MATERIAL</th>
                        <th rowspan="2">SPEC</th>
                        <th rowspan="2">TGL DIPERLUKAN</th>
                        <th colspan="4">REQ</th>
                        <th rowspan="2">MILL<br>CERTIFICATE</th>
                        <th rowspan="2">ACTUAL</th>
                        <th rowspan="2">DEVIASI</th>
                        <th colspan="{{ $dateCount }}">{{ $section['incoming_title'] }}</th>
                        <th rowspan="2">REMARK</th>
                    </tr>
                    <tr>
                        <th>QTY</th>
                        <th>EA</th>
                        <th>WEIGHT</th>
                        <th>TOTAL</th>
                        @forelse($section['dates'] as $date)
                            <th title="{{ $date['full'] }}">{{ $date['label'] }}</th>
                        @empty
                            <th>&nbsp;</th>
                        @endforelse
                    </tr>
                </thead>
                <tbody>
                    @forelse($section['rows'] as $row)
                        <tr>
                            <td>{{ $row['no'] }}</td>
                            <td class="mr-left">{{ $row['name'] }}</td>
                            <td class="mr-left">{{ $row['spec'] }}</td>
                            <td>{{ $row['purchase_date'] }}</td>
                            <td>{{ $fmt($row['quantity']) }}</td>
                            <td>{{ $row['uom'] }}</td>
                            <td>{{ $fmt($row['weight']) }}</td>
                            <td>{{ $fmt($row['total_weight']) }}</td>
                            <td class="mr-mill">
                                @if(!empty($interactive))
                                    <input type="checkbox" class="form-check-input mr-mill-check" data-item-id="{{ $row['id'] }}" {{ $row['mill_cert'] ? 'checked' : '' }} title="Centang jika Mill Certificate sudah diterima" aria-label="Mill Certificate {{ $row['name'] }}">
                                @else
                                    {{ $row['mill_cert'] ? '✓' : '' }}
                                @endif
                            </td>
                            <td class="mr-actual">{{ $fmt($row['actual']) }}</td>
                            <td class="{{ \App\Support\MaterialReport::deviationClass($row['deviation']) }}">{{ $fmt($row['deviation']) }}</td>
                            @forelse($section['dates'] as $date)
                                <td>{{ isset($row['by_date'][$date['key']]) ? $fmt($row['by_date'][$date['key']]) : '' }}</td>
                            @empty
                                <td></td>
                            @endforelse
                            <td class="mr-remark">{{ $row['remarks'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="mr-empty" colspan="{{ $colCount }}">Belum ada material terdaftar pada tabel ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">{{ $section['summary_label'] }}</td>
                        <td>{{ $fmt($section['totals']['quantity']) }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ $fmt($section['totals']['total_weight']) }}</td>
                        <td>{{ $section['totals']['mill_cert'] }}/{{ count($section['rows']) }}</td>
                        <td>{{ $fmt($section['totals']['actual']) }}</td>
                        <td>{{ $fmt($section['totals']['deviation']) }}</td>
                        <td class="mr-percent" colspan="{{ $dateCount }}">{{ $section['percentage_label'] }}</td>
                        <td class="mr-percent">{{ \App\Support\MaterialReport::percent($section['totals']['percentage']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endforeach