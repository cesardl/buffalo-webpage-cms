<?php

declare(strict_types=1);

namespace Buffalo\Http;

interface Controller
{
    public function handle(Request $request): Response;
}
