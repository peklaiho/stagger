<?php
namespace Stagger;

use Symfony\Component\Yaml\Yaml;

class Parser
{
    public function parse(string $id): Site
    {
        $dir = SITES_DIR . $id . '/';
        if (!is_readable($dir)) {
            exit_with_error("Site $id not found or directory $dir is not readable.");
        }

        $sitefile = $dir . 'site.yml';
        if (!is_readable($sitefile)) {
            exit_with_error("File site.yml does not exist or is not readable.");
        }

        // Parse site.yml
        try {
            $info = Yaml::parse(file_get_contents($sitefile));
        } catch (\Exception $ex) {
            exit_with_error("Unable to parse site.yml: " . $ex->getMessage());
        }

        // Check that required info is present
        $required = ['name', 'url'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $info)) {
                exit_with_error("File site.yml does not contain required key $key.");
            }
        }

        // Create site object
        $site = new Site($id, $info['name'], $info['url']);

        // Optional info
        $site->description = $info['description'] ?? null;
        $site->lang = $info['lang'] ?? null;

        // Favicon
        if (array_key_exists('icon', $info)) {
            $iconfile = $dir . $info['icon'];
            if (!is_readable($iconfile)) {
                exit_with_error("File " . $info['icon'] . " is not readable.");
            }

            $site->icon = [
                'name' => $info['icon'],
                'type' => mime_content_type($iconfile),
                'data' => file_get_contents($iconfile)
            ];
        }

        // Templates
        $site->templates = $this->readTemplates($dir . 'templates/');

        // Styles and scripts
        $site->css = $this->readCssJs($dir . 'css/', $info['css'] ?? []);
        $site->js = $this->readCssJs($dir . 'js/', $info['js'] ?? []);

        // Read pages
        $site->pages = $this->readPages($dir . 'pages/', $info['pages']);
        if (empty($site->pages)) {
            exit_with_error("No pages found, at least one page is required.");
        }

        // Read menu
        $site->menu = $info['menu'] ?? [];
        foreach ($site->menu as $menupage) {
            if (!array_key_exists($menupage, $site->pages)) {
                exit_with_error("Menu references page $menupage that does not exist.");
            }
        }

        // Css classes
        $site->cssClasses = $info['classes'] ?? [];

        return $site;
    }

    private function readCssJs(string $dir, array $filenames): array
    {
        $files = [];

        if (!file_exists($dir)) {
            return $files;
        }

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
        $homepage = true;

        if (!file_exists($dir)) {
            return $pages;
        }

        foreach ($pagenames as $id) {
            $pagedir = $dir . $id . '/';
            $infofile = $pagedir . 'page.yml';
            $contfile = $pagedir . 'page.md';

            if (!is_readable($infofile)) {
                exit_with_error("File page.yml for page $id is not readable.");
            } elseif (!is_readable($contfile)) {
                exit_with_error("File page.md for page $id is not readable.");
            }

            // Parse page.yml
            try {
                $info = Yaml::parse(file_get_contents($infofile));
            } catch (\Exception $ex) {
                exit_with_error("Unable to parse page.yml: " . $ex->getMessage());
            }

            // Check that required info is present
            $required = ['name'];
            foreach ($required as $key) {
                if (!array_key_exists($key, $info)) {
                    exit_with_error("File page.yml does not contain required key $key.");
                }
            }

            $page = new Page(
                $id,
                $info['name'],
                file_get_contents($contfile),
                $homepage
            );

            $page->files = $this->readPageFiles($pagedir);

            $pages[$id] = $page;
            $homepage = false;
        }

        return $pages;
    }

    private function readPageFiles(string $dir): array
    {
        $files = [];

        foreach (glob($dir . '*') as $file) {
            $pi = pathinfo($file);

            // Skip over page.md and page.yml
            if ($pi['basename'] == 'page.md' ||
                $pi['basename'] == 'page.yml') {
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

    private function readTemplates(string $dir): array
    {
        $templates = [];

        if (is_readable($dir)) {
            $files = glob($dir . '*.twig');
            foreach ($files as $file) {
                $pi = pathinfo($file);
                $templates[$pi['filename']] = file_get_contents($file);
            }
        }

        // Check required templates
        $required = ['layout'];
        foreach ($required as $req) {
            if (!array_key_exists($req, $templates)) {
                exit_with_error("Required template $req.twig missing.");
            }
        }

        return $templates;
    }
}
