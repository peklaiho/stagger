<?php
namespace Stagger;

use League\CommonMark\MarkdownConverter;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;

class Reader
{
    const BLOGFILE = 'blog.md';
    const PAGEFILE = 'page.md';
    const POSTFILE = 'post.md';

    private MarkdownConverter $markdown;

    public function __construct(MarkdownConverter $markdown)
    {
        $this->markdown = $markdown;
    }

    public function readFiles(string $dir, array $filenames): array
    {
        $files = [];

        foreach ($filenames as $filename) {
            if (is_readable($dir . $filename)) {
                $files[] = new File($filename, file_get_contents($dir . $filename));
            }
        }

        return $files;
    }

    /**
     * Read a directory recursively. This returns a Page object
     * if the directory contains page.md or post.md. Otherwise
     * it returns an array of all the contents as File objects.
     */
    public function readDirectory(string $dir)
    {
        show_info("Reading directory: $dir");

        if (!is_readable($dir)) {
            return [];
        }

        $page = null;
        if (is_readable($dir . self::BLOGFILE)) {
            $pagefile = $dir . self::BLOGFILE;
            $page = new Blog(pathinfo($dir)['basename']);
        } elseif (is_readable($dir . self::PAGEFILE)) {
            $pagefile = $dir . self::PAGEFILE;
            $page = new Page(pathinfo($dir)['basename']);
        } elseif (is_readable($dir . self::POSTFILE)) {
            $pagefile = $dir . self::POSTFILE;
            $page = new Post(pathinfo($dir)['basename']);
        }

        if ($page) {
            $data = $this->markdown->convertToHtml(file_get_contents($pagefile));

            if ($data instanceof RenderedContentWithFrontMatter) {
                $page->content = $data->getContent();
                $info = $data->getFrontMatter();

                $page->title = $info['title'] ?? null;
                $page->home = boolval($info['home'] ?? false);

                $page->description = $info['description'] ?? null;
                $page->author = $info['author'] ?? null;
                $page->date = $info['date'] ?? null;
                $page->edited = $info['edited'] ?? null;

                if ($page instanceof Post) {
                    $page->category = $info['category'] ?? null;
                    $page->tags = $info['tags'] ?? [];
                }
            } else {
                $page->content = $data;
            }

            foreach (glob($dir . '*') as $file) {
                $basename = pathinfo($file)['basename'];

                if ($basename == self::PAGEFILE || $basename == self::POSTFILE) {
                    continue;
                }

                if (is_dir($file)) {
                    $child = $this->readDirectory($file . '/');

                    if ($child instanceof Page) {
                        $page->children[] = $child;
                        $child->parent = $page;
                    } else {
                        // Subdirectory that is not a page, ignored for now.
                        show_info("Ignoring directory: $file");
                    }
                } else {
                    $page->files[] = new File($basename, file_get_contents($file));
                }
            }

            return $page;
        } else {
            // Not a page, return an array of files
            $results = [];

            foreach (glob($dir . '*') as $file) {
                $basename = pathinfo($file)['basename'];

                if (is_dir($file)) {
                    $page = $this->readDirectory($file . '/');

                    if ($page instanceof Page) {
                        $results[] = $page;
                    } else {
                        // Subdirectory that is not a page, ignored for now.
                        show_info("Ignoring directory: $file");
                    }
                } else {
                    $results[] = new File($basename, file_get_contents($file));
                }
            }

            return $results;
        }
    }
}
