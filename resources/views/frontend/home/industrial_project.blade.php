@extends('frontend.index')

@section('content')
<div class="page__content">
    <!-- main content-->
    <article class="banner">
        <img class="banner__bg" src="{{ asset('images/thong-tin-chung-banner.jpg') }}" alt="" />
        <div class="banner__title">{{ __('app.investment_products') }}</div>
    </article>

    <section class=" pb-40 pt-40">
        <div class="container">
            <div class="row g-20">
                <div class="col-lg-3">
                    <form class="aside-form" action="{{ route('industrial_projects') }}" method="GET">
                        <div class="mb-4">
                            <div class="input-group">
                                <input class="form-control" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="{{ __('app.search_project') }}" />
                                <div class="input-group-text"><i class="fal fa-fw fa-search"></i></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="fw-600 text-uppercase mb-2">{{ __('app.project_name') }}</div>

                            <select class="form-select" name="project_id">
                                <option value="">{{ __('app.all') }}</option>
                                @foreach ($projects as $id => $project)
                                <option value="{{ $id }}" {{ request('project_id') == $id ? 'selected' : '' }}>{{ $project }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="button button--block" type="submit">{{ __('app.search') }}</button>
                    </form>
                </div>
                <div class="col-lg-9">
                    <div class="row g-20">
                        @if($industrialProjects->isEmpty())
                        <div class="col-12">
                            <p class="text-center">{{ __('app.no_matching_results') }}</p>
                        </div>
                        @endif
                        @foreach($industrialProjects as $item)
                        <div class="col-6 col-md-4 col-lg-6 col-xl-4">
                            <div class="project" style="box-shadow: 0 -3px 8px rgba(11, 11, 11, 0.1); overflow: hidden;">
                                <a class="project__frame" target="_blank" href="{{ $item->link }}">
                                    <img src="{{ $item->hotspots->url ?? asset('/images/project-1.jpg') }}" style="object-fit: scale-down" alt="" /></a>
                                <div class="project__body">
                                    <h3 class="project__title" data-tippy-content="{{$item->description}}"><a href="{{ $item->link }}" target="_blank">{{$item->description}}</a></h3>
                                    <ul class="project__info">
                                        <li>
                                            <span data-tippy-content="{{ __('app.project_under') }}:{{$item->project->name}}"> {{ __('app.project_under') }}: {{ $item->project->name }} </span>
                                        </li>
                                        <li>
                                            <span>{{ __('app.code') }}: {{ $item->code }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mt-40 mt-lg-50">
                        {{ $industrialProjects->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('bottom')

@endpush
