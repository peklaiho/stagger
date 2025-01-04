<?php
namespace Stagger;

/**
 * Index page for a blog. Lists the posts.
 */
class Blog extends Page
{
    public int $previewParagraphs = 2;

    /**
     * Return data used in rendering Twig templates.
     */
    public function getTwigData(array $sitedata): array
    {
        $data = parent::getTwigData($sitedata);

        $data['posts'] = $this->getTwigDataForPosts($sitedata);

        $data['tags'] = $this->getChildTags();

        if ($sitedata['rss_enabled']) {
            $data['rss_url'] = $sitedata['site_url'] . $this->getPath(false) . 'rss.xml';
        }

        return $data;
    }

    /**
     * Return data for posts, to be used in rendering Twig templates.
     */
    public function getTwigDataForPosts(array $sitedata, ?string $tag = null): array
    {
        $posts = $this->getPosts($tag);

        return array_map(function ($post) use ($sitedata) {
            return $post->getTwigData($sitedata);
        }, $posts);
    }

    /**
     * Return all posts or posts which have the given tag.
     */
    public function getPosts(?string $tag = null): array
    {
        $posts = [];

        foreach ($this->children as $post) {
            if ($post instanceof Post) {
                if (!$tag || in_array($tag, $post->tags)) {
                    $posts[] = $post;
                }
            }
        }

        usort($posts, function ($a, $b) {
            return strtotime($b->date) - strtotime($a->date);
        });

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

    /**
     * Return the previous post before the given one.
     */
    public function getPrevious(Post $post): ?Post
    {
        $posts = $this->getPosts();

        for ($i = 0; $i < count($posts) - 1; $i++) {
            if ($posts[$i] === $post) {
                return $posts[$i + 1];
            }
        }

        return null;
    }

    /**
     * Return the next post after the given one.
     */
    public function getNext(Post $post): ?Post
    {
        $posts = $this->getPosts();

        for ($i = 1; $i < count($posts); $i++) {
            if ($posts[$i] === $post) {
                return $posts[$i - 1];
            }
        }

        return null;
    }

    public function getType(): string
    {
        return 'blog';
    }
}
