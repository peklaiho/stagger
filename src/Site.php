<?php
namespace Stagger;

class Site
{
    public string $id;
    public string $name;
    public string $url;
    public string $theme;

    public ?string $description = null;
    public ?array $icon = null;
    public ?string $lang = null;

    public array $css = [];
    public array $js = [];

    public array $pages = [];
    public array $menu = [];

    public function __construct(string $id, string $name, string $url, string $theme)
    {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
        $this->theme = $theme;
    }
}
