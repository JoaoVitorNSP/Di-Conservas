<?php
declare(strict_types=1);

namespace App\Services;

// Problema na foto enviada, com mensagem que pode ser mostrada ao usuário.
class ImageUploadError extends \RuntimeException
{
}
