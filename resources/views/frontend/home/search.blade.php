@extends('frontend.index')

@section('content')
    <div class="page__content">
        <nav class="breadcrumb-wrapper mt-4">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a class="link-unstyled" href="{{ route('home_page') }}">
                            <i class="fal fa-fw fa-home me-2"></i>
                            <span>{{ __('app.home') }}</span>
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ __('app.search') }}</li>
                </ol>
            </div>
        </nav>
        <section class="pt-10 pb-60 search-min-height">
            <div class="container">
                <h1 class="section__title">{{ __('app.search_results_for') }}: "{{ $key ?? '...' }}"</h1>
                <div class="mx-auto mt-4" style="max-width: 800px">
                    <form class="search-field" method="GET" action="{{ route('search') }}" id="search-form">
                        <div class="input-group">
                            <input class="form-control" type="text" name="keyword" value="{{ $key ?? '' }}"
                                placeholder="{{ __('app.search') }}" style="padding: 10px" />
                            <input type="hidden" name="type" value="{{ $type ?? 'all' }}">
                            <button class="input-group-text"><i class="fal fa-fw fa-search"></i></button>
                        </div>
                    </form>
                </div>

                @if(empty($groupedResults) || collect($groupedResults)->isEmpty())
                    <p class="text-center text-danger fs-5 pt-5 pb-5">
                        <i class="fas fa-search-minus me-2"></i>
                        {{ __('app.no_results_for_keyword', ['key' => $key ?? '...']) }}
                    </p>                
                @else
                    @foreach($groupedResults as $categoryName => $paginator)
                        @php
                            $ajax_type = $paginator->first()?->type ?? 'unknown'; 
                        @endphp

                        <div class="mt-40 mb-3" id="category-{{ $ajax_type }}">
                            <h2 class="section__subtitle section__subtitle--line text-primary">
                                {{ $categoryName }}
                                <span class="badge bg-secondary ms-2">{{ $paginator->total() }}</span>
                            </h2>
                        </div>
                        <div id="results-{{ $ajax_type }}">
                            @include('frontend.home.partials.search_results_ajax', ['results' => $paginator, 'type_name' => $categoryName])
                        </div>                        

                        @if(!$loop->last)
                            <hr class="mb-5 mt-5">
                        @endif
                    @endforeach
                @endif
            </div>
        </section>
    </div>
@endsection

@push('bottom')
    <style>
        .text-truncate-3-lines {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .search-min-height {
            min-height: 75vh;
        }
    </style>

<script>
    $(document).ready(function () {
        const keyword = @json($key ?? '');

        // QUAN TRỌNG: Sử dụng event delegation đúng cách
        $(document).on('click', '.pagination a', function (e) {
            e.preventDefault();
            
            const $link = $(this);
            const href = $link.attr('href');
            
            if (!href || $link.parent().hasClass('active') || $link.parent().hasClass('disabled')) {
                return; // Bỏ qua nếu là trang hiện tại hoặc disabled
            }

            // Tìm container chứa pagination này
            const $paginationWrapper = $link.closest('[id^="results-"]');
            const type = $paginationWrapper.attr('id').replace('results-', '');
            
            if (!type) {
                console.error('Không tìm thấy type từ container');
                return;
            }

            // Parse page number từ URL
            let page = 1;
            try {
                const url = new URL(href, window.location.href);
                // Thử tìm page theo nhiều pattern
                page = url.searchParams.get(type + '_page') 
                    || url.searchParams.get('page') 
                    || 1;
            } catch (err) {
                // Fallback: parse query string manually
                const match = href.match(/[?&](page|' + type + '_page)=(\d+)/);
                if (match) {
                    page = match[2];
                }
            }

            console.log('Pagination click:', { type, page, href });

            // Hiển thị loading state
            $paginationWrapper.css('opacity', '0.5').css('pointer-events', 'none');

            // AJAX request
            $.ajax({
                url: '{{ route("search") }}',
                type: 'GET',
                data: {
                    keyword: keyword,
                    ajax_type: type,
                    page: page,
                    [type + '_page']: page
                },
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (response) {
                    if (response.html) {
                        $paginationWrapper.html(response.html);
                        
                        // Scroll đến vị trí category
                        const $category = $('#category-' + type);
                        if ($category.length) {
                            $('html, body').animate({
                                scrollTop: $category.offset().top - 100
                            }, 500);
                        }
                    } else {
                        console.warn('Response không có HTML');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error,
                        response: xhr.responseText
                    });
                    alert('Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại!');
                },
                complete: function () {
                    $paginationWrapper.css('opacity', '1').css('pointer-events', 'auto');
                }
            });
        });
    });
</script>

@endpush