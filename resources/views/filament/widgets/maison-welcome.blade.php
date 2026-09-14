<x-filament-widgets::widget>
    <section class="mi-welcome" aria-label="{{ __('admin.dashboard.title') }}">
        <div>
            <p class="mi-welcome__kicker">{{ config('maison.name') }} · {{ now()->translatedFormat('l j F') }}</p>
            <h2 class="mi-welcome__title">{{ __('admin.dashboard.welcome', ['name' => $name]) }}</h2>
            <p class="mi-welcome__text">{{ __('admin.dashboard.lead') }}</p>
        </div>

        <ol class="mi-welcome__steps">
            <li><span>{{ __('admin.dashboard.step_1_label') }}</span>{{ __('admin.dashboard.step_1') }}</li>
            <li><span>{{ __('admin.dashboard.step_2_label') }}</span>{{ __('admin.dashboard.step_2') }}</li>
            <li><span>{{ __('admin.dashboard.step_3_label') }}</span>{{ __('admin.dashboard.step_3') }}</li>
        </ol>
    </section>
</x-filament-widgets::widget>
