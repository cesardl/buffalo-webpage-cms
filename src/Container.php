<?php

declare(strict_types=1);

namespace Buffalo;

use Buffalo\Database\ConnectionFactory;
use Buffalo\Http\Controller;
use Buffalo\Http\Controller\BienvenidoController;
use Buffalo\Http\Controller\HealthController;
use Buffalo\Http\Controller\LoginController;
use Buffalo\Http\PhpSession;
use Buffalo\Http\Session;
use Buffalo\Repository\MasterRepository;
use Buffalo\Repository\PdoMasterRepository;
use Buffalo\Repository\PdoProductoRepository;
use Buffalo\Repository\PdoUsuarioRepository;
use Buffalo\Repository\ProductoRepository;
use Buffalo\Repository\UsuarioRepository;
use Buffalo\Security\Authenticator;
use Buffalo\View\Template;
use PDO;
use RuntimeException;

/**
 * Wiring, by hand.
 *
 * A container library would do this with reflection and configuration; there
 * are a handful of services, so a match arm each is shorter than the setup a
 * library would need, and the wiring stays greppable.
 */
final class Container
{
    private ?PDO $pdo = null;
    private ?Session $session = null;
    private ?Template $template = null;

    public function __construct(
        private readonly Config $config,
        private readonly string $templateDirectory,
    ) {
    }

    public function pdo(): PDO
    {
        return $this->pdo ??= (new ConnectionFactory($this->config))->create();
    }

    public function session(): Session
    {
        return $this->session ??= new PhpSession();
    }

    public function template(): Template
    {
        return $this->template ??= new Template($this->templateDirectory);
    }

    public function productos(): ProductoRepository
    {
        return new PdoProductoRepository($this->pdo());
    }

    public function masters(): MasterRepository
    {
        return new PdoMasterRepository($this->pdo());
    }

    public function usuarios(): UsuarioRepository
    {
        return new PdoUsuarioRepository($this->pdo());
    }

    /** @param class-string<Controller> $class */
    public function controller(string $class): Controller
    {
        return match ($class) {
            HealthController::class => new HealthController($this->pdo()),
            LoginController::class => new LoginController($this->template(), $this->session()),
            BienvenidoController::class => new BienvenidoController(
                $this->template(),
                $this->session(),
                new Authenticator($this->usuarios()),
                $this->productos(),
                $this->masters(),
            ),
            default => throw new RuntimeException("No wiring for {$class}."),
        };
    }
}
