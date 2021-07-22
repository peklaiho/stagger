<?php
namespace Stagger;

use League\CommonMark\MarkdownConverter;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;

class Reader
{
    const PAGEFILE = 'page.md';
    const POSTFILE = 'post.md';

    private MarkdownConverter $markdown;

    public function __construct(MarkdownConverter $markdown)
    {
        $this->markdown = $markdown;
    }

    public function readFiles(string $dir, array $filenames): array
    {
        $all = $this->readDirectory($dir);

        $filtered = [];

        foreach ($all as $file) {
            if (in_array($file->filename, $filenames)) {
                $filtered[] = $file;
            }
        }

        return $filtered;
    }

    public function readDirectory(string $dir, ?Page $parent = null)
    {
        $results = [];

        if (!is_readable($dir)) {
            return $results;
        }

        // Page or Post?
        $page = null;
        if (is_readable($dir . self::PAGEFILE)) {
            $page = new Page(self::PAGEFILE);
        } elseif (is_readable($dir . self::POSTFILE)) {
            $page = new Post(self::POSTFILE);
        }

        if ($page) {
            $data = $this->markdown->convertToHtml(file_get_contents($page->filename));

            if ($data instanceof RenderedContentWithFrontMatter) {
                $page->content = $data->getContent();
                $info = $data->getFrontMatter();

                $page->title = $info['title'] ?? null;
                $page->home = boolval($info['home'] ?? false);

                $page->description = $info['description'] ?? null;
                $page->author = $info['author'] ?? null;
                $page->created = $info['created'] ?? null;
                $page->edited = $info['edited'] ?? null;

                if ($page instanceof Post) {
                    $page->category = $info['category'] ?? null;
                    $page->tags = $info['tags'] ?? [];
                }
            } else {
                $page->content = $data;
            }

            if ($parent) {
                $parent->addChild($page);
            }

            foreach (glob($dir . '*') as $file) {
                $basename = pathinfo($file)['basename'];

                if ($basename == self::PAGEFILE || $basename == self::POSTFILE) {
                    continue;
                }

                if (is_dir($file)) {
                    $this->readDirectory($dir . $basename . '/', $page);
                } else {
                    $page->addFile(new File($basename, file_get_contents($file)))
                }
            }
        }
    }
}