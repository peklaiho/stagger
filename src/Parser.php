<?php
namespace Stagger;

use Symfony\Component\Yaml\Yaml;

/**
 * Parser reads a website from disk into memory.
 */
class Parser
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Parse the given website.
     */
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
        $site->meta = $info['meta'] ?? [];

        // Favicon
        if (array_key_exists('icon', $info)) {
            $site->icon = $this->reader->readSimpleFile($dir . $info['icon']);
        }

        // Image
        if (array_key_exists('image', $info)) {
            $site->image = $this->reader->readSimpleFile($dir . $info['image']);
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
