<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('cms.name') }} | Forgot password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('backend_assets/vendor/fontawesome/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/vendor/bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/css/adminlte.min.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <style>
        body.login-page { min-height: 100vh; background: #eef3f8; }
        .login-box { width: min(420px, calc(100vw - 32px)); }
        .hmg-login-card { border: 0; border-radius: 8px; overflow: hidden; box-shadow: 0 18px 45px rgba(20, 38, 64, .16); }
        .hmg-login-brand { display: flex; align-items: center; justify-content: center; min-height: 132px; padding: 28px 30px; background: #14263f; }
        .hmg-login-logo { display: block; width: 100%; max-width: 320px; height: auto; }
        .hmg-login-body { padding: 28px 30px 30px; }
        .hmg-login-title { margin-bottom: 12px; color: #253244; font-size: 18px; font-weight: 600; text-align: center; }
        .hmg-login-copy { color: #657284; text-align: center; }
        .hmg-login-body .form-control, .hmg-login-body .input-group-text, .hmg-login-submit { height: 42px; }
        .hmg-login-submit { border-radius: 4px; font-weight: 600; }
        @media (max-width: 575.98px) {
            .hmg-login-brand { min-height: 112px; padding: 24px; }
            .hmg-login-body { padding: 24px; }
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card hmg-login-card">
            <a href="{{ route('backend_dashboard') }}" class="hmg-login-brand">
                <img src="{{ asset('backend_assets/images/hmglogo.png') }}" alt="{{ config('cms.name') }}"
                    class="hmg-login-logo">
            </a>

            <div class="card-body login-card-body hmg-login-body">
                <p class="hmg-login-title">Forgot your password?</p>
                <p class="hmg-login-copy">Enter your email and we will send you a password reset link.</p>

                @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
                @endif

                <form action="{{ route('password.email') }}" method="post">
                    @csrf
                    <div class="input-group mt-3">
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                            placeholder="Email" autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    @if($errors->has('email'))
                    <span class="text-danger">{{ $errors->first('email') }}</span>
                    @endif

                    <button type="submit" class="btn btn-primary btn-block hmg-login-submit mt-3">
                        Send reset link
                    </button>

                    <p class="mb-0 mt-3 text-center">
                        <a href="{{ route('login') }}">Back to sign in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('backend_assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('backend_assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
