<?php
// Fotos de produto: valida o envio e regrava a imagem do zero, descartando qualquer conteúdo escondido.
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

const IMAGE_MAX_BYTES = 10 * 1024 * 1024;
const IMAGE_MAX_PIXELS = 40_000_000;
const IMAGE_OUTPUT_SIZE = 1200;

class ImageUploadError extends RuntimeException
{
}

function image_was_sent(?array $file): bool
{
    return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}

// Retorna o caminho público da imagem salva.
function image_save_upload(array $file): string
{
    if (!is_int($file['error'] ?? null) || $file['error'] !== UPLOAD_ERR_OK) {
        $tooBig = in_array($file['error'] ?? null, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
        throw new ImageUploadError($tooBig ? 'A foto é grande demais (máximo de 10 MB).' : 'A foto não chegou inteira. Tente de novo.');
    }
    if ($file['size'] > IMAGE_MAX_BYTES) {
        throw new ImageUploadError('A foto é grande demais (máximo de 10 MB).');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new ImageUploadError('Envio de arquivo inválido.');
    }
    if (!extension_loaded('gd')) {
        throw new RuntimeException('A extensão GD do PHP não está ativa no servidor.');
    }

    // O tipo vem do conteúdo do arquivo, nunca do nome ou do que o navegador informou.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $loaders = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'];
    if (!isset($loaders[$mime])) {
        throw new ImageUploadError('Formato não aceito. Envie uma foto JPG, PNG ou WEBP.');
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || $info[0] < 1 || $info[1] < 1) {
        throw new ImageUploadError('O arquivo não é uma imagem válida.');
    }
    if ($info[0] * $info[1] > IMAGE_MAX_PIXELS) {
        throw new ImageUploadError('A foto tem resolução alta demais. Reduza o tamanho e tente de novo.');
    }

    ini_set('memory_limit', '512M');
    $source = @$loaders[$mime]($file['tmp_name']);
    if ($source === false) {
        throw new ImageUploadError('Não foi possível abrir a imagem. O arquivo pode estar danificado.');
    }
    if ($mime === 'image/jpeg') {
        $source = image_apply_exif_rotation($source, $file['tmp_name']);
    }

    $resized = image_fit($source);
    imagedestroy($source);

    if (!is_dir(PRODUCT_IMAGE_DIR)) {
        mkdir(PRODUCT_IMAGE_DIR, 0755, true);
    }
    // O nome é sempre gerado aqui: o nome enviado pelo usuário não é usado.
    $name = 'p-' . bin2hex(random_bytes(8));
    if (function_exists('imagewebp')) {
        $name .= '.webp';
        $saved = imagewebp($resized, PRODUCT_IMAGE_DIR . '/' . $name, 80);
    } else {
        $name .= '.jpg';
        $saved = imagejpeg($resized, PRODUCT_IMAGE_DIR . '/' . $name, 82);
    }
    imagedestroy($resized);

    if (!$saved) {
        throw new RuntimeException('Não foi possível salvar a foto. Verifique as permissões de assets/produtos.');
    }
    return PRODUCT_IMAGE_URL . $name;
}

function image_apply_exif_rotation(GdImage $image, string $path): GdImage
{
    if (!function_exists('exif_read_data')) {
        return $image;
    }
    $exif = @exif_read_data($path);
    $angle = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
    if ($angle === 0) {
        return $image;
    }
    $rotated = imagerotate($image, $angle, 0);
    if ($rotated === false) {
        return $image;
    }
    imagedestroy($image);
    return $rotated;
}

// Reduz para caber em IMAGE_OUTPUT_SIZE, sobre fundo branco (PNG transparente fica limpo).
function image_fit(GdImage $source): GdImage
{
    $width = imagesx($source);
    $height = imagesy($source);
    $scale = min(1, IMAGE_OUTPUT_SIZE / max($width, $height));
    $newWidth = max(1, (int) round($width * $scale));
    $newHeight = max(1, (int) round($height * $scale));

    $canvas = imagecreatetruecolor($newWidth, $newHeight);
    imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
    imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    return $canvas;
}

// Apaga a foto de um produto, só se estiver na pasta de produtos e nenhum outro produto a usar.
function image_delete_if_unused(?string $url, array $products): void
{
    if (!$url || !preg_match('#^/assets/produtos/[A-Za-z0-9._-]+\.(webp|jpe?g|png)$#', $url)) {
        return;
    }
    foreach ($products as $product) {
        if (($product['image'] ?? '') === $url) {
            return;
        }
    }
    $path = PRODUCT_IMAGE_DIR . '/' . basename($url);
    if (is_file($path)) {
        @unlink($path);
    }
}
