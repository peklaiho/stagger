<?php
namespace Stagger;

use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Node\Block\Paragraph;

class Site
{
    // Required info
    public ?string $name = null;
    public ?string $title = null;
    public ?string $url = null;

    // Optional metadata
    public ?string $description = null;
    public ?string $author = null;
    public ?array $icon = null;
    public ?string $lang = null;

    // Content
    public array $templates = [];
    public array $css = [];
    public array $js = [];
    public array $pages = [];
    public array $menu = [];
    public array $cssClasses = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getTwigData(): array
    {
        $data = [
            'site_title' => $this->title,
            'url' => $this->url
        ];

        if ($this->description) {
            $data['description'] = $this->description;
        }
        if ($this->author) {
            $data['author'] = $this->author;
        }
        if ($this->icon) {
            $data['icon'] = $this->icon;
        }
        if ($this->lang) {
            $data['lang'] = $this->lang;
        }

        $data['css'] = array_keys($this->css);
        $data['js'] = array_keys($this->js);

        // Build menu
        $menu = [];
        foreach ($this->menu as $menupage) {
            $page = $this->pages[$menupage];
            $menu[$page->getPath(true)] = $page->title;
        }
        $data['menu'] = $menu;

        return $data;
    }

    public function onPageParsed($event)
    {
        $document = $event->getDocument();
        $walker = $document->walker();

        while ($event = $walker->next()) {
            if (!$event->isEntering()) {
                continue;
            }

            $node = $event->getNode();

            $elemName = $this->getElementName($node);

            if ($elemName && array_key_exists($elemName, $this->cssClasses)) {
                $node->data->append('attributes/class', $this->cssClasses[$elemName]);
            }
        }
    }

    protected function getElementName($node): ?string
    {
        if ($node instanceof Table) {
            return 'table';
        } elseif ($node instanceof TableCell) {
            if ($node->getType() == TableCell::TYPE_HEADER) {
                return 'th';
            } else {
                return 'td';
            }
        } elseif ($node instanceof TableRow) {
            return 'tr';
        } elseif ($node instanceof TableSection) {
            if ($node->isHead()) {
                return 'thead';
            } else {
                return 'tbody';
            }
        } elseif ($node instanceof Paragraph) {
            return 'p';
        } elseif ($node instanceof Link) {
            return 'a';
        } elseif ($node instanceof Image) {
            return 'img';
        } elseif ($node instanceof ListBlock) {
            if ($node->getListData()->type == ListBlock::TYPE_ORDERED) {
                return 'ol';
            } else {
                return 'ul';
            }
        } elseif ($node instanceof ListItem) {
            return 'li';
        } elseif ($node instanceof Heading) {
            return 'h' . $node->getLevel();
        } elseif ($node instanceof FencedCode) {
            return 'pre';
        } elseif ($node instanceof Code) {
            return 'code';
        } elseif ($node instanceof Emphasis) {
            return 'em';
        } elseif ($node instanceof Strong) {
            return 'strong';
        } elseif ($node instanceof BlockQuote) {
            return 'blockquote';
        }

        // Add more here as needed...

        return null;
    }
}
