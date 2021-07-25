<?php
namespace Stagger;

/**
 * Index page for a blog. Lists the posts.
 */
class Blog extends Page
{
    /**
     * Return data used in rendering Twig templates.
     */
    public function getTwigData(array $sitedata): array
    {
        $data = parent::getTwigData($sitedata);

        $data['posts'] = $this->getTwigDataForPosts($sitedata);

        $data['tags'] = $this->getChildTags();

        return $data;
    }

    /**
     * Return data for posts, to be used in rendering Twig templates.
     */
    public function getTwigDataForPosts(array $sitedata, ?string $tag = null): array
    {
        $posts = $tag ? $this->getPostsWithTag($tag) : $this->children;

        usort($posts, function ($a, $b) {
            return strtotime($b->date) - strtotime($a->date);
        });

        return array_map(function ($post) use ($sitedata) {
            return $post->getTwigData($sitedata);
        }, $posts);
    }

    /**
     * Return all posts which have the given tag.
     */
    public function getPostsWithTag(string $tag): array
    {
        $posts = [];

        foreach ($this->children as $post) {
            if (in_array($tag, $post->tags)) {
                $posts[] = $post;
            }
        }

        return $posts;
    }

    /**
     * Return all tags that posts have.
     */
    public function getChildTags(): array
    {
        $tags = [];

        foreach ($this->children as $post) {
            foreach ($post->tags as $tag) {
                if (!in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }

        sort($tags);
        return $tags;
    }

    public function getType(): string
    {
        return 'blog';
    }
}
