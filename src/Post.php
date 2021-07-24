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

        $data['preview'] = $this->makePreview();

        return $data;
    }

    public function getType(): string
    {
        return 'post';
    }

    protected function makePreview(): string
    {
        $preview = [];

        // Get everything inside paragraph tags
        if (preg_match_all('/<p[^>]*>(.+)<\/p>/', $this->content, $matches)) {
            foreach ($matches as $match) {
                for ($i = 0; $i < count($matches[0]); $i++) {
                    // Skip images
                    if (substr($matches[1][$i], 0, 4) == '<img') {
                        continue;
                    }

                    if (count($preview) < 2) {
                        $preview[] = $matches[0][$i];
                    }
                }
            }
        }

        return implode(PHP_EOL, $preview);
    }
}
