<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top-up Request Rejected</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
            line-height: 1.6;
            margin: 0;
        }

        .container {
            max-width: 600px;
            background: #fff;
            margin: 0 auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        h1 {
            color: #dc3545;
            font-size: 22px;
            margin-bottom: 16px;
            text-align: center;
        }

        .info {
            background-color: #f1f1f1;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 14px;
        }

        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #777;
            text-align: center;
        }

        strong {
            color: #555;
        }

        @media screen and (max-width: 480px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
                margin: 0 auto;
                width: 100%;
            }

            h1 {
                font-size: 18px;
                margin-bottom: 12px;
            }

            .info {
                padding: 10px;
                font-size: 13px;
            }

            .footer {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Top-up Request Rejected</h1>
        <p>Dear <strong>{{ $transaction->user->first_name }}</strong>,</p>

        <p>We regret to inform you that your top-up request has been <strong>rejected</strong>. The transaction details are as follows:</p>

        <div class="info">
            <p><strong>Transaction Code:</strong> {{ $transaction->transaction_code }}</p>
            <p><strong>Amount:</strong> {{ number_format($transaction->amount, 2) }} VND</p>
            <p><strong>Method:</strong> {{ $transaction->method }}</p>
            <p><strong>Rejected At:</strong> {{ $transaction->updated_at->format('d M Y H:i') }}</p>
        </div>

        <p>If you have any questions or need assistance, please contact our support team.</p>

        <div class="footer">
            <p>Thank you for using our service.</p>
            <p>— The Support Team</p>
        </div>
    </div>
</body>

</html>