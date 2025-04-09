<!DOCTYPE html>
<html>

<head>
    <title>Fulfill Page</title>
</head>

<body>
    <h1>Welcome to the Fulfill Page</h1>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>

</html>