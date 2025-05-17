@extends('layouts.auth')

@section('title', 'Login')

@section('content-auth')
<div class="flex items-center justify-center min-h-screen bg-gray-50 product-sans-regular form-login">

    <form class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md" action="{{ route('signin') }}" method="POST">
        @csrf
        <div class="flex justify-center mb-6">
            <img src="{{ asset('assets/images/logo HM-02.png') }}" alt="Sign In Image 2" class="h-32">
        </div>
        <div class="mb-6 w-64 md:w-96 mx-auto">
            <label for="email" class="block mb-2 product-sans-regular text-gray-900 dark:text-white">Email or phone number</label>
            <input type="email" value="{{ old('email') }}" id="email" name="email" class="shadow-lg bg-gray-50 border border-gray-300 text-gray-900  rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Enter your email or phone number" required />
            @if ($errors->has('email'))
            <span class="text-red-500 text-sm">{{ $errors->first('email') }}</span>
            @endif
        </div>
        <div class="mb-6">
            <label for="password" class="block mb-2 text-gray-900 dark:text-white">Password</label>
            <input type="password" value="{{ old('password') }}" id="password" name="password" class="shadow-lg bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Enter your password" required />
            @if ($errors->has('password'))
            <span class="text-red-500 text-sm">{{ $errors->first('password') }}</span>
            @endif
        </div>
        <div class="mb-6 flex justify-end">
            <a href="#" class="text-blue-600 hover:underline dark:text-blue-500">Forgot password?</a>
        </div>
        <button type="submit" class="text-white block w-full rounded-lg px-6 py-3 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Sign in</button>

        <div class="mt-6 text-center">
            <span class="text-gray-600">Or sign in with</span>
            <div class="mt-2">
                <a href="#" style="color: #000;" class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <img src="{{ asset('assets/images/icon/google.png') }}" alt="Google" class="w-4 h-4 mr-2">
                    Google
                </a>
            </div>
        </div>

        <div class="mt-4 text-center">
            <p class="text-gray-600">Don't have an account? <a href="{{ route('register') }}" class="text-blue-600 hover:underline dark:text-blue-500">Sign up</a></p>
        </div>
    </form>
</div>
@endsection