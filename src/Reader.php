<?php
namespace Stagger;

use League\CommonMark\MarkdownConverter;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;

/**
 * Recursively reads directories from disk and converts
 * them to Blog, Page or Post objects if applicable.
 */
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

    /**
     * Read a file into a File object.
     */
    public function readSimpleFile(string $filename): File
    {
        if (!is_readable($filename)) {
            exit_with_error("File $filename is not readable.");
        }

        $basename = pathinfo($filename)['basename'];

        $file = new File($basename, file_get_contents($filename));
        $file->filetype = mime_content_type($filename);

        return $file;
    }

    /**
     * Read the given files from the given directory.
     */
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
     * Read a directory recursively and return a Blog, Page
     * or Post object if the directory contains blog.md, page.md
     * or post.md respectively. Otherwise return all the files
     * in the directory as an array of File objects.
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
                $page->image = $info['image'] ?? null;

                if ($page instanceof Post) {
                    $page->category = $info['category'] ?? null;
                    $page->tags = $info['tags'] ?? [];
                } elseif ($page instanceof Blog) {
                    $page->previewParagraphs = $info['preview_paragraphs'] ?? 2;
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
