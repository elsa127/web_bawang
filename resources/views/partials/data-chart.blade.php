{{-- Skala yang sama dipakai untuk seluruh kecamatan agar perbandingan tidak menyesatkan. --}}
<figure class="rounded-xl border border-slate-200 bg-white p-4">
    <figcaption class="font-semibold text-slate-800">{{ $chartTitle }}</figcaption>
    <p class="text-xs text-slate-500">{{ $chartUnit }}</p>
    @if($chartRows === [])
        <p class="py-6 text-sm">Data belum tersedia.</p>
    @else
        <svg viewBox="0 0 360 225" class="w-full" role="img" aria-label="{{ $chartTitle }} dalam {{ $chartUnit }}">
            @foreach([$chartMin, ($chartMin + $chartMax) / 2, $chartMax] as $tick)
                @php
$tickY = 180 - ($tick - $chartMin) / ($chartMax - $chartMin) * 140;
@endphp
                <line x1="44" y1="{{ $tickY }}" x2="330" y2="{{ $tickY }}" stroke="#e2e8f0" />
                <text x="37" y="{{ $tickY + 4 }}" text-anchor="end" font-size="11" fill="#64748b">{{ number_format($tick, 1, ',', '.') }}</text>
            @endforeach
            @foreach($chartSeries as $series)
                @php
$previousPoint = null;
@endphp
                @foreach($chartRows as $index => $row)
                    @php
                        $value = $row[$series['key']] ?? null;
                        $x = 65 + $index * (240 / max(1, count($chartRows) - 1));
                        $y = $value !== null ? 180 - ($value - $chartMin) / ($chartMax - $chartMin) * 140 : null;
                    @endphp
                    @if($value !== null)
                        @if($previousPoint !== null)
                            <line x1="{{ $previousPoint[0] }}" y1="{{ $previousPoint[1] }}" x2="{{ $x }}" y2="{{ $y }}" stroke="{{ $series['color'] }}" stroke-width="2" @if($loop->parent->index > 0) stroke-dasharray="5 3" @endif />
                        @endif
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="{{ $series['color'] }}"><title>{{ $row['district'] }} {{ $row['year'] }}: {{ $series['label'] }} {{ number_format($value, 3, ',', '.') }} {{ $chartUnit }}</title></circle>
                        <text x="{{ $x }}" y="{{ $y + ($loop->parent->index === 0 ? -10 : 17) }}" text-anchor="middle" font-size="10" fill="{{ $series['color'] }}">{{ number_format($value, 2, ',', '.') }}</text>
                        @php
$previousPoint = [$x, $y];
@endphp
                    @else
                        @php
$previousPoint = null;
@endphp
                    @endif
                @endforeach
            @endforeach
            @foreach($chartRows as $index => $row)
                <text x="{{ 65 + $index * (240 / max(1, count($chartRows) - 1)) }}" y="207" text-anchor="middle" font-size="12" fill="#475569">{{ $row['year'] }}</text>
            @endforeach
        </svg>
        <div class="flex flex-wrap gap-3 text-xs">
            @foreach($chartSeries as $series)
                <span class="inline-flex items-center gap-2"><span style="background:{{ $series['color'] }};width:10px;height:10px;border-radius:50%"></span>{{ $series['label'] }}</span>
            @endforeach
        </div>
    @endif
</figure>
