<?php

declare(strict_types=1);

namespace Buffalo\View;

use RuntimeException;

/**
 * Renders a plain PHP template.
 *
 * Templates receive their data as extracted variables and are responsible for
 * escaping, for which they have e(). The legacy pages built HTML by
 * concatenating database values into echo statements, which is why the
 * product descriptions needed htmlspecialchars() sprinkled around by hand and
 * the admin table did without.
 */
final readonly class Template
{
    public function __construct(private string $directory)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $name, array $data = []): string
    {
        $path = $this->directory . '/' . $name . '.php';

        if (!is_file($path)) {
            throw new RuntimeException("No such template: {$name}");
        }

        $render = static function (string $__path, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            require $__path;

            return (string) ob_get_clean();
        };

        return $render($path, $data);
    }
}
