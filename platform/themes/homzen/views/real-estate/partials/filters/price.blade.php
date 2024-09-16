<div @class(['widget-price', $class ?? null]) data-bb-toggle="range" data-min="0" data-max="{{ get_max_properties_price() }}">
    <div class="box-title-price">
        <span class="title-price">{{ __('Price Range') }}</span>
        <div class="caption-price">
            <span>{{ __('from') }}</span>
            <span data-bb-toggle="range-from-value" class="fw-7 ms-1 me-1"></span>
            <span>{{ __('to') }}</span>
            <span data-bb-toggle="range-to-value" class="fw-7 ms-1"></span>
        </div>
    </div>
    <div data-bb-toggle="range-slider" data-currency-symbol="{{ get_application_currency()->symbol }}"></div>
    <div class="slider-labels">
        <div>
            <input type="hidden" data-bb-toggle="min-input" name="min_price" value="{{ BaseHelper::stringify(request()->float('min_price')) }}" />
            <input type="hidden" data-bb-toggle="max-input" name="max_price" value="{{ BaseHelper::stringify(request()->float('max_price')) }}" />
        </div>
    </div>
</div>
