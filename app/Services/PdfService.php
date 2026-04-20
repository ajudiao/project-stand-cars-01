<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class PdfService
{
    private Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(BASE_PATH . '/resources/views');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => false,
        ]);

        $this->twig->addGlobal('companyLogo', 'https://thumbs.dreamstime.com/z/o-projeto-do-logotipo-do-carro-da-auto-loja-com-conceito-ostenta-silhueta-do-ve%C3%ADculo-86246431.jpg?ct=jpeg');
    }

    public function renderToPdf(string $template, array $data, string $filename): void
    {
        $html = $this->twig->render($template . '.twig', $data);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo $dompdf->output();
        exit;
    }

    public function getImageDataUri(string $path): ?string
    {
        if (!file_exists($path) || !is_readable($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $mimeType = mime_content_type($path) ?: 'image/png';
        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }
}
