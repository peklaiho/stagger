<?php
namespace Stagger;

class Page
{
    public string $id;
    public string $name;
    public string $content;
    public bool $homepage;

    public array $files = [];

    public function __construct(string $id, string $name, string $content, bool $homepage)
    {
        $this->id = $id;
        $this->name = $name;
        $this->content = $content;
        $this->homepage = $homepage;
    }

    /**
     * The $data argument contains the default data for the site
     * and can be modified here to add page-specific values.
     */
    public function getTwigData(array $data): array
    {
        $data['id'] = $this->id;
        $data['name'] = $this->name;
        $data['home'] = $this->homepage;
        $data['link'] = $this->getLink(true);
        $data['url'] =  $data['url'] . $this->getLink(false);
        $data['content'] = $this->content;

        return $data;
    }

    public function getLink(bool $includeRootSlash): string
    {
        $link = $includeRootSlash ? '/' : '';

        if (!$this->homepage) {
            $link .= $this->id . '/';
        }

        return $link;
    }
}
