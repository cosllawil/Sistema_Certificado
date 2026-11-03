<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$fonts = [
    'Great Vibes' => 'GreatVibes-Regular.ttf',
    'Alex Brush' => 'AlexBrush-Regular.ttf',
    'Pinyon Script' => 'PinyonScript-Regular.ttf',
    'Allura' => 'Allura-Regular.ttf',
    'Birthstone' => 'Birthstone-Regular.ttf',
    'Bonheur Royale' => 'BonheurRoyale-Regular.ttf',
    'Kings' => 'Kings-Regular.ttf',
    'Rouge Script' => 'RougeScript-Regular.ttf',
    'Tangerine' => 'Tangerine-Regular.ttf',
    'Yesteryear' => 'Yesteryear-Regular.ttf',
    'Fineday Style One' => 'Fineday-StyleOne.ttf',
    'Fineday Style One Non-connect' => 'Fineday-StyleOnenon-connect.ttf',
    'Fineday Style Two' => 'Fineday-StyleTwo.ttf',
    'Fineday Style Two Non-connect' => 'Fineday-StyleTwonon-connect.ttf',
];
$makeFont = $root . '/vendor/setasign/fpdf/makefont/makefont.php';
if (!is_file($makeFont)) {
    fwrite(STDERR, "No se encontro FPDF MakeFont.\n");
    exit(1);
}

$out = $root . '/app/PdfFonts';
if (!is_dir($out)) {
    mkdir($out, 0755, true);
}
$errors = 0;
foreach ($fonts as $name => $filename) {
    $path = $root . '/assets/fonts/' . $filename;
    if (!is_file($path)) {
        echo "PENDIENTE: $name (coloca assets/fonts/$filename)\n";
        $errors++;
        continue;
    }
    try {
        $old = getcwd();
        chdir($out);
        $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($makeFont) . ' ' . escapeshellarg($path) . ' cp1252 1 1 2>&1';
        exec($cmd, $lines, $code);
        chdir($old);
        if ($code !== 0 || !is_file($out . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.json')) {
            throw new RuntimeException(implode("\n", $lines));
        }
        echo "OK: $name\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "ERROR $name: " . $e->getMessage() . "\n");
        $errors++;
    }
}
exit($errors ? 1 : 0);
