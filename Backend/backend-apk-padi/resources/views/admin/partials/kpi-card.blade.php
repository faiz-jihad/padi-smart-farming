@php
    $cardClass = 'dashboard-kpi-card '.($href ? 'dashboard-kpi-card--link' : '');
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $cardClass }}">
@else
    <article class="{{ $cardClass }}">
@endif
        <div class="dashboard-kpi-card__body">
            <div>
                <p class="dashboard-kpi-card__label">{{ $metric['label'] }}</p>
                <strong class="dashboard-kpi-card__value" data-countup="{{ $metric['value'] }}">{{ number_format($metric['value'], 0, ',', '.') }}</strong>
            </div>

            <span class="dashboard-kpi-card__icon dashboard-tone-{{ $metric['tone'] }}" aria-hidden="true">
                @include('admin.partials.metric-icon', ['icon' => $metric['icon']])
            </span>
        </div>

        <p class="dashboard-kpi-card__helper">{{ $metric['helper'] }}</p>
@if($href)
    </a>
@else
    </article>
@endif
