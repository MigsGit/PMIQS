<?php
namespace App\Providers;

use App\Services\FileService;

use App\Services\EmailService;
use App\Services\CommonService;
use App\Interfaces\FileInterface;
use App\Services\ResourceService;

use App\Interfaces\EmailInterface;
use App\Services\PdfCustomService;
use App\Interfaces\CommonInterface;
use App\Interfaces\ResourceInterface;
use App\Interfaces\PdfCustomInterface;
use Illuminate\Support\ServiceProvider;

class SolidServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ResourceInterface::class, ResourceService::class);
        $this->app->bind(CommonInterface::class, CommonService::class);
        $this->app->bind(PdfCustomInterface::class, PdfCustomService::class);
        $this->app->bind(FileInterface::class, FileService::class);
        $this->app->bind(EmailInterface::class, EmailService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
