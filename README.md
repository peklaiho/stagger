# Stagger

Stagger is a minimalist static website generator written in PHP.

It uses [Markdown](https://spec.commonmark.org/current/) for content, [YAML](https://yaml.org/) for configuration and [Twig](https://twig.symfony.com/) for HTML templates.

## Rationale

There are lots of static website generators out there, but many of the popular ones are an overkill when it comes to creating a simple website or a blog. They are bloated with features and contain hundreds of pages of documentation. This means that you need to spend lots of time to learn *how to use the tool*, rather than just using the tool, and spending that time instead on creating content for your website.

Stagger, by contrast, aims to be really simple with a minimal set of features and this README file as the only documentation that you need. The goal is that you can just start writing content immediately and spend minimal time on configuration or reading documentation.

## Example Website

Stagger includes an example website located at **sites/example** which shows the structure of a website with some example pages and blog posts. There is only one configuration file, **site.yml**, located in the root directory. Pages are located in the **pages** directory with each page in their separate subdirectory. If the subdirectory contains **blog.md**, **page.md** or **post.md** it will be recognized as blog index, normal page, or a blog post respectively.

The example website uses [Spectre.css](https://picturepan2.github.io/spectre/). It gives some sane defaults for CSS styles and is a good starting point. Feel free to use it or not.

## Requirements

You need to know HTML, CSS and Markdown to make a website using Stagger. Knowledge of PHP is not needed unless you want to modify the internals of the tool.

You need a command line interface to use Stagger. Preferably a shell like Bash in Linux or Mac.

First, make sure PHP is installed on the command line:

    $ php --version
    PHP 8.0.8 (cli) (built: Jun 29 2021 16:09:21) ( NTS )

Second, make sure you have installed [Composer](https://getcomposer.org/). If not, follow the [instructions](https://getcomposer.org/download/).

    $ composer --version
    Composer version 2.1.3 2021-06-09 16:31:20

## Basic Usage

To get started, create a new project using Composer:

    $ composer create-project peklaiho/stagger mysite
    Creating a "peklaiho/stagger" project at "./mysite"
    $ cd mysite

You can generate the example website like this:

    $ bin/stagger example
    ...
    Generated site in /home/pekka/mysite/bin/../output/example.

The finished website is located in **output/example**. You can use the PHP built-in web server to view it:

    $ php -S 127.0.0.1:8000 -t output/example/
    [Sun Jul 25 12:20:26 2021] PHP 8.0.8 Development Server (http://127.0.0.1:8000) started

Navigate your web browser to *http://127.0.0.1:8000*.

That's all there is to it! Compare the files in **sites/example** to **output/example** to understand how the tool operates. It should be fairly obvious for the most part.

You can copy the example site to get a starting point for building your own site:

    $ cd sites
    $ cp -r example mysite

## Advanced Usage

Stagger features server-side syntax highlighting of source code using [highlight.php](https://github.com/scrivo/highlight.php) so no JavaScript is needed. Choose one CSS file from **vendor/scrivo/highlight.php/styles** directory and include it in your website.

If you need additional features for Markdown, take a look at [extensions for CommonMark](https://commonmark.thephpleague.com/2.0/extensions/overview/) that can be enabled.

## Code

The code is under 1000 lines (including comments and blanks) and will be kept under that limit.

It contains 5 classes that represent the data of the website during processing:

* Site - the website as a whole
* Page - a regular page
* Blog - index page for blog posts
* Post - a blog post
* File - a regular file (CSS, JS, image, ...)

The other classes contain the main functionality. They are used in roughly this order:

* Parser - read the site and parse the site.yml file
* Reader - read files and also process Markdown content
* Validator - check the site does not contain errors
* Generator - write the site to disk
* Processor - manipulate HTML code before it is written

## Limitations

Currently Stagger only supports websites that are located in the root directory of a domain. Small modifications are needed to support websites in a subdirectory so that links and URLs are generated correctly.

Functionality on Windows has not been tested and may require small modifications to handle the different directory separator.

## License

MIT

If you use this tool to make a website, it would be greatly appreciated if you add a mention (e.g. "Made with Stagger" or similar) with a link to this repo. Thanks!
