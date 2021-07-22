<?php
namespace Stagger;

use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\Table\Table;

/**
 * Add CSS classes to HTML elements after Markdown has been parsed.
 */
class CssClassProcessor
{
    private $environment;
    private $classes;

    public function __construct(EnvironmentInterface $environment, array $classes)
    {
        $this->environment = $environment;
        $this->classes = $classes;
    }

    public function onDocumentParsed(DocumentParsedEvent $event)
    {
        $document = $event->getDocument();
        $walker = $document->walker();

        while ($event = $walker->next()) {
            if (!$event->isEntering()) {
                continue;
            }

            $node = $event->getNode();

            $elemName = $this->getElementName($node);

            if ($elemName && array_key_exists($elemName, $this->classes)) {
                $node->data->append('attributes/class', $this->classes[$elemName]);
            }
        }
    }

    protected function getElementName($node): ?string
    {
        $cname = get_class($node);

        if ($cname == Table::class) {
            return 'table';
        }

        // We need to add more here as needed...

        return null;
    }
}
