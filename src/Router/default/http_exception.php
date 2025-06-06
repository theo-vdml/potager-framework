<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Error <?= $code ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            height: 100vh;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .container {
            text-align: center;
            padding: 2rem;
        }

        .code {
            font-size: 5rem;
            color: #f14d49;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .separator {
            width: 60px;
            height: 4px;
            background-color: #f14d49;
            margin: 0 auto 1rem auto;
        }

        .message {
            font-size: 1.5rem;
            color: #333;
        }

        .light {
            width: 100vw;
            aspect-ratio: 1/1;
            position: fixed;
            left 0;
            top: 100%;
            background: #f14d49;
            border-radius: 50%;
            transform: translate(0%, -15%);
            filter: blur(100px) opacity(0.4);
            z-index: -1;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="code"><?= $code ?></div>
        <div class="separator"></div>
        <div class="message"><?= $message ?></div>
    </div>
    <div class="light"></div>
</body>

</html>