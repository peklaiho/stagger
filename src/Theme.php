<?php
namespace Stagger;

use Twig\Environment;
use Twig\Loader\ArrayLoader;

class Theme
{
    public string $name;

    private Environment $twig;

    public function __construct(string $name)
    {
        $dir = THEMES_DIR . $name . '/';
        if (!is_readable($dir)) {
            exit_with_error("Theme $name does not exist or is not readable.");
        } else {
            $this->name = $name;
        }

        $templates = [];

        $files = glob($dir . '*.twig');
        foreach ($files as $file) {
            $pi = pathinfo($file);
            $templates[$pi['filename']] = file_get_contents($file);
        }

        // Check required templates
        $required = ['layout'];
        foreach ($required as $req) {
            if (!array_key_exists($req, $templates)) {
                exit_with_error("Required template $req.twig missing from theme $name.");
            }
        }

        $loader = new ArrayLoader($templates);
        $this->twig = new Environment($loader, [
            'autoescape' => false
        ]);
    }

    public function render(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }
}
