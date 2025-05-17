<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Topup Request Approved</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e1e1;
        }

        h1 {
            color: #28a745;
            text-align: center;
            font-size: 24px;
            margin-bottom: 25px;
        }

        .content p {
            margin: 10px 0;
            font-size: 15px;
            line-height: 1.5;
        }

        .content p strong {
            color: #000;
        }

        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #777;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 20px;
            }

            h1 {
                font-size: 20px;
            }

            .content p {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Top-up Request Approved</h1>

        <div class="content">
            <p>Dear <strong>{{ $transaction->user->first_name }}</strong>,</p>
            <p>Your top-up request has been <strong>approved</strong>.</p>
            <p><strong>Transaction Code:</strong> {{ $transaction->transaction_code }}</p>
            <p><strong>Amount:</strong> {{ number_format($transaction->amount, 2) }} VND</p>
            <p><strong>Method:</strong> {{ $transaction->method }}</p>
            <p><strong>Approved At:</strong> {{ $transaction->created_at->format('d M Y H:i') }}</p>
            <p>Your account balance has been successfully updated.</p>
            <p>Thank you for using our service!</p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>

</html>