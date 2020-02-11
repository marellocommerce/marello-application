<?php

namespace Marello\Bundle\PdfBundle\Renderer;

use Twig\Environment;

class TwigRenderer
{
    protected $renderer;

    protected $twig;

    public function __construct(HtmlRenderer $renderer, Environment $twig)
    {
        $this->renderer = $renderer;
        $this->twig = $twig;
    }

    public function render($template, array $params = [])
    {
        return $this->renderer->render($this->renderTwig($template, $params));
    }

    public function renderToFile($template, array $params, $filename)
    {
        return $this->renderer->renderToFile($this->renderTwig($template, $params), $filename);
    }

    public function renderBase64($template, array $params = [])
    {
        return base64_encode($this->render($template, $params));
    }

    protected function renderTwig($template, array $params = [])
    {
        return $this->twig->render($template, $params);
    }
}
