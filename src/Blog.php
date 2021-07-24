<?php
namespace Stagger;

class Blog extends Page
{
    public function getTwigData(array $sitedata): array
    {
        $data = parent::getTwigData($sitedata);

        $posts = $this->children;

        usort($posts, function ($a, $b) {
            return strtotime($b->date) - strtotime($a->date);
        });

        $data['posts'] = array_map(function ($post) use ($sitedata) {
            return $post->getTwigData($sitedata);
        }, $posts);

        return $data;
    }

    public function getType(): string
    {
        return 'blog';
    }
}
