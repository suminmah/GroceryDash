<?php
$logos = [
    'esewa.png' => 'https://raw.githubusercontent.com/sahrohit/esewa-epay/master/src/assets/esewa-logo.png',
    'khalti.png' => 'https://khalti.com/static/images/logos/KHALTI_LOGO.png',
    'fonepay.png' => 'https://raw.githubusercontent.com/manoj1201/fonepay-php/master/assets/fonepay_logo.png'
];

$dir = __DIR__ . '/public/assets/images/';

foreach ($logos as $filename => $url) {
    echo "Downloading $filename...\n";
    $content = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 5]]));
    if ($content) {
        file_put_contents($dir . $filename, $content);
        echo "Saved $filename.\n";
    } else {
        echo "Failed to download $filename.\n";
    }
}
