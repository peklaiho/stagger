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
    private RssGenerator $rssGen;

    public function __construct(Environment $twig, Processor $processor, RssGenerator $rssGen)
    {
        $this->twig = $twig;
        $this->processor = $processor;
        $this->rssGen = $rssGen;
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

        // Write images
        if ($site->img) {
            $imgdir = $outdir . 'img/';
            $this->makedir($imgdir);

            foreach ($site->img as $file) {
                file_put_contents($imgdir . $file->filename, $file->content);
            }
        }

        // Write icon
        if ($site->icon) {
            file_put_contents($outdir . $site->icon->filename, $site->icon->content);
        }

        // Write site image
        if ($site->image) {
            file_put_contents($outdir . $site->image->filename, $site->image->content);
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

            // Generate RSS feed
            if ($site->rss) {
                $rss = $this->rssGen->generate($site, $page);
                $rssFile = $pagedir . 'rss.xml';
                show_info("Writing RSS feed: $rssFile");
                file_put_contents($rssFile, $rss);
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
