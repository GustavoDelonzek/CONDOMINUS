<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\WhatsAppProviderInterface;
use App\Enums\WhatsAppProvider;
use App\Services\WhatsApp\EvolutionApiProvider;
use App\Services\WhatsApp\ZApiProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WhatsAppProviderInterface::class, function ($app) {
            $provider = WhatsAppProvider::tryFrom(env('WHATSAPP_PROVIDER', 'evolution'));

            return match($provider) {
                WhatsAppProvider::ZAPI      => new ZApiProvider(),
                WhatsAppProvider::EVOLUTION  => new EvolutionApiProvider(),
                default                     => new EvolutionApiProvider(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
