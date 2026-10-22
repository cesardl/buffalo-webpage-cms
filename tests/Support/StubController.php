<?php

declare(strict_types=1);

namespace Buffalo\Tests\Support;

use Buffalo\Http\Controller;
use Buffalo\Http\Request;
use Buffalo\Http\Response;

final class StubController implements Controller
{
    public ?Request $received = null;

    public function handle(Request $request): Response
    {
        $this->received = $request;

        return Response::text('handled');
    }
}
