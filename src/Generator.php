<?php
namespace Stagger;

use Twig\Environment;

class Generator
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
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
            $data = $page->getTwigData($site->getTwigData());

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
