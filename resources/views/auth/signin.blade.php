<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sign In</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Arial', sans-serif;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .signin-form {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .signin-form img {
            height: 120px;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2d3748;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.25rem;
            background-color: #f7fafc;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.2);
        }

        .error {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
        }

        .remember-forgot input {
            margin-right: 0.5rem;
        }

        .remember-forgot a {
            color: #3182ce;
            text-decoration: none;
        }

        .remember-forgot a:hover {
            text-decoration: underline;
        }

        .signin-button {
            width: 100%;
            background-color: #3182ce;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .signin-button:hover {
            background-color: #2b6cb0;
        }

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #4a5568;
        }

        .signup-link a {
            color: #3182ce;
            text-decoration: none;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <form action="{{ route('signin') }}" method="POST" class="signin-form">
            @csrf
            <div class="flex justify-center">
                <img src="{{ asset('assets/images/logo HM-02.png') }}" alt="Sign In Image">
            </div>
            <div class="form-group">
                <label for="email">Email address <span style="color: red;">(*)</span></label>
                <input type="email" value="{{ old('email') }}" id="email" name="email" placeholder="Enter your email address" required>
                @if ($errors->has('email'))
                <span class="error">{{ $errors->first('email') }}</span>
                @endif
            </div>
            <div class="form-group">
                <label for="password">Password <span style="color: red;">(*)</span></label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                @if ($errors->has('password'))
                <span class="error">{{ $errors->first('password') }}</span>
                @endif
            </div>
            <div class="remember-forgot">
                <label for="remember">
                    <input id="remember" type="checkbox" value="">
                    Remember me
                </label>
                <a href="#">Forgot password?</a>
            </div>
            <button type="submit" class="signin-button">Sign in</button>
            <div class="signup-link">
                <p>Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
            </div>
        </form>
    </div>
</body>

</html>