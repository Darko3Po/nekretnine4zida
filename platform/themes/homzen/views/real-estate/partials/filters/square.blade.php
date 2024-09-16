<div @class(['widget-price', $class ?? null])>
    <div class="box-title-price">
        <span class="title-price">{{ __('Square Range') }}</span>
        <div class="caption-price">
            <span>{{ __('from') }}</span>
            <span id="slider-range-value01" class="fw-7 ms-1 me-1"></span>
            <span>{{ __('to') }}</span>
            <span id="slider-range-value02" class="fw-7 ms-1"></span>
        </div>
    </div>
    <div
        id="slider-range2"
        data-min="{{ get_min_square() }}"
        data-max="{{ get_max_square() }}"
        data-unit="{{ setting('real_estate_square_unit', 'm²') }}"
    ></div>
    <div class="slider-labels">
        <div>
            <input type="hidden" name="min_square" value="{{ BaseHelper::stringify(request()->query('min_square')) }}" />
            <input type="hidden" name="max_square" value="{{ BaseHelper::stringify(request()->query('max_square')) }}" />
        </div>
    </div>
</div>
