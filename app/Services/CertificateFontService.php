<?php

declare(strict_types=1);
final class CertificateFontService
{
    private const DEFINITIONS = [
        'Great Vibes' => ['' => 'GreatVibes-Regular.json'],
        'Alex Brush' => ['' => 'AlexBrush-Regular.json'],
        'Pinyon Script' => ['' => 'PinyonScript-Regular.json'],
        'Allura' => ['' => 'Allura-Regular.json'],
        'Birthstone' => ['' => 'Birthstone-Regular.json'],
        'Bonheur Royale' => ['' => 'BonheurRoyale-Regular.json'],
        'Kings' => ['' => 'Kings-Regular.json'],
        'Rouge Script' => ['' => 'RougeScript-Regular.json'],
        'Tangerine' => ['' => 'Tangerine-Regular.json'],
        'Yesteryear' => ['' => 'Yesteryear-Regular.json'],
        'Fineday Style One' => ['' => 'Fineday-StyleOne.json'],
        'Fineday Style One Non-connect' => ['' => 'Fineday-StyleOnenon-connect.json'],
        'Fineday Style Two' => ['' => 'Fineday-StyleTwo.json'],
        'Fineday Style Two Non-connect' => ['' => 'Fineday-StyleTwonon-connect.json'],
        'Citadel Script' => ['' => 'Citadel Script Regular.json'],
        'Niconne' => ['' => 'Niconne-Regular.json'],
        'Amoresa Aged' => ['' => 'Amoresa-Aged.json'],
        'Amoresa Regular' => ['' => 'Amoresa-Regular.json'],
        'Gladiola' => ['' => 'Gladiola.json'],
        'MADE Evolve Sans' => ['' => 'MADE-Evolve-Sans-Regular.json'],
        'EB Garamond' => ['' => 'EBGaramond.json', 'B' => 'EBGaramond-B.json', 'I' => 'EBGaramond-I.json'],
        'Lato' => ['' => 'Lato.json', 'B' => 'Lato-B.json', 'I' => 'Lato-I.json', 'BI' => 'Lato-BI.json'],
        'Arimo' => ['' => 'Arimo.json', 'B' => 'Arimo-B.json', 'I' => 'Arimo-I.json', 'BI' => 'Arimo-BI.json'],
        'Carlito' => ['' => 'Carlito.json', 'B' => 'Carlito-B.json', 'I' => 'Carlito-I.json', 'BI' => 'Carlito-BI.json'],
        'Liberation Serif' => ['' => 'LiberationSerif.json', 'B' => 'LiberationSerif-B.json', 'I' => 'LiberationSerif-I.json', 'BI' => 'LiberationSerif-BI.json'],
        'FreeSerif' => ['' => 'FreeSerif.json', 'B' => 'FreeSerif-B.json', 'I' => 'FreeSerif-I.json', 'BI' => 'FreeSerif-BI.json'],
    ];

    public static function apply(\setasign\Fpdi\Fpdi $pdf, string $family, string $style, float $size): void
    {
        $style = in_array($style, ['', 'B', 'I', 'BI'], true) ? $style : '';
        if (isset(self::DEFINITIONS[$family])) {
            if (in_array($family, ['Great Vibes', 'Alex Brush', 'Pinyon Script', 'Allura', 'Birthstone', 'Bonheur Royale', 'Kings', 'Rouge Script', 'Tangerine', 'Yesteryear', 'Fineday Style One', 'Fineday Style One Non-connect', 'Fineday Style Two', 'Fineday Style Two Non-connect', 'Citadel Script', 'Niconne', 'Amoresa Aged', 'Amoresa Regular', 'Gladiola', 'MADE Evolve Sans'], true)) {
                $style = '';
            }
            $definitions = self::DEFINITIONS[$family];
            $file = $definitions[$style] ?? $definitions[''] ?? reset($definitions);
            $fontPath = __DIR__ . '/../PdfFonts/' . $file;
            if (!is_file($fontPath)) {
                throw new \RuntimeException('Falta instalar la tipografía ' . $family . ' para el PDF. Ejecuta: php tools/install_script_fonts.php');
            }
            $pdf->AddFont($family, $style, $file, __DIR__ . '/../PdfFonts/');
            $pdf->SetFont($family, $style, $size);
        } else {
            $family = in_array($family, ['Helvetica', 'Times', 'Courier'], true) ? $family : 'Helvetica';
            $pdf->SetFont($family, $style, $size);
        }
    }
}
