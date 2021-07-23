<?php
namespace Stagger;

class Page extends File
{
    public ?string $title = null;
    public bool $home = false;

    public ?string $description = null;
    public ?string $author = null;
    public ?string $created = null;
    public ?string $edited = null;

    public ?Page $parent = null;
    public array $children = [];
    public array $files = [];

    /**
     * The $data argument contains the default data for the site
     * and can be modified here to add page-specific values.
     */
    public function getTwigData(array $sitedata): array
    {
        $data = parent::getTwigData($sitedata);

        $data['page_title'] = $this->title;
        $data['home'] = $this->home;

        if ($this->description) {
            $data['description'] = $this->description;
        }
        if ($this->author) {
            $data['author'] = $this->author;
        }
        if ($this->created) {
            $data['created'] = $this->created;
        }
        if ($this->edited) {
            $data['edited'] = $this->edited;
        }

        $data['path'] = $this->getPath(true);
        $data['url'] = $sitedata['url'] . $this->getPath(false);

        $data['children'] = array_map(function ($c) use ($sitedata) {
            return $c->getTwigData($sitedata);
        }, $this->children);

        return $data;
    }

    public function getPath(bool $includeLeadingSlash): string
    {
        if ($this->parent) {
            $path = $this->parent->getPath($includeLeadingSlash);
        } else {
            $path = $includeLeadingSlash ? '/' : '';
        }

        if (!$this->home) {
            $path .= $this->filename . '/';
        }

        return $path;
    }
}
