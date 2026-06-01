<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('cms.name') }} | Log in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('backend_assets/vendor/fontawesome/all.min.css') }}">
    {{-- <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">--}}

    <link rel="stylesheet" href="{{ asset('backend_assets/vendor/bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/css/adminlte.min.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <style>
        body.login-page {
            min-height: 100vh;
            background: #eef3f8;
        }

        .login-box {
            width: min(420px, calc(100vw - 32px));
        }

        .hmg-login-card {
            border: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 18px 45px rgba(20, 38, 64, .16);
        }

        .hmg-login-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 132px;
            padding: 28px 30px;
            background: #14263f;
        }

        .hmg-login-logo {
            display: block;
            width: 100%;
            max-width: 320px;
            height: auto;
        }

        .hmg-login-body {
            padding: 28px 30px 30px;
        }

        .hmg-login-title {
            margin-bottom: 22px;
            color: #253244;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
        }

        .hmg-login-body .form-control,
        .hmg-login-body .input-group-text,
        .hmg-login-submit {
            height: 42px;
        }

        .hmg-login-submit {
            border-radius: 4px;
            font-weight: 600;
        }

        @media (max-width: 575.98px) {
            .hmg-login-brand {
                min-height: 112px;
                padding: 24px;
            }

            .hmg-login-body {
                padding: 24px;
            }
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
                <p class="hmg-login-title">Sign in to your workspace</p>
                @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif

                <form action="{{ route('login') }}" method="post">
                    @csrf
                    <div class="input-group mt-3">
                        <input type="text" name="email" value="{{ old('email') }}" class="form-control"
                            placeholder="Email">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    @if($errors->has('email'))
                    <span class="text-danger">{{ $errors->first('email') }}</span>
                    @endif

                    <div class="input-group mt-3">
                        <input type="password" name="password" class="form-control" placeholder="Password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    @if($errors->has('password'))
                    <span class="text-danger">{{ $errors->first('password') }}</span>
                    @endif

                    <div class="row mt-3">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">
                                    Remember Me
                                </label>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block hmg-login-submit">Sign In</button>
                        </div>
                        <!-- /.col -->
                    </div>

                    <p class="mb-0 mt-3 text-center">
                        <a href="{{ route('password.request') }}">Forgot your password?</a>
                    </p>
                </form>
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>

    <script src="{{ asset('backend_assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('backend_assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

</body>

</html>
