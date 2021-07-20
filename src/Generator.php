<?php
namespace Stagger;

use League\CommonMark\MarkdownConverter;

class Generator
{
    private MarkdownConverter $markdown;

    public function __construct(MarkdownConverter $markdown)
    {
        $this->markdown = $markdown;
    }

    public function generate(Site $site, Theme $theme): void
    {
        $outdir = OUTPUT_DIR . $site->name . '/';

        $data = [];

        if ($site->lang) {
            $data['lang'] = $site->lang;
        }
        if ($site->description) {
            $data['description'] = $site->description;
        }
        if ($site->icon) {
            // TODO
        }

        $data['css'] = array_keys($site->css);
        $data['js'] = array_keys($site->js);

        foreach ($site->pages as $pagename => $content) {
            $pagedir = $outdir . $pagename . '/';

            if (!file_exists($pagedir)) {
                if (!@mkdir($pagedir)) {
                    exit_with_error("Unable to create directory $pagedir.");
                }
            }

            $html = $this->markdown->convertToHtml($content);
        }
    }
}
