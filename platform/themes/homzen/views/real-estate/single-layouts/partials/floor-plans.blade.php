 <div @class(['single-property-floor', $class ?? null])>
    <div class="h7 title fw-7">{{ __('Floor plans') }}</div>
    <ul class="box-floor" id="parent-floor">
        @foreach ($property->formatted_floor_plans as $floorPlan)
            @php
                $slug = Str::slug($floorPlan['name']) . '-' . $loop->index;
            @endphp

            <li class="floor-item">
                <div class="floor-header" data-bs-target="#floor-{{ $slug }}" data-bs-toggle="collapse" aria-expanded="false" aria-controls="floor-{{ $slug }}">
                    <div class="inner-left">
                        <i class="icon icon-arr-r"></i>
                        <span class="fw-7">{!! BaseHelper::clean($floorPlan['name']) !!}</span>
                    </div>
                    <ul class="inner-right">
                        <li class="d-flex align-items-center gap-8">
                            <x-core::icon name="ti ti-bed" />
                            {{ $floorPlan['bedrooms'] }}
                        </li>
                        <li class="d-flex align-items-center gap-8">
                            <x-core::icon name="ti ti-bath" />
                            {{ $floorPlan['bathrooms'] }}
                        </li>
                    </ul>
                </div>
                <div id="floor-{{ $slug }}" class="collapse show" data-bs-parent="#parent-floor">
                    <div class="faq-body">
                        @if ($floorPlan['description'])
                            <div class="box-desc text-variant-1 mb-3">
                                {!! BaseHelper::clean($floorPlan['description']) !!}
                            </div>
                        @endif
                        @if ($floorPlan['image'])
                            <div class="box-img">
                                {{ RvMedia::image($floorPlan['image'], $floorPlan['name']) }}
                            </div>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
