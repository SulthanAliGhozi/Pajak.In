<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\RegionProviderInterface;
use App\Contracts\TaxReferenceGatewayInterface;
use App\Services\MidtransSnapGateway;
use App\Services\RegionApiClient;
use App\Services\ResilientTaxReferenceGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaxReferenceGatewayInterface::class, ResilientTaxReferenceGateway::class);
        $this->app->bind(RegionProviderInterface::class, RegionApiClient::class);
        $this->app->bind(PaymentGatewayInterface::class, MidtransSnapGateway::class);
    }

    public function boot(): void
    {
        //
    }
}
