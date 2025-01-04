<?php
namespace Stagger;

use Twig\Environment;

class RssGenerator
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generate(Site $site, Blog $blog): string
    {
        $data = $blog->getTwigData($site->getTwigData());
        $data['rss_last_build_date'] = date('r');
        $data['rss_pub_date'] = date('r', $blog->getMaxDateOfChildren());
        return $this->twig->render('rss', $data);
    }
}
