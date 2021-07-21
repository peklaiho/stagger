<?php
namespace Stagger;

use League\CommonMark\EnvironmentInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\Table\Table;

/**
 * We can manipulate the AST of the Markdown document
 * before it is rendered into HTML. We use this to
 * add some CSS classes as needed.
 */
class MarkdownProcessor
{
    private $environment;

    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    public function onDocumentParsed(DocumentParsedEvent $event)
    {
        $document = $event->getDocument();
        $walker = $document->walker();
        while ($event = $walker->next()) {
            $node = $event->getNode();

            // Only stop at Table nodes when we first encounter them
            if (!($node instanceof Table) || !$event->isEntering()) {
                continue;
            }

            $node->data['attributes']['class'] = 'table';
        }
    }
}
