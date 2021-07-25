<?php
namespace Stagger;

use Twig\Environment;

/**
 * Write the website to the output directory.
 */
class Generator
{
    private Environment $twig;
    private Processor $processor;

    public function __construct(Environment $twig, Processor $processor)
    {
        $this->twig = $twig;
        $this->processor = $processor;
    }

    /**
     * Write the given site to the output directory.
     */
    public function generate(Site $site): void
    {
        $outdir = OUTPUT_DIR . $site->name . '/';

        $this->makedir($outdir);

        // Write css
        if ($site->css) {
            $cssdir = $outdir . 'css/';
            $this->makedir($cssdir);

            foreach ($site->css as $file) {
                file_put_contents($cssdir . $file->filename, $file->content);
            }
        }

        // Write js
        if ($site->js) {
            $jsdir = $outdir . 'js/';
            $this->makedir($jsdir);

            foreach ($site->js as $file) {
                file_put_contents($jsdir . $file->filename, $file->content);
            }
        }

        // Write icon
        if ($site->icon) {
            $iconfile = $outdir . $site->icon->filename;
            file_put_contents($iconfile, $site->icon->content);
        }

        // Write pages
        foreach ($site->pages as $page) {
            $this->writePage($site, $page);
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

    /**
     * Write a Page to disk and recursively write its child pages also.
     */
    private function writePage(Site $site, Page $page): void
    {
        $outdir = OUTPUT_DIR . $site->name . '/';
        $pagedir = $outdir . $page->getPath(false);

        show_info("Writing {$page->getType()}: $pagedir");

        $this->makeDir($pagedir);

        // Data for Twig templates
        $sitedata = $site->getTwigData();
        $data = $page->getTwigData($sitedata);

        $this->renderAndWrite($site, $page, $pagedir . 'index.html', $data);

        // For blog, we also generate pages for different tags
        if ($page instanceof Blog) {
            $tags = $page->getChildTags();

            foreach ($tags as $tag) {
                $data['page_title'] = $page->title . ': ' . $tag;
                $data['posts'] = $page->getTwigDataForPosts($sitedata, $tag);
                $this->renderAndWrite($site, $page, $pagedir . "tag-$tag.html", $data);
            }
        }

        // Write other files
        foreach ($page->files as $file) {
            file_put_contents($pagedir . $file->filename, $file->content);
        }

        // Write children
        foreach ($page->children as $child) {
            $this->writePage($site, $child);
        }
    }

    /**
     * Render a Page using Twig and write it to disk.
     */
    private function renderAndWrite(Site $site, Page $page, string $filename, array $data): void
    {
        // Add CSS classes and syntax highlighting to HTML
        $data['content'] = $this->processor->process($site, $data['content']);

        // Render content based on page type
        $data['content'] = $this->twig->render($page->getType(), $data);

        // Render full layout of the site
        $html = $this->twig->render('layout', $data);

        // Write it to file
        file_put_contents($filename, $html);
    }
}
