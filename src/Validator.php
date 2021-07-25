<?php
namespace Stagger;

/**
 * Validates a website after it has been read
 * from the disk.
 */
class Validator
{
	/**
	 * Validate the given site and exit if it
	 * contains errors.
	 */
	public function validate(Site $site): void
	{
		show_info('Validating site.');

		// Required site attributes
		if (!$site->title) {
			exit_with_error('Site does not have title.');
		}
		if (!$site->url) {
			exit_with_error('Site does not have URL.');
		}

		// Optional site attributes, show warning and proceed
		if (!$site->description) {
			show_info('Warning: Site does not have description.');
		}
		if (!$site->lang) {
			show_info('Warning: Site does not have language.');
		}
		if (!$site->icon) {
			show_info('Warning: Site does not have favicon.');
		}
		if (!$site->menu) {
			show_info('Warning: Site does not have menu.');
		}

		// Check we have home page
		$homepages = 0;
		foreach ($site->pages as $page) {
			if ($page->home) {
				$homepages++;
			}
		}
		if ($homepages != 1) {
			exit_with_error('Site should have exactly 1 home page.');
		}

		// Check required templates
		$templates = array_keys($site->getTwigTemplates());
		$reqTemplates = ['blog', 'layout', 'page', 'post'];
		foreach ($reqTemplates as $req) {
			if (!in_array($req, $templates)) {
				exit_with_error('Required template is missing: ' . $req);
			}
		}

		// Validate pages recursively
		$this->validatePages($site->pages);
	}

	/**
	 * Validate the given pages recursively.
	 */
	private function validatePages(array $pages): void
	{
		foreach ($pages as $page) {

			// Required page attributes
			if (!$page->title) {
				exit_with_error("Page '{$page->filename}' does not have title.");
			}

			// Required fields for blog posts
			if ($page instanceof Post) {
				if (!$page->date) {
					exit_with_error("Post '{$page->filename}' does not have date.");
				}
			}

			// Validate dates if given
			if ($page->date && !$this->isValidDate($page->date)) {
				exit_with_error("Date for page '{$page->filename}' is not valid: " . $page->date);
			}
			if ($page->edited && !$this->isValidDate($page->edited)) {
				exit_with_error("Edit date for page '{$page->filename}' is not valid: " . $page->edited);
			}

			// Validate child pages
			$this->validatePages($page->children);
		}
	}

    /**
     * Validate date in ISO format (yyyy-mm-dd).
     */
    private function isValidDate(string $date): bool
    {
        return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date);
    }
}
