<?php
namespace Stagger;

use Symfony\Component\Yaml\Yaml;

class Parser
{
    public function parse(string $name): Site
    {
        $dir = SITES_DIR . $name . '/';
        if (!is_readable($dir)) {
            exit_with_error("Site $name not found or directory $dir is not readable.");
        }

        $sitefile = $dir . 'site.yml';
        if (!is_readable($sitefile)) {
            exit_with_error("File site.yml does not exist or is not readable.");
        }

        $info = Yaml::parse(file_get_contents($sitefile));

        $site = new Site();

        // Required info
        $required = ['name', 'url', 'theme'];
        foreach ($required as $key) {
            if (array_key_exists($key, $info)) {
                $site->$key = $info[$key];
            } else {
                exit_with_error("File site.yml does not contain required key $key.");
            }
        }

        // Pages
        $site->pages = $this->readPages($dir . 'pages/', $info['pages']);
        if (empty($site->pages)) {
            exit_with_error("No pages found, at least one page is required.");
        }

        // Optional info
        $site->description = $info['description'] ?? null;
        $site->icon = $info['icon'] ?? null;
        $site->lang = $info['lang'] ?? null;

        // Styles and scripts
        $site->css = $this->readCssJs($dir . 'css/', $info['css'] ?? []);
        $site->js = $this->readCssJs($dir . 'js/', $info['js'] ?? []);

        return $site;
    }

    private function readCssJs(string $dir, array $filenames): array
    {
        $files = [];

        foreach ($filenames as $filename) {
            $fn = $dir . $filename;
            if (!is_readable($fn)) {
                exit_with_error("File $filename is not readable.");
            }

            $files[$filename] = file_get_contents($fn);
        }

        return $files;
    }

    private function readPages(string $dir, array $pagenames): array
    {
        $pages = [];

        foreach ($pagenames as $pagename) {
            $pagedir = $dir . $pagename . '/';
            $pagefile = $pagedir . '/page.md';

            if (!is_readable($pagefile)) {
                exit_with_error("File page.md for page $pagename is not readable.");
            }

            $page = new Page();

            $page->name = $pagename;
            $page->content = file_get_contents($pagefile);
            $page->files = $this->readPageFiles($pagedir);

            $pages[] = $page;
        }

        return $pages;
    }

    private function readPageFiles(string $dir): array
    {
        $files = [];

        foreach (glob($dir . '*') as $file) {
            $pi = pathinfo($file);

            // Skip over main file
            if ($pi['basename'] == 'page.md') {
                continue;
            }

            // Skip if directory or not readable
            if (is_dir($file) || !is_readable($file)) {
                continue;
            }

            $files[$pi['basename']] = file_get_contents($file);
        }

        return $files;
    }
}
