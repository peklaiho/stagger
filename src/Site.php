<?php
namespace Stagger;

class Site
{
    public string $id;
    public string $name;
    public string $url;

    public ?string $description = null;
    public ?array $icon = null;
    public ?string $lang = null;

    public array $templates = [];

    public array $css = [];
    public array $js = [];

    public array $pages = [];
    public array $menu = [];

    public function __construct(string $id, string $name, string $url)
    {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
    }
}
