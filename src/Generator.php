<?php
namespace Stagger;

use League\CommonMark\MarkdownConverter;
use Twig\Environment;

class Generator
{
    private MarkdownConverter $markdown;
    private Environment $twig;

    public function __construct(MarkdownConverter $markdown, Environment $twig)
    {
        $this->markdown = $markdown;
        $this->twig = $twig;
    }

    public function generate(Site $site): void
    {
        $outdir = OUTPUT_DIR . $site->id . '/';

        $this->makedir($outdir);

        // Write css
        if ($site->css) {
            $cssdir = $outdir . 'css/';
            $this->makedir($cssdir);

            foreach ($site->css as $fname => $fcontent) {
                file_put_contents($cssdir . $fname, $fcontent);
            }
        }

        // Write js
        if ($site->js) {
            $jsdir = $outdir . 'js/';
            $this->makedir($jsdir);

            foreach ($site->js as $fname => $fcontent) {
                file_put_contents($jsdir . $fname, $fcontent);
            }
        }

        // Write icon
        if ($site->icon) {
            $iconfile = $outdir . $site->icon['name'];
            file_put_contents($iconfile, $site->icon['data']);
        }

        // Write pages
        foreach ($site->pages as $page) {
            $pagedir = $outdir;
            if (!$page->homepage) {
                $pagedir .= $page->id . '/';
            }

            $this->makeDir($pagedir);

            // Data for Twig templates
            $data = $page->getTwigData($this->getCommonTwigData($site));

            // Render content using Markdown
            $data['content'] = $this->markdown->convertToHtml($page->content);

            // Render HTML using Twig
            $html = $this->twig->render('layout', $data);

            // Write it to file
            file_put_contents($pagedir . 'index.html', $html);

            // Write other files
            foreach ($page->files as $fname => $fcontent) {
                file_put_contents($pagedir . $fname, $fcontent);
            }
        }
    }

    /**
     * Return data for Twig templates that is common for all pages.
     */
    private function getCommonTwigData(Site $site): array
    {
        $data = [
            'site' => $site->name,
            'url' => $site->url
        ];

        if ($site->lang) {
            $data['lang'] = $site->lang;
        }
        if ($site->description) {
            $data['description'] = $site->description;
        }
        if ($site->icon) {
            $data['icon'] = $site->icon;
        }

        $data['css'] = array_keys($site->css);
        $data['js'] = array_keys($site->js);

        // Build menu
        $menu = [];
        foreach ($site->menu as $menupage) {
            $page = $site->pages[$menupage];
            $menu[$page->getLink(true)] = $page->name;
        }
        $data['menu'] = $menu;

        return $data;
    }

    /**
     * Create directory if it doesn't exist.
     */
    private function makeDir(string $dir): void
    {
        if (!file_exists($dir)) {
            if (!@mkdir($dir)) {
                exit_with_error("Unable to create directory $dir.");
            }
        }
    }
}
