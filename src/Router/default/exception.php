<?php
function getCodeSnippet(array $excerpt): string
{
    $lines = '';
    foreach ($excerpt['excerpt'] as $i => $lineContent) {
        $lines .= "<span class='code-line'>" . rtrim($lineContent) . "</span>\n";
    }
    return $lines;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/lightfair.min.css"
        integrity="sha512-7XR4V1+vHjARBIMw1snyPoLn7d9U9gjBUhGAXVMRXRvXpfyjfmHiAnwxc9eP4imeh0gr7cBvDg9XO06OBj3+jA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            max-width: 1200px;
            margin: 0 auto;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        body>*:not(:last-child) {
            margin-bottom: 2rem;
        }

        header {
            padding: 4rem 0px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 2.5rem;
            color: #333;
            text-align: center;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        header h1 i {
            font-size: 2rem;
            color: #f14d49;
            position: relative;
            background-color: #fcdad9;
            border-radius: 50%;
            padding: 1rem;
            aspect-ratio: 1/1;
        }

        header .link a {
            font-size: 2rem;
            color: #333;
            text-decoration: none;
            cursor: pointer;
        }

        section {
            padding: 2.5rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        section>*:not(:last-child) {
            margin-bottom: 2rem;
        }

        .class {
            font-size: 1.2rem;
            font-weight: bold;
            color: #f14d49;
            background-color: #fcdad9;
            padding: 0.8rem;
            border-radius: 1.4rem;
        }

        .message {
            font-size: 1.5rem;
            font-weight: bold;
            margin-left: 5px;
        }

        .title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .file {
            font-size: 1.2rem;
            font-weight: bold;
            color: #666;
        }

        .file .line {
            color: #888;
            font-size: 1rem;
            margin-left: 0.3rem;
        }

        .excerpt {
            font-size: 1.2rem;
            tab-size: 4;
        }

        .excerpt .hljs-ln-n {
            font-family: Arial, sans-serif;
            margin-right: 2rem;
            padding: 2px;
        }


        .trace {
            font-size: 1.2rem;
            overflow-x: auto;
            padding: 1rem
        }

        .version {
            font-size: 1rem;
            color: #666;
            text-align: center;
        }
    </style>
</head>

<body>
    <header>
        <h1><i class="ri-error-warning-line"></i>Internal Server Error</h1>
        <span class="link"><a href="#"><i class="ri-github-fill"></i></a></span>
    </header>

    <section>
        <p><span class="class">Class : <?= $class ?></span></p>
        <p class="message"><?= $message ?></p>

    </section>


    <section>
        <p class="title">File</p>
        <p class="file">
            <?= $file ?> <span class="line">:<?= $line ?></span>
        </p>
        <pre class="excerpt"><code class="language-php"><?= getCodeSnippet($excerpt) ?></code></pre>
    </section>

    <section>
        <p class="title">Stack Trace</p>
        <pre class="trace"><?= $trace ?></pre>
    </section>

    <p class="version">PHP <?= PHP_VERSION ?></p>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script
        src="//cdnjs.cloudflare.com/ajax/libs/highlightjs-line-numbers.js/2.9.0/highlightjs-line-numbers.min.js"></script>
    <script
        src="//cdn.jsdelivr.net/gh/TRSasasusu/highlightjs-highlight-lines.js@1.2.0/highlightjs-highlight-lines.min.js"></script>
    <script>
        hljs.configure({ tabReplace: '  ' })
        hljs.highlightAll();
        hljs.initLineNumbersOnLoad({
            startFrom: <?= $excerpt['start'] ?>
        });
        hljs.highlightLinesAll([[{ start: <?= $excerpt['highlight'] ?>, end: <?= $excerpt['highlight'] ?>, color: '#fcdad9' }]]);
    </script>

</body>

</html>