<?php
namespace Stagger;

/**
 * Class that represents a regular page (not a blog post).
 */
class Page extends File
{
    public ?string $title = null;
    public bool $home = false;

    public ?string $description = null;
    public ?string $author = null;
    public ?string $date = null;
    public ?string $edited = null;
    public ?string $image = null;

    public ?Page $parent = null;
    public array $children = [];
    public array $files = [];

    /**
     * Return data used in rendering Twig templates.
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
        if ($this->date) {
            $data['date'] = $this->date;
            $data['pretty_date'] = date('j F Y', strtotime($this->date));
            $data['rfc_date'] = date('r', strtotime($this->date));
        }
        if ($this->edited) {
            $data['edited'] = $this->edited;
            $data['pretty_edited'] = date('j F Y', strtotime($this->edited));
            $data['rfc_edited'] = date('r', strtotime($this->edited));
        }
        if ($this->image) {
            $data['page_image'] = $this->image;
        }

        $data['path'] = $this->getPath(true);
        $data['page_url'] = $sitedata['site_url'] . $this->getPath(false);

        return $data;
    }

    /**
     * Return the path of this page, to be used in links.
     */
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

    public function getMaxDateOfChildren(): ?int
    {
        $dates = [];

        foreach ($this->children as $child) {
            if ($child->date) {
                $dates[] = strtotime($child->date);
            }
        }

        return empty($dates) ? null : max($dates);
    }

    public function getType(): string
    {
        return 'page';
    }
}
