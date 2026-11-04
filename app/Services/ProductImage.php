<?php
declare(strict_types=1);

namespace App\Services;

use GdImage;

// Fotos de produto: valida o envio e regrava a imagem do zero, descartando qualquer conteúdo escondido.
class ProductImage
{
    private const MAX_BYTES = 10 * 1024 * 1024;
    private const MAX_PIXELS = 40_000_000;
    private const OUTPUT_SIZE = 1200;
    private const URL_PREFIX = '/assets/produtos/';

    private static function dir(): string
    {
        return ROOT_PATH . '/public/assets/produtos';
    }

    public static function wasSent(?array $file): bool
    {
        return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    // Retorna o caminho público da imagem salva.
    public static function save(array $file): string
    {
        if (!is_int($file['error'] ?? null) || $file['error'] !== UPLOAD_ERR_OK) {
            $tooBig = in_array($file['error'] ?? null, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
            throw new ImageUploadError($tooBig ? 'A foto é grande demais (máximo de 10 MB).' : 'A foto não chegou inteira. Tente de novo.');
        }
        if ($file['size'] > self::MAX_BYTES) {
            throw new ImageUploadError('A foto é grande demais (máximo de 10 MB).');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new ImageUploadError('Envio de arquivo inválido.');
        }
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('A extensão GD do PHP não está ativa no servidor.');
        }

        // O tipo vem do conteúdo do arquivo, nunca do nome ou do que o navegador informou.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $loaders = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'];
        if (!isset($loaders[$mime])) {
            throw new ImageUploadError('Formato não aceito. Envie uma foto JPG, PNG ou WEBP.');
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false || $info[0] < 1 || $info[1] < 1) {
            throw new ImageUploadError('O arquivo não é uma imagem válida.');
        }
        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            throw new ImageUploadError('A foto tem resolução alta demais. Reduza o tamanho e tente de novo.');
        }

        ini_set('memory_limit', '512M');
        $source = @$loaders[$mime]($file['tmp_name']);
        if ($source === false) {
            throw new ImageUploadError('Não foi possível abrir a imagem. O arquivo pode estar danificado.');
        }
        if ($mime === 'image/jpeg') {
            $source = self::applyExifRotation($source, $file['tmp_name']);
        }
        $resized = self::fit($source);

        if (!is_dir(self::dir())) {
            mkdir(self::dir(), 0755, true);
        }
        // O nome é sempre gerado aqui: o nome enviado pelo usuário não é usado.
        $name = 'p-' . bin2hex(random_bytes(8));
        if (function_exists('imagewebp')) {
            $name .= '.webp';
            $saved = imagewebp($resized, self::dir() . '/' . $name, 80);
        } else {
            $name .= '.jpg';
            $saved = imagejpeg($resized, self::dir() . '/' . $name, 82);
        }
        if (!$saved) {
            throw new \RuntimeException('Não foi possível salvar a foto. Verifique as permissões de public/assets/produtos.');
        }
        return self::URL_PREFIX . $name;
    }

    // Só apaga arquivos da pasta de fotos de produto.
    public static function delete(?string $url): void
    {
        if (!$url || !preg_match('#^/assets/produtos/[A-Za-z0-9._-]+\.(webp|jpe?g|png)$#', $url)) {
            return;
        }
        $path = self::dir() . '/' . basename($url);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private static function applyExifRotation(GdImage $image, string $path): GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data($path);
        $angle = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
        if ($angle === 0) {
            return $image;
        }
        return imagerotate($image, $angle, 0) ?: $image;
    }

    // Reduz para caber em OUTPUT_SIZE, sobre fundo branco (PNG transparente fica limpo).
    private static function fit(GdImage $source): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, self::OUTPUT_SIZE / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        return $canvas;
    }
}
