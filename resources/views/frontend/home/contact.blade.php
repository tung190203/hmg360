@extends('frontend.index')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="bg-white p-5 rounded shadow-sm">
                <h2 class="mb-4 text-primary">{{ __('app.contact') }}</h2>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{route('contact')}}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('app.full_name') }}</label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="{{ __('app.input_full_name') }}" value="{{ old('name', Auth::guard('guest')->check() ? Auth::guard('guest')->user()->name : '') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('app.email_address') }}</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="you@example.com" value="{{ old('email', Auth::guard('guest')->check() ? Auth::guard('guest')->user()->email : '') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">{{ __('app.phone_number') }}</label>
                        <input type="text" name="phone" id="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               placeholder="{{ __('app.input_phone_number') }}" value="{{ old('phone', Auth::guard('guest')->check() ? Auth::guard('guest')->user()->phone : '') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="project_industry_id" class="form-label">{{ __('app.fields_of_interest') }}</label>
                        <select name="project_industry_id" id="project_industry_id" class="form-select @error('project_industry_id') is-invalid @enderror">
                            <option value="">-- {{ __('app.choice_fields') }} --</option>
                            @foreach ($project_industries as $industries)
                                <option value="{{ $industries->id }}" {{ old('project_industry_id') == $industries->_id ? 'selected' : '' }}>{{ $industries->name }}</option>
                            @endforeach
                        </select>
                        @error('project_industry_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">{{ __('app.note') }}</label>
                        <textarea name="message" id="message" rows="4"
                                  class="form-control @error('message') is-invalid @enderror"
                                  placeholder="{{ __('app.what_are_you_interested_in') }}">{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary text-white">{{ __('app.sent_contact') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('bottom')

@endpush
