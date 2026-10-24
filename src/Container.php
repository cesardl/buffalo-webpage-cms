<?php

declare(strict_types=1);

namespace Buffalo;

use Buffalo\Catalogo\Catalogo;
use Buffalo\Contacto\ContactoValidator;
use Buffalo\Database\ConnectionFactory;
use Buffalo\Http\Controller;
use Buffalo\Http\Controller\BienvenidoController;
use Buffalo\Http\Controller\ContactanosController;
use Buffalo\Http\Controller\FormColoresController;
use Buffalo\Http\Controller\FormDetallesController;
use Buffalo\Http\Controller\FormProductoController;
use Buffalo\Http\Controller\GuardarColoresController;
use Buffalo\Http\Controller\GuardarContactoController;
use Buffalo\Http\Controller\GuardarDetallesController;
use Buffalo\Http\Controller\GuardarProductoController;
use Buffalo\Http\Controller\HealthController;
use Buffalo\Http\Controller\LoginController;
use Buffalo\Http\Controller\ProductosController;
use Buffalo\Http\Controller\VehiculoDeleteController;
use Buffalo\Http\Controller\VehiculoSelectController;
use Buffalo\Http\PhpSession;
use Buffalo\Http\Session;
use Buffalo\Repository\ColorRepository;
use Buffalo\Repository\ContactoRepository;
use Buffalo\Repository\DetalleProductoRepository;
use Buffalo\Repository\MasterRepository;
use Buffalo\Repository\PdoColorRepository;
use Buffalo\Repository\PdoContactoRepository;
use Buffalo\Repository\PdoDetalleProductoRepository;
use Buffalo\Repository\PdoMasterRepository;
use Buffalo\Repository\PdoProductoRepository;
use Buffalo\Repository\PdoUsuarioRepository;
use Buffalo\Repository\PdoVehiculoRepository;
use Buffalo\Repository\ProductoRepository;
use Buffalo\Repository\UsuarioRepository;
use Buffalo\Repository\VehiculoRepository;
use Buffalo\Security\Authenticator;
use Buffalo\Upload\Uploader;
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
        private readonly string $publicDirectory,
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

    public function uploader(): Uploader
    {
        return new Uploader($this->publicDirectory);
    }

    public function productos(): ProductoRepository
    {
        return new PdoProductoRepository($this->pdo());
    }

    public function masters(): MasterRepository
    {
        return new PdoMasterRepository($this->pdo());
    }

    public function vehiculos(): VehiculoRepository
    {
        return new PdoVehiculoRepository($this->pdo());
    }

    public function colores(): ColorRepository
    {
        return new PdoColorRepository($this->pdo());
    }

    public function detalles(): DetalleProductoRepository
    {
        return new PdoDetalleProductoRepository($this->pdo());
    }

    public function catalogo(): Catalogo
    {
        return new Catalogo(
            $this->masters(),
            $this->productos(),
            $this->colores(),
            $this->detalles(),
        );
    }

    public function usuarios(): UsuarioRepository
    {
        return new PdoUsuarioRepository($this->pdo());
    }

    public function contactos(): ContactoRepository
    {
        return new PdoContactoRepository($this->pdo());
    }

    /** @param class-string<Controller> $class */
    public function controller(string $class): Controller
    {
        return match ($class) {
            HealthController::class => new HealthController($this->pdo()),
            ProductosController::class => new ProductosController($this->template(), $this->catalogo()),
            LoginController::class => new LoginController($this->template(), $this->session()),
            ContactanosController::class => new ContactanosController(
                $this->template(),
                $this->session(),
            ),
            GuardarContactoController::class => new GuardarContactoController(
                new ContactoValidator(),
                $this->contactos(),
            ),
            BienvenidoController::class => new BienvenidoController(
                $this->template(),
                $this->session(),
                new Authenticator($this->usuarios()),
                $this->productos(),
                $this->masters(),
            ),
            FormProductoController::class => new FormProductoController(
                $this->template(),
                $this->session(),
                $this->productos(),
                $this->masters(),
                $this->vehiculos(),
            ),
            FormDetallesController::class => new FormDetallesController(
                $this->template(),
                $this->session(),
                $this->detalles(),
            ),
            FormColoresController::class => new FormColoresController(
                $this->template(),
                $this->session(),
                $this->colores(),
            ),
            GuardarProductoController::class => new GuardarProductoController(
                $this->template(),
                $this->productos(),
                $this->uploader(),
            ),
            GuardarDetallesController::class => new GuardarDetallesController(
                $this->template(),
                $this->detalles(),
                $this->uploader(),
            ),
            GuardarColoresController::class => new GuardarColoresController(
                $this->template(),
                $this->colores(),
            ),
            VehiculoSelectController::class => new VehiculoSelectController($this->masters()),
            VehiculoDeleteController::class => new VehiculoDeleteController($this->productos()),
            default => throw new RuntimeException("No wiring for {$class}."),
        };
    }
}
