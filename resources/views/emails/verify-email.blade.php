<!DOCTYPE html>
<html>

<head>
    <title>Verify Your Email Address</title>
</head>

<body>
    <h1>Hi {{ $user->first_name }},</h1>
    <p>Thank you for registering! Please verify your email address by clicking the link below:</p>
    <a href="{{ url('verify-email/' . $user->email_verification_at) }}">Verify Email</a>
    <p>Thank you!</p>
</body>

</html>