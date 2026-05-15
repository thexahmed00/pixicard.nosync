<?php

namespace Webkul\Pos\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $eventTemplates = [
            [
                'event'    => 'bagisto.admin.layout.head.before',
                'template' => 'pos::admin.layouts.style',
            ],
        ];

        foreach ($eventTemplates as $eventTemplate) {
            Event::listen(current($eventTemplate), fn ($e) => $e->addTemplate(end($eventTemplate)));
        }
    }
}
