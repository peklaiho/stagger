<?php
namespace Stagger;

/**
 * Class that represents a website.
 */
class Site
{
    public string $name;

    // Required info
    public ?string $title = null;
    public ?string $url = null;

    // Optional metadata
    public ?string $description = null;
    public ?string $author = null;
    public ?string $lang = null;
    public ?File $icon = null;
    public ?File $image = null;
    public array $meta = [];

    // Content
    public array $templates = [];
    public array $css = [];
    public array $js = [];
    public array $cssClasses = [];
    public array $pages = [];
    public array $menu = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Return data used in rendering Twig templates.
     */
    public function getTwigData(): array
    {
        $data = [
            'site_title' => $this->title,
            'site_url' => $this->url
        ];

        if ($this->description) {
            $data['description'] = $this->description;
        }
        if ($this->author) {
            $data['author'] = $this->author;
        }
        if ($this->lang) {
            $data['lang'] = $this->lang;
        }
        if ($this->icon) {
            $data['icon'] = $this->icon;
        }
        if ($this->image) {
            $data['site_image'] = $this->image;
        }

        $data['meta'] = $this->meta;

        $fn = function ($f) {
            return $f->filename;
        };

        $data['css'] = array_map($fn, $this->css);
        $data['js'] = array_map($fn, $this->js);

        // Build menu
        $menu = [];
        foreach ($this->menu as $menuitem) {
            foreach ($this->pages as $page) {
                if ($menuitem == $page->filename) {
                    $menu[$page->getPath(true)] = $page->title;
                    break;
                }
            }
        }
        $data['menu'] = $menu;

        return $data;
    }

    /**
     * Return the Twig templates of the site.
     */
    public function getTwigTemplates(): array
    {
        $results = [];

        foreach ($this->templates as $file) {
            $results[$file->filename] = $file->content;
        }

        return $results;
    }
}
