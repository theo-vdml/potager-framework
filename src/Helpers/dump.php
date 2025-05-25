<?php

// Include necessary libraries
function includeHighlightAssets(): string
{
    return <<<HTML
    <!DOCTYPE html>
    <html>
        <head>
        <style>
            pre {
                tab-size: 4;
            }
            .hljs-ln-line.hljs-ln-numbers {
                padding-right: 2rem;
            }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css" referrerpolicy="no-referrer" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/highlightjs-line-numbers.js/2.9.0/highlightjs-line-numbers.min.js"></script>
        <script src="//cdn.jsdelivr.net/gh/TRSasasusu/highlightjs-highlight-lines.js@1.2.0/highlightjs-highlight-lines.min.js"></script>
    </head>
    <body>
HTML;
}

function getDumpSource(): string
{
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

    foreach ($backtrace as $trace) {
        if (isset($trace['file'], $trace['line']) && !str_contains($trace['file'], __FILE__)) {
            return $trace['file'] . ':' . $trace['line'];
        }
    }

    return 'unknown source';
}

function getDumpCallSource(): string
{
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2] ?? null;

    if (!$backtrace || !isset($backtrace['file'], $backtrace['line'])) {
        return '';
    }

    $file = $backtrace['file'];
    $lineNumber = $backtrace['line'];

    if (!is_readable($file)) {
        return '';
    }

    $fileLines = file($file, FILE_IGNORE_NEW_LINES);
    $contextLines = [];

    $start = max(0, $lineNumber - 5);
    $end = min(count($fileLines), $lineNumber + 5);

    for ($i = $start; $i < $end; $i++) {
        $contextLines[] = $fileLines[$i];
    }

    $callSource = trim(join(PHP_EOL, $contextLines));

    return '<pre style="border-radius: 4px; font-family: monospace; overflow-x: auto; box-shadow: 0 0 2px 0 rgba(0,0,0,0.3);"><code class="language-php" data-highlight-ln="4" data-ln-start-from="' . $lineNumber - 3 . '">' . htmlspecialchars($callSource) . '</code></pre>';
}

function guessDumpType(mixed $var): string
{
    return (is_array($var) || is_object($var)) ? 'json' : 'php';
}

function renderDump(mixed $var): string
{
    $type = guessDumpType($var);

    if ($type === 'json') {
        $output = json_encode($var, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        $output = print_r($var, true);
    }

    $html = '<pre style="border-radius: 4px; font-family: monospace; overflow-x: auto; box-shadow: 0 0 2px 0 rgba(0,0,0,0.3);"><code class="language-' . $type . '">' . htmlspecialchars($output) . '</code></pre>';

    return $html;
}

function renderDumpContainer(array $vars): void
{
    static $isFirstCall = true;

    if ($isFirstCall) {
        echo includeHighlightAssets();
        $isFirstCall = false;
    }

    $source = getDumpSource();
    $callSource = getDumpCallSource();

    $containerId = 'dump-' . uniqid();

    echo '<div id="' . $containerId . '" style="margin: 1em 0; padding: 1em;">';
    echo '<p style="font-family: monospace; color: #888; margin-bottom: 0.5em;">Source :</p>';
    echo '<div style="font-family: monospace; color: #888; margin-bottom: 0.5em;">';
    echo htmlspecialchars($source);
    echo '</div>';

    if ($callSource) {
        echo $callSource;
    }

    echo '<p style="font-family: monospace; color: #888; margin-bottom: 0.5em;">Dumped values :</p>';
    foreach ($vars as $var) {
        echo renderDump($var);
    }

    echo '</div>';

    echo <<<HTML
        <script>
            (function() {
                const container = document.getElementById('$containerId');
                if (container) {
                    container.querySelectorAll('code').forEach(
                        el => {
                            hljs.highlightElement(el)
                            hljs.initLineNumbersOnLoad();
                            if (el.getAttribute('data-highlight-ln'))
                                hljs.highlightLinesElement(el, [{ start: el.getAttribute('data-highlight-ln'), end: el.getAttribute('data-highlight-ln'), color: '#FFDE21' }]);
                        }
                    );
                }
            })();
        </script>
        HTML;
}

function dump(...$vars)
{
    renderDumpContainer($vars);
    return $vars;
}

function dd(...$vars)
{
    dump(...$vars);
    exit;
}
