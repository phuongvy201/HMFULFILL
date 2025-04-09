    @extends('layouts.auth')

    @section('title', 'Register')

    @section('content')
    <div class="flex items-center justify-center min-h-screen bg-gray-50 product-sans-regular form-login">

        <form action="{{ route('register') }}" method="POST" class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md my-10">
            @csrf
            <div class="flex justify-center mb-6">
                <img src="{{ asset('assets/images/logo HM-02.png') }}" alt="Sign In Image 2" class="h-32">
            </div>
            <div class="mb-6 mx-auto">
                <label for="email" class="block mb-2 product-sans-regular text-gray-900 dark:text-white">Email address <span class="text-red-500">(*)</span></label>
                <input type="email" id="email" name="email" class="shadow-lg bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Enter your email address" required />

                @if ($errors->has('email'))
                <span class="text-red-500 text-sm">{{ $errors->first('email') }}</span>
                @endif
            </div>


            <div class="mb-6 flex flex-col md:flex-row md:space-x-4">
                <div class="flex-1 mb-4 md:mb-0">
                    <label for="first_name" class="block mb-2 text-gray-900 dark:text-white">First Name <span class="text-red-500">(*)</span></label>
                    <input type="text" id="first_name" name="first_name" class="shadow-lg bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Enter your first name" required />

                    @if ($errors->has('first_name'))
                    <span class="text-red-500 text-sm">{{ $errors->first('first_name') }}</span>
                    @endif
                </div>
                <div class="flex-1">
                    <label for="last_name" class="block mb-2 text-gray-900 dark:text-white">Last Name <span class="text-red-500">(*)</span></label>
                    <input type="text" id="last-name" name="last_name" class="shadow-lg bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Enter your last name" required />

                    @if ($errors->has('last_name'))
                    <span class="text-red-500 text-sm">{{ $errors->first('last_name') }}</span>
                    @endif
                </div>
            </div>
            <div class="mb-6">
                <label for="phone" class="block mb-2 text-gray-900 dark:text-white">Phone number (Optional)</label>
                <input type="text" id="phone" name="phone" class="shadow-lg bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Enter your phone number" />

                @if ($errors->has('phone'))
                <span class="text-red-500 text-sm">{{ $errors->first('phone') }}</span>
                @endif
            </div>
            <div class="mb-6">
                <label for="password" class="block mb-2 text-gray-900 dark:text-white">Password <span class="text-red-500">(*)</span></label>
                <input type="password" id="password" name="password" class="shadow-lg bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Enter your password" required />

                @if ($errors->has('password'))
                <span class="text-red-500 text-sm">{{ $errors->first('password') }}</span>
                @endif
            </div>
            <div class="mb-6">
                <label for="password" class="block mb-2 text-gray-900 dark:text-white">Confirm Password <span class="text-red-500">(*)</span></label>
                <input type="password" id="password" name="password_confirmation" class="shadow-lg bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Enter your confirm password" required />

                @if ($errors->has('password_confirmation'))
                <span class="text-red-500 text-sm">{{ $errors->first('password_confirmation') }}</span>
                @endif
            </div>
            <div class="flex items-start mb-5">
                <div class="flex items-center h-5">
                    <input id="terms" type="checkbox" value="" class="w-4 h-4 border border-gray-300 rounded-sm bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800" required />
                </div>
                <label for="terms" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">I agree with the <a href="#" class="text-blue-600 hover:underline dark:text-blue-500">Term of Service</a> and <a href="#" class="text-blue-600 hover:underline dark:text-blue-500">Privacy Policy</a></label>
            </div>
            <button type="submit" class="text-white block w-full rounded-lg px-6 py-3 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Sign up</button>


            <div class="mt-4 text-center">
                <p class="text-gray-600">Already have an account? <a href="#" class="text-blue-600 hover:underline dark:text-blue-500">Sign in</a></p>
            </div>
        </form>
    </div>
    @endsection