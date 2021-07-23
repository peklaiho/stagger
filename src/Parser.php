<?php
namespace Stagger;

use Symfony\Component\Yaml\Yaml;

class Parser
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function parse(Site $site): void
    {
        $dir = SITES_DIR . $site->name . '/';
        if (!is_readable($dir)) {
            exit_with_error("Site not found or directory is not readable.");
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

        // Required fields
        $site->title = $info['title'] ?? null;
        $site->url = $info['url'] ?? null;

        // Optional metadata
        $site->description = $info['description'] ?? null;
        $site->author = $info['author'] ?? null;
        $site->lang = $info['lang'] ?? null;

        // Favicon
        if (array_key_exists('icon', $info)) {
            $iconfile = $dir . $info['icon'];
            if (!is_readable($iconfile)) {
                exit_with_error("Favicon is not readable.");
            }

            $site->icon = new File($info['icon'], file_get_contents($iconfile));
            $site->icon->filetype = mime_content_type($iconfile);
        }

        // Templates
        $site->templates = $this->reader->readDirectory($dir . 'templates/');

        // Styles and scripts
        $site->css = $this->reader->readFiles($dir . 'css/', $info['css'] ?? []);
        $site->js = $this->reader->readFiles($dir . 'js/', $info['js'] ?? []);

        // Css classes (before pages)
        $site->cssClasses = $info['classes'] ?? [];

        // Read pages
        $site->pages = $this->reader->readDirectory($dir . 'pages/');

        // Read menu
        $site->menu = $info['menu'] ?? [];
    }
}
