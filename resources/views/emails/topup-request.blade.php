<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Top-up Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            padding: 20px;
            line-height: 1.6;
            margin: 0;
        }

        .container {
            max-width: 600px;
            background: #fff;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }

        h1 {
            font-size: 22px;
            color: #007bff;
            margin-bottom: 16px;
            text-align: center;
        }

        .info {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .info p {
            margin: 5px 0;
        }

        .image-section {
            margin-top: 16px;
            text-align: center;
        }

        .image-section a {
            color: #007bff;
            text-decoration: underline;
            display: inline-block;
            margin-bottom: 8px;
        }

        img {
            margin-top: 8px;
            max-width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #888;
            text-align: center;
        }

        @media screen and (max-width: 480px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
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

            .image-section {
                margin-top: 12px;
            }

            .footer {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>New Top-up Request</h1>

        <div class="info">
            <p><strong>Transaction Code:</strong> {{ $transaction->transaction_code }}</p>
            <p><strong>Amount:</strong> {{ number_format($transaction->amount, 2) }} VND</p>
            <p><strong>Method:</strong> {{ $transaction->method }}</p>
            <p><strong>Status:</strong> {{ ucfirst($transaction->status) }}</p>
            <p><strong>User ID:</strong> {{ $transaction->user_id }}</p>
            <p><strong>Created At:</strong> {{ $transaction->created_at->format('d M Y H:i') }}</p>
        </div>

        <div class="image-section">
            <p><strong>Proof Image:</strong> <a href="{{ $transaction->note }}" target="_blank">View Image</a></p>

        </div>

        <div class="footer">
            <p>This is an automated message. Please review and process the request.</p>
        </div>
    </div>
</body>

</html>