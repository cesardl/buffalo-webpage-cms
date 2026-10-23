<?php

declare(strict_types=1);

namespace Buffalo\Http\Controller;

use Buffalo\Domain\Master;
use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;
use Buffalo\Repository\MasterRepository;

use function Buffalo\View\e;

/** Feeds the class dropdown when the vehicle type changes. */
final readonly class VehiculoSelectController implements Controller
{
    public function __construct(private MasterRepository $masters)
    {
    }

    public function handle(Request $request): Response
    {
        $vehiculoId = (int) $request->postString('veh_id');
        $html = '<option value="0">[Seleccione]</option>';

        if ($vehiculoId !== 0) {
            foreach ($this->masters->findByVehiculo($vehiculoId) as $master) {
                $html .= sprintf(
                    '<option value="%s">%s</option>',
                    e((string) $master->id),
                    e($master->clase),
                );
            }
        }

        return Response::html($html);
    }
}
