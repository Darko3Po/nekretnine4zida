<?php

namespace Botble\RealEstate\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\AutocompleteFieldOption;
use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\RepeaterFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\Fields\AutocompleteField;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\RepeaterField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Location\Fields\Options\SelectLocationFieldOption;
use Botble\Location\Fields\SelectLocationField;
use Botble\RealEstate\Enums\CustomFieldEnum;
use Botble\RealEstate\Enums\ModerationStatusEnum;
use Botble\RealEstate\Enums\PropertyPeriodEnum;
use Botble\RealEstate\Enums\PropertyStatusEnum;
use Botble\RealEstate\Enums\PropertyTypeEnum;
use Botble\RealEstate\Facades\RealEstateHelper;
use Botble\RealEstate\Forms\Fields\CategoryMultiField;
use Botble\RealEstate\Forms\Fronts\Auth\FieldOptions\TextFieldOption;
use Botble\RealEstate\Http\Requests\PropertyRequest;
use Botble\RealEstate\Models\Currency;
use Botble\RealEstate\Models\CustomField;
use Botble\RealEstate\Models\Facility;
use Botble\RealEstate\Models\Feature;
use Botble\RealEstate\Models\Project;
use Botble\RealEstate\Models\Property;
use stdClass;

class PropertyForm extends FormAbstract
{
    public function setup(): void
    {
        Assets::usingVueJS()
            ->addStyles('datetimepicker')
            ->addScripts('input-mask')
            ->addStylesDirectly('vendor/core/plugins/real-estate/css/real-estate.css')
            ->addScriptsDirectly([
                'vendor/core/plugins/real-estate/js/real-estate.js',
                'vendor/core/plugins/real-estate/js/components.js',
            ]);

        $projects = Project::query()
            ->select('name', 'id')
            ->latest()
            ->get()
            ->mapWithKeys(fn (Project $item) => [$item->getKey() => $item->name])
            ->all();

        $currencies = Currency::query()->pluck('title', 'id')->all();

        $selectedCategories = [];
        if ($this->getModel()) {
            $selectedCategories = $this->getModel()->categories()->pluck('category_id')->all();
        }

        if (! $this->formHelper->hasCustomField('categoryMulti')) {
            $this->formHelper->addCustomField('categoryMulti', CategoryMultiField::class);
        }

        $selectedFeatures = [];
        if ($this->getModel()) {
            $selectedFeatures = $this->getModel()->features()->pluck('id')->all();
        }

        $features = Feature::query()
            ->select('id', 'name')
            ->get()
            ->each(function ($item) {
                $item->name = (string) $item->name;
            });

        $facilities = Facility::query()
            ->select('id', 'name')
            ->get()
            ->each(function ($item) {
                $item->name = (string) $item->name;
            });

        if ($this->getModel()) {
            $selectedFacilities = $this->getModel()->facilities()->select('re_facilities.id', 'distance')->get();
        } else {
            $selectedFacilities = collect();

            $oldSelectedFacilities = old('facilities', []);

            if (! empty($oldSelectedFacilities)) {
                foreach ($oldSelectedFacilities as $oldSelectedFacility) {
                    if (! isset($oldSelectedFacility['id']) || ! isset($oldSelectedFacility['distance'])) {
                        continue;
                    }

                    $item = new stdClass();
                    $item->id = $oldSelectedFacility['id'];
                    $item->distance = $oldSelectedFacility['distance'];

                    $selectedFacilities->add($item);
                }
            }
        }

        $squareUnit = setting('real_estate_square_unit', 'm²') ? sprintf('(%s)', setting('real_estate_square_unit', 'm²')) : null;

        if ($this->getModel() && is_in_admin(true)) {
            add_filter('base_action_form_actions_extra', function (?string $html) {
                return $html . view(
                        'plugins/real-estate::partials.forms.duplicate-button',
                        [
                            'url' => route('property.duplicate-property', $this->getModel()->id),
                            'label' => trans('plugins/real-estate::property.duplicate'),
                        ]
                    )->render();
            });
        }

        $this
            ->setupModel(new Property())
            ->setValidatorClass(PropertyRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->required())
            ->add('type', SelectField::class, [
                'label' => trans('plugins/real-estate::property.form.type'),
                'required' => true,
                'choices' => PropertyTypeEnum::labels(),
                'attr' => [
                    'id' => 'type',
                ],
            ])
            ->add('description', TextareaField::class, DescriptionFieldOption::make())
            ->add(
                'is_featured',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('core/base::forms.is_featured'))
                    ->defaultValue(false)
            )
            ->add('content', EditorField::class, ContentFieldOption::make()->allowedShortcodes())
            ->add('images[]', 'mediaImages', [
                'label' => trans('plugins/real-estate::property.form.images'),
                'values' => $this->getModel()->id ? $this->getModel()->images : [],
            ])
            ->when(is_plugin_active('location'), function (FormAbstract $form) {
                $form->add(
                    'location_data',
                    SelectLocationField::class,
                    SelectLocationFieldOption::make()
                );
            })
            ->add(
                'location',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/real-estate::property.form.location'))
                    ->placeholder(('plugins/real-estate::property.form.location'))
                    ->maxLength(191)
            )
            ->add('rowOpen', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('latitude', 'text', [
                'label' => trans('plugins/real-estate::property.form.latitude'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-6',
                ],
                'attr' => [
                    'placeholder' => 'Ex: 1.462260',
                    'data-counter' => 25,
                ],
                'help_block' => [
                    'tag' => 'a',
                    'text' => trans('plugins/real-estate::property.form.latitude_helper'),
                    'attr' => [
                        'href' => 'https://www.latlong.net/convert-address-to-lat-long.html',
                        'target' => '_blank',
                        'rel' => 'nofollow',
                    ],
                ],
            ])
            ->add('longitude', 'text', [
                'label' => trans('plugins/real-estate::property.form.longitude'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-6',
                ],
                'attr' => [
                    'placeholder' => 'Ex: 103.812530',
                    'data-counter' => 25,
                ],
                'help_block' => [
                    'tag' => 'a',
                    'text' => trans('plugins/real-estate::property.form.longitude_helper'),
                    'attr' => [
                        'href' => 'https://www.latlong.net/convert-address-to-lat-long.html',
                        'target' => '_blank',
                        'rel' => 'nofollow',
                    ],
                ],
            ])
            ->add('rowClose', 'html', [
                'html' => '</div>',
            ])
            ->add('rowOpen1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('number_bedroom', 'number', [
                'label' => trans('plugins/real-estate::property.form.number_bedroom'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-3',
                ],
                'attr' => [
                    'placeholder' => trans('plugins/real-estate::property.form.number_bedroom'),
                ],
            ])
            ->add('number_bathroom', 'number', [
                'label' => trans('plugins/real-estate::property.form.number_bathroom'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-3',
                ],
                'attr' => [
                    'placeholder' => trans('plugins/real-estate::property.form.number_bathroom'),
                ],
            ])
            ->add('number_floor', 'number', [
                'label' => trans('plugins/real-estate::property.form.number_floor'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-3',
                ],
                'attr' => [
                    'placeholder' => trans('plugins/real-estate::property.form.number_floor'),
                ],
            ])
            ->add('square', 'number', [
                'label' => trans('plugins/real-estate::property.form.square', ['unit' => $squareUnit]),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-3',
                ],
                'attr' => [
                    'placeholder' => trans('plugins/real-estate::property.form.square', ['unit' => $squareUnit]),
                ],
            ])
            ->add('rowClose1', 'html', [
                'html' => '</div>',
            ])
            ->add('rowOpen2', 'html', [
                'html' => '<div class="row">',
            ])
            ->add('price', 'text', [
                'label' => trans('plugins/real-estate::property.form.price'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-6',
                ],
                'attr' => [
                    'id' => 'price-number',
                    'placeholder' => trans('plugins/real-estate::property.form.price'),
                    'class' => 'form-control input-mask-number',
                    'data-thousands-separator' => RealEstateHelper::getThousandSeparatorForInputMask(),
                    'data-decimal-separator' => RealEstateHelper::getDecimalSeparatorForInputMask(),
                ],
            ])
            ->add('currency_id', 'customSelect', [
                'label' => trans('plugins/real-estate::property.form.currency'),
                'wrapper' => [
                    'class' => 'form-group mb-3 col-md-6',
                ],
                'attr' => [
                    'class' => 'select-full',
                ],
                'choices' => $currencies,
            ])
            ->add('period', 'customSelect', [
                'label' => trans('plugins/real-estate::property.form.period'),
                'required' => true,
                'wrapper' => [
                    'class' => 'form-group mb-3 period-form-group col-md-4' . ($this->getModel()->type != PropertyTypeEnum::RENT ? ' hidden' : null),
                ],
                'attr' => [
                    'class' => 'select-search-full',
                    'id' => 'period',
                ],
                'choices' => PropertyPeriodEnum::labels(),
            ])
            ->add('rowClose2', 'html', [
                'html' => '</div>',
            ])
            ->add('never_expired', 'onOff', [
                'label' => trans('plugins/real-estate::property.never_expired'),
                'default_value' => true,
            ])
            ->add(
                'private_notes',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label(trans('plugins/real-estate::property.private_notes'))
                    ->helperText(trans('plugins/real-estate::property.private_notes_helper'))
                    ->rows(2)
                    ->colspan(2)
            )
            ->add('auto_renew', 'onOff', [
                'label' => trans('plugins/real-estate::property.renew_notice', ['days' => RealEstateHelper::propertyExpiredDays()]),
                'default_value' => false,
                'wrapper' => [
                    'class' => 'form-group mb-3 auto-renew-form-group' . (! $this->getModel()->id || $this->getModel()->never_expired ? ' hidden' : null),
                ],
                'attr' => [
                    'id' => 'auto_renew',
                ],
            ])
            ->add(
                'floor_plans',
                RepeaterField::class,
                RepeaterFieldOption::make()
                    ->label(trans('plugins/real-estate::property.floor_plans.title'))
                    ->fields([
                        'name' => [
                            'type' => 'text',
                            'label' => trans('plugins/real-estate::property.floor_plans.name'),
                            'attributes' => [
                                'name' => 'name',
                                'value' => null,
                                'options' => [
                                    'class' => 'form-control',
                                    'data-counter' => 255,
                                ],
                            ],
                        ],
                        'description' => [
                            'type' => 'textarea',
                            'label' => trans('plugins/real-estate::property.floor_plans.description'),
                            'attributes' => [
                                'name' => 'description',
                                'value' => null,
                                'options' => [
                                    'class' => 'form-control',
                                    'rows' => 2,
                                ],
                            ],
                        ],
                        'image' => [
                            'type' => 'mediaImage',
                            'label' => trans('plugins/real-estate::property.floor_plans.image'),
                            'attributes' => [
                                'name' => 'image',
                                'value' => null,
                            ],
                        ],
                        'bedrooms' => [
                            'type' => 'number',
                            'label' => trans('plugins/real-estate::property.floor_plans.bedrooms'),
                            'attributes' => [
                                'name' => 'bedrooms',
                                'value' => null,
                                'options' => [
                                    'class' => 'form-control',
                                    'placeholder' => trans('plugins/real-estate::property.floor_plans.bedrooms_placeholder'),
                                ],
                            ],
                        ],
                        'bathrooms' => [
                            'type' => 'number',
                            'label' => trans('plugins/real-estate::property.floor_plans.bathrooms'),
                            'attributes' => [
                                'name' => 'bathrooms',
                                'value' => null,
                                'options' => [
                                    'class' => 'form-control',
                                    'placeholder' => trans('plugins/real-estate::property.floor_plans.bathrooms_placeholder'),
                                ],
                            ],
                        ],
                    ])
            )
            ->addMetaBoxes([
                'features' => [
                    'title' => trans('plugins/real-estate::property.form.features'),
                    'content' => view(
                        'plugins/real-estate::partials.form-features',
                        compact('selectedFeatures', 'features')
                    )->render(),
                    'priority' => 1,
                ],
                'facilities' => [
                    'title' => trans('plugins/real-estate::property.distance_key'),
                    'content' => view(
                        'plugins/real-estate::partials.form-facilities',
                        compact('facilities', 'selectedFacilities')
                    ),
                    'priority' => 0,
                ],
            ])
            ->add(
                'status',
                SelectField::class,
                StatusFieldOption::make()
                    ->choices(PropertyStatusEnum::labels())
                    ->selected((string) $this->model->status ?: PropertyStatusEnum::SELLING)
            )
            ->add('moderation_status', 'customSelect', [
                'label' => trans('plugins/real-estate::property.moderation_status'),
                'required' => true,
                'attr' => [
                    'class' => 'select-full',
                ],
                'choices' => ModerationStatusEnum::labels(),
                'selected' => (string) $this->model->moderation_status ?: ModerationStatusEnum::APPROVED,
            ])
            ->add('categories[]', 'categoryMulti', [
                'label' => trans('plugins/real-estate::property.form.categories'),
                'choices' => get_property_categories_with_children(),
                'value' => old('categories', $selectedCategories),
            ])
            ->add('unique_id', 'text', [
                'label' => trans('plugins/real-estate::property.unique_id'),
                'attr' => [
                    'placeholder' => trans('plugins/real-estate::property.unique_id'),
                ],
            ])
            ->when(! empty($projects), function () use ($projects) {
                $this
                    ->add('project_id', 'customSelect', [
                        'label' => trans('plugins/real-estate::property.form.project'),
                        'attr' => [
                            'class' => 'select-search-full',
                        ],
                        'choices' => [0 => trans('plugins/real-estate::property.select_project')] + $projects,
                    ]);
            })
            ->setBreakFieldPoint('status')
            ->add(
                'author_id',
                AutocompleteField::class,
                AutocompleteFieldOption::make()
                    ->label(trans('plugins/real-estate::property.account'))
                    ->ajaxUrl(route('account.list'))
                    ->when($this->getModel()->author_id, function (AutocompleteFieldOption $option) {
                        $option->choices([$this->model->author->id => $this->model->author->name]);
                    })
                    ->emptyValue(trans('plugins/real-estate::property.select_account'))
                    ->allowClear()
            )
            ->when(RealEstateHelper::isEnabledCustomFields(), function (FormAbstract $form) {
                Assets::addScriptsDirectly('vendor/core/plugins/real-estate/js/custom-fields.js');

                $customFields = CustomField::query()->select(['name', 'id', 'type'])->get();

                $form->addMetaBoxes([
                    'custom_fields_box' => [
                        'title' => trans('plugins/real-estate::custom-fields.name'),
                        'content' => view('plugins/real-estate::custom-fields.custom-fields', [
                            'options' => CustomFieldEnum::labels(),
                            'customFields' => $customFields,
                            'model' => $this->model,
                            'ajax' => is_in_admin(true) ? route('real-estate.custom-fields.get-info') : route('public.account.custom-fields.get-info'),
                        ]),
                        'priority' => 0,
                    ],
                ]);
            });
    }
}