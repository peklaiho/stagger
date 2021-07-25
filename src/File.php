<?php
namespace Stagger;

/**
 * Class for normal files (not Blog, Page or Post).
 */
class File
{
    public string $filename;
    public string $content;

    public ?string $filetype = null;

    public function __construct(string $filename, string $content = '')
    {
        $this->filename = $filename;
        $this->content = $content;
    }

    /**
     * Return data used in rendering Twig templates.
     */
    public function getTwigData(array $sitedata): array
    {
        $data = $sitedata;

        $data['filename'] = $this->filename;
        $data['content'] = $this->content;

        if ($this->filetype) {
            $data['filetype'] = $this->filetype;
        }

        $data['pagetype'] = $this->getType();

        return $data;
    }

    public function getType(): string
    {
        return 'file';
    }
}
