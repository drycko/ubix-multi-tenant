<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
  /**
   * The URIs that should be excluded from CSRF verification.
   *
   * Add the gateway notify path here (adjust path to the real public URI).
   *
   * @var array<int, string>
   */
  protected $except = [
    'tg/paygate/notify',
    'tg/payfast/notify', // if you want PayFast excluded here too
  ];
}