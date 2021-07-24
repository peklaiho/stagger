<?php
namespace Stagger;

class Post extends Page
{
    public ?string $category = null;
    public array $tags = [];

    public function getTwigData(array $sitedata): array
    {
        $data = parent::getTwigData($sitedata);

        if ($this->category) {
            $data['category'] = $this->category;
        }
        if ($this->tags) {
            $data['tags'] = $this->tags;
        }

        $data['preview'] = $data['content'];

        return $data;
    }

    public function getType(): string
    {
        return 'post';
    }
}
