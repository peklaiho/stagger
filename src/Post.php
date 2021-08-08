<?php
namespace Stagger;

/**
 * Class that represents a blog post.
 */
class Post extends Page
{
    public ?string $category = null;
    public array $tags = [];

    /**
     * Return data used in rendering Twig templates.
     */
    public function getTwigData(array $sitedata, bool $getPreviousNext = true): array
    {
        $data = parent::getTwigData($sitedata);

        if ($this->category) {
            $data['category'] = $this->category;
        }
        if ($this->tags) {
            $data['tags'] = $this->tags;
        }

        if ($getPreviousNext && $this->parent && $this->parent instanceof Blog) {
            $previous = $this->parent->getPrevious($this);
            if ($previous) {
                $data['previous'] = $previous->getTwigData($sitedata, false);
            }

            $next = $this->parent->getNext($this);
            if ($next) {
                $data['next'] = $next->getTwigData($sitedata, false);
            }
        }

        $data['preview'] = $this->makePreview();

        return $data;
    }

    public function getType(): string
    {
        return 'post';
    }

    /**
     * Return a preview of the post that consists of first two paragraphs.
     */
    protected function makePreview(): string
    {
        $preview = [];

        if ($this->parent instanceof Blog) {
            $paragraphs = $this->parent->previewParagraphs;
        } else {
            $paragraphs = 2;
        }

        // Get everything inside paragraph tags
        if (preg_match_all('/<p[^>]*>(.+)<\/p>/', $this->content, $matches)) {
            foreach ($matches as $match) {
                for ($i = 0; $i < count($matches[0]); $i++) {
                    // Skip images
                    if (substr($matches[1][$i], 0, 4) == '<img') {
                        continue;
                    }

                    if (count($preview) < $paragraphs) {
                        $preview[] = $matches[0][$i];
                    }
                }
            }
        }

        return implode(PHP_EOL, $preview);
    }
}
