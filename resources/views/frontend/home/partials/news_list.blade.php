<div class="row g-3 g-sm-4">
    @forelse($posts as $item)
        <div class="col-6 col-lg-4">
            <div class="news">
                <a class="news__frame" href="{{ route('post_detail',['id' => $item->id, 'slug' => $item->slug, 'ref' => 'app.news']) }}">
                    <img src="{{$item->image}}" alt=""/>
                </a>
                <div class="news__body">
                    <div class="news__info">
                        <div class="news__time">
                            <i class="fal fa-clock me-2"></i>
                            <span>{{ \Carbon\Carbon::parse($item->published_at)->format('d/m/Y') }}</span>
                        </div>
                        <a class="news__like" href="javascript:void(0)"
                               data-id="{{ $item->id }}" data-type="App\Models\Post">
                                <i class="fas fa-fw fa-heart {{ $item->is_interested ? 'text-danger' : '' }}"></i>
                            </a>
                    </div>
                    <h3 class="news__title custom-desc">
                        <a href="{{ route('post_detail',['id' => $item->id, 'slug' => $item->slug, 'ref' => 'app.news']) }}" data-tippy-content="{{$item->name}}">
                            {{ $item->name }}
                        </a>
                    </h3>
                    <div class="news__desc">{{ $item->description }}</div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">{{ __('app.no_news_to_display') }}</p>
        </div>
    @endforelse
</div>
<nav class="d-flex justify-content-center mt-40 mt-lg-50" aria-label="Pagination navigation">
    {{ $posts->onEachSide(1)->links('pagination::bootstrap-4') }}
</nav>
