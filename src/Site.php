<?php
namespace Stagger;

class Site
{
    public string $name;
    public string $url;
    public string $theme;

    public ?string $description;
    public ?string $icon;
    public ?string $lang;

    public array $css;
    public array $js;

    public array $pages;
}
