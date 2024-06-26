<?php
namespace Stagger;

use Highlight\Highlighter;

/**
 * Class used to process the HTML code after it has been
 * rendered from Markdown, but before it is written to disk.
 */
class Processor
{
    private Highlighter $hl;

    public function __construct(Highlighter $hl)
    {
        $this->hl = $hl;
    }

    /**
     * Process the given HTML code. Add CSS classes to
     * elements and add syntax highlighting to code blocks.
     */
    public function process(Site $site, string $html): string
    {
        // Add css classes
        foreach ($site->cssClasses as $tag => $class) {
            $html = str_replace("<$tag>", "<$tag class=\"$class\">", $html);
        }

        // Add syntax highlighting
        $fn = function (array $matches) {
            $language = $matches[1];
            $code = $matches[2];

            $highlighted = $this->hl->highlight($language, htmlspecialchars_decode($code));

            return '<pre class="code" data-lang="' . strtoupper($language) . '"><code class="language-' . $language . '">' . $highlighted->value . '</code></pre>';
        };

        $html = preg_replace_callback('/<pre><code class="language-([^"]+)">(.+)<\/code><\/pre>/sU', $fn, $html);

        // Process normal code block, no syntax highlighting
        $fn = function (array $matches) {
            return '<pre class="code"><code>' . $matches[1] . '</code></pre>';
        };

        $html = preg_replace_callback('/<pre><code>(.+)<\/code><\/pre>/sU', $fn, $html);

        return $html;
    }
}
