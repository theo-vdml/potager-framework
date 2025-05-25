<?php

namespace Potager\Support;

class Dumper
{
    public static function dump(...$vars): void
    {
        $sourceInfo = self::getSource();
        $context = self::extractRelevantContext($sourceInfo['file'], $sourceInfo['line']);
        $rendered = self::renderIframeContent($sourceInfo, $context, array_map(fn($v) => self::formatVar($v), $vars));
        echo self::buildIframe($rendered);
    }

    protected static function getSource(): array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        foreach ($backtrace as $trace) {
            if (isset($trace['file'], $trace['line']) && !str_contains($trace['file'], __FILE__)) {
                return ['file' => $trace['file'], 'line' => $trace['line']];
            }
        }
        return ['file' => 'unknown', 'line' => 0];
    }

    protected static function extractRelevantContext(string $file, int $line): array
    {
        if (!is_readable($file)) {
            return ['lines' => [], 'startLine' => 0];
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $relevantLines = [];
        $startLine = $line - 1;
        $insideMultilineComment = false;

        // Scan upward for comments
        for ($i = $line - 2; $i >= 0; $i--) {
            $current = rtrim($lines[$i]);
            $trimmed = ltrim($current);

            // Detect end of multiline comment
            if (!$insideMultilineComment && str_ends_with($trimmed, '*/')) {
                $insideMultilineComment = true;
            }

            if ($insideMultilineComment) {
                array_unshift($relevantLines, $current);
                $startLine = $i;
                // Detect start of multiline comment
                if (str_starts_with($trimmed, '/*')) {
                    $insideMultilineComment = false;
                }
                continue;
            }

            if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '#')) {
                array_unshift($relevantLines, $current);
                $startLine = $i;
            } else {
                break;
            }
        }

        // Add the dump line
        $relevantLines[] = $lines[$line - 1];

        // Trim common indentation (tabs/spaces)
        $minIndent = null;
        foreach ($relevantLines as $lineText) {
            if (trim($lineText) === '')
                continue;
            preg_match('/^\s*/', $lineText, $m);
            $indent = strlen($m[0]);
            $minIndent = is_null($minIndent) ? $indent : min($minIndent, $indent);
        }

        if ($minIndent > 0) {
            $relevantLines = array_map(fn($l) => substr($l, $minIndent), $relevantLines);
        }

        return ['lines' => $relevantLines, 'startLine' => $startLine + 1];
    }


    protected static function formatVar(mixed $var): string
    {
        $isComplex = is_array($var) || is_object($var);
        $output = $isComplex
            ? json_encode($var, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : print_r($var, true);

        return htmlspecialchars($output, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected static function buildIframe(string $iframeContent): string
    {
        $escaped = htmlspecialchars($iframeContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $iframeId = 'dump-' . uniqid();

        return <<<HTML
<div id="{$iframeId}-container">
    <iframe id="{$iframeId}" style="width:100%; border:none; border-bottom: 1px solid #f14d49; background:#fff;" 
        sandbox="allow-scripts allow-same-origin" srcdoc="{$escaped}" scrolling="no"></iframe>
</div>
<script>
    window.addEventListener('message', function(e) {
        if (e.data && e.data.type === 'resize-iframe' && e.data.id === '{$iframeId}') {
            const iframe = document.getElementById('{$iframeId}');
            if (iframe) {
                iframe.style.height = e.data.height + 'px';
            }
        }
    });
</script>
HTML;
    }

    protected static function renderIframeContent(array $sourceInfo, array $context, array $dumpedVars): string
    {
        $file = htmlspecialchars($sourceInfo['file']);
        $dumpLine = (int) $sourceInfo['line'];
        $startLine = (int) $context['startLine'];
        $contextLines = $context['lines'];

        $code = '';
        foreach ($contextLines as $text) {
            $code .= htmlspecialchars($text) . "\n";
        }

        $contextHtml = '<pre><code class="language-php" data-ln-start-from="' . $startLine . '">' . $code . '</code></pre>';

        $varsHtml = '';
        foreach ($dumpedVars as $varHtml) {
            $varsHtml .= '<pre><code class="language-json">' . $varHtml . '</code></pre>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
<style>
@import url('https://fonts.googleapis.com/css2?family=Fira+Code&family=Inter:wght@400;600&display=swap');

:root {
    --bg: #f9f9fb;
    --text: #1e1e1e;
    --muted: #555;
    --highlight: #fff9c4;
    --border: #e1e4e8;
    --card: #ffffff;
    --accent: #4e89ff;
    --brand: #f14d49;
    --badge-bg: #fef3f2;
    --badge-border: #fadbd9;
}

body {
    font-family: 'Inter', sans-serif;
    margin: 0;
    padding: 2em;
    background: var(--bg);
    color: var(--text);
    line-height: 1.6;
    font-size: 0.95rem;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4em;
    background: var(--badge-bg);
    border: 1px solid var(--badge-border);
    color: var(--brand);
    font-weight: 600;
    border-radius: 999px;
    padding: 0.4em 1em;
    font-size: 0.8rem;
    margin-bottom: 1.5em;
}

pre {
    background: var(--card);
    border-radius: 12px;
    padding: 1.5em;
    overflow-x: auto;
    font-family: 'Fira Code', monospace;
    font-size: 0.85rem;
    color: var(--text);
    border: 1px solid var(--border);
    margin-bottom: 2em;
    transition: all 0.2s ease-in-out;
}

pre:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.section-title {
    font-weight: 600;
    font-size: 1rem;
    color: var(--muted);
    margin-bottom: 0.75em;
    margin-top: 2em;
    border-left: 4px solid var(--brand);
    padding-left: 0.5em;
}

details {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 10px;
    margin-bottom: 1.5em;
    overflow: hidden;
    transition: all 0.3s ease;
}

summary {
    padding: 1em;
    cursor: pointer;
    font-weight: 600;
    color: var(--brand);
    background: #fff0ef;
    transition: background 0.3s ease;
}

summary:hover {
    background: #ffe4e3;
}


.hljs-ln-n{
    padding-right: 1.5rem; 
}

.hljs-ln  {
    transition: .3s;
}

.hljs-ln tr:hover {
    background: var(--highlight);
}

</style>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlightjs-line-numbers.js/2.8.0/highlightjs-line-numbers.min.js"></script>
</head>
<body>
    <div class="badge">🐛 Debug Dump — <code>{$file}</code>:{$dumpLine}</div>

<div class="section-title">📄 Code Context</div>
{$contextHtml}

<div class="section-title">📦 Variable Dump</div>
{$varsHtml}


    <script>
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block);
            hljs.lineNumbersBlock(block);

            if (block.classList.contains('language-php')) {
                const start = parseInt(block.getAttribute('data-line-start') || '1');
                const offset = {$dumpLine} - start;
                const lines = block.parentNode.querySelectorAll('.hljs-ln-line');
                if (lines[offset]) {
                    lines[offset].classList.add('hljs-highlighted');
                }
            }
        });

        const height = document.documentElement.scrollHeight;
        parent.postMessage({ type: 'resize-iframe', height: height, id: window.frameElement.id }, '*');
    </script>
</body>
</html>
HTML;
    }
}