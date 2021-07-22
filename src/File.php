<?php
namespace Stagger;

class File
{
    public string $filename;
    public string $content;

    public function __construct(string $filename, string $content = '')
    {
        $this->filename = $filename;
        $this->content = $content;
    }

    public function getTwigData(array $sitedata): array
    {
        $data = $sitedata;

        $data['filename'] = $this->filename;
        $data['content'] = $this->content;

        return $data;
    }
}