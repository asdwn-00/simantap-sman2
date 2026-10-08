@if ($paginator->hasPages())
    <style>
        .simantap-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 20px;
            font-size: 13px;
        }

        .simantap-pagination .halaman-info {
            margin: 0;
            color: #2B3674;
        }

        .simantap-pagination .halaman-links {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .simantap-pagination .halaman-tombol {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            padding: 8px 12px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            background: white;
            color: #2B3674;
            text-decoration: none;
            line-height: 1.4;
            white-space: nowrap;
        }

        .simantap-pagination a.halaman-tombol:hover,
        .simantap-pagination .halaman-aktif {
            background: #2B4885;
            border-color: #2B4885;
            color: white;
        }

        .simantap-pagination .halaman-nonaktif {
            background: #F8FAFC;
            color: #94A3B8;
        }
    </style>

    <nav class="simantap-pagination" aria-label="Navigasi halaman">
        <p class="halaman-info">
            Menampilkan {{ $paginator->firstItem() }}
            sampai {{ $paginator->lastItem() }}
            dari {{ $paginator->total() }} data
        </p>

        <div class="halaman-links">
            @if ($paginator->onFirstPage())
                <span class="halaman-tombol halaman-nonaktif" aria-disabled="true">
                    Sebelumnya
                </span>
            @else
                <a class="halaman-tombol" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    Sebelumnya
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="halaman-tombol">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="halaman-tombol halaman-aktif" aria-current="page">
                                {{ $page }}
                            </span>
                        @else
                            <a class="halaman-tombol" href="{{ $url }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="halaman-tombol" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Selanjutnya
                </a>
            @else
                <span class="halaman-tombol halaman-nonaktif" aria-disabled="true">
                    Selanjutnya
                </span>
            @endif
        </div>
    </nav>
@endif