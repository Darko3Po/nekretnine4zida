<?php

use Botble\Theme\Events\RenderingThemeOptionSettings;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Facades\ThemeOption;
use Botble\Theme\ThemeOption\Fields\ColorField;
use Botble\Theme\ThemeOption\Fields\MediaImageField;
use Botble\Theme\ThemeOption\Fields\SelectField;
use Botble\Theme\ThemeOption\Fields\TextField;
use Botble\Theme\ThemeOption\Fields\ToggleField;
use Botble\Theme\ThemeOption\Fields\UiSelectorField;
use Botble\Theme\ThemeOption\ThemeOptionSection;

app('events')->listen(RenderingThemeOptionSettings::class, function () {
    ThemeOption::setSection(
        ThemeOptionSection::make('opt-text-subsection-styles')
            ->title(__('Styles'))
            ->icon('ti ti-palette')
            ->fields([
                ColorField::make()
                    ->name('primary_color')
                    ->label(__('Primary color'))
                    ->defaultValue('#db1d23'),
                ColorField::make()
                    ->name('hover_color')
                    ->label(__('Hover color'))
                    ->defaultValue('#cd380f'),
                ColorField::make()
                    ->name('footer_background_color')
                    ->label(__('Footer background color'))
                    ->defaultValue('#161e2d'),
                MediaImageField::make()
                    ->name('footer_background_image')
                    ->label(__('Footer background image')),
                ToggleField::make()
                    ->name('use_modal_for_authentication')
                    ->label(__('Use Modal for Login/Register'))
                    ->defaultValue(true)
                    ->helperText(__('When the login/register button is clicked, a popup will appear with the login/register form instead of redirecting users to another page.')),
                ColorField::make()
                    ->name('top_header_background_color')
                    ->label(__('Top header background color'))
                    ->defaultValue('#f7f7f7'),
                ColorField::make()
                    ->name('top_header_text_color')
                    ->label(__('Top header text color'))
                    ->defaultValue('#161e2d'),
                ColorField::make()
                    ->name('main_header_background_color')
                    ->label(__('Main header background color'))
                    ->defaultValue('#ffffff'),
                ColorField::make()
                    ->name('main_header_text_color')
                    ->label(__('Main header text color'))
                    ->defaultValue('#161e2d'),
                ColorField::make()
                    ->name('main_header_border_color')
                    ->label(__('Main header border color'))
                    ->defaultValue('#e4e4e4'),
            ])
    )
        ->setField(
            UiSelectorField::make()
                ->sectionId('opt-text-subsection-real-estate')
                ->name('real_estate_property_listing_layout')
                ->label(__('Property listing page layout'))
                ->defaultValue('top-map')
                ->numberItemsPerRow(2)
                ->options($listingLayouts = [
                    'top-map' => [
                        'image' => Theme::asset()->url('images/listing-layouts/top-map.png'),
                        'label' => __('List with map on top'),
                    ],
                    'half-map' => [
                        'image' => Theme::asset()->url('images/listing-layouts/half-map.png'),
                        'label' => __('List with map on the right'),
                    ],
                    'sidebar' => [
                        'image' => Theme::asset()->url('images/listing-layouts/sidebar.png'),
                        'label' => __('Filter box on the left'),
                    ],
                    'without-map' => [
                        'image' => Theme::asset()->url('images/listing-layouts/without-map.png'),
                        'label' => __('Without map'),
                    ],
                ])
        )

        ->setField(
            UiSelectorField::make()
                ->sectionId('opt-text-subsection-real-estate')
                ->name('real_estate_property_detail_layout')
                ->label(__('Property detail page layout'))
                ->defaultValue(1)
                ->numberItemsPerRow(2)
                ->options(
                    collect(range(1, 4))->mapWithKeys(fn ($style) => [
                        $style => [
                            'image' => Theme::asset()->url("images/single-layouts/style-$style.png"),
                            'label' => __('Style :number', ['number' => $style]),
                        ],
                    ])->all()
                )
        )
        ->setField(
            UiSelectorField::make()
                ->sectionId('opt-text-subsection-real-estate')
                ->name('real_estate_project_listing_layout')
                ->label(__('Project listing page layout'))
                ->numberItemsPerRow(2)
                ->defaultValue('top-map')
                ->options($listingLayouts)
        )
        ->setField(
            TextField::make()
                ->name('hotline')
                ->label(__('Hotline'))
                ->sectionId('opt-text-subsection-general')
        )
        ->setField(
            TextField::make()
                ->name('email')
                ->label(__('Email'))
                ->sectionId('opt-text-subsection-general')
        )
        ->setField(
            ColorField::make()
                ->name('breadcrumb_background_color')
                ->label(__('Breadcrumb background color'))
                ->sectionId('opt-text-subsection-breadcrumb')
                ->defaultValue('#f7f7f7')
        )
        ->setField(
            ColorField::make()
                ->name('breadcrumb_text_color')
                ->label(__('Breadcrumb text color'))
                ->sectionId('opt-text-subsection-breadcrumb')
                ->defaultValue('#161e2d')
        )
        ->setField(
            MediaImageField::make()
                ->name('breadcrumb_background_image')
                ->label(__('Breadcrumb background image'))
                ->sectionId('opt-text-subsection-breadcrumb')
                ->helperText(__('If you select an image, the background color will be ignored.'))
        )
        ->setField(
            MediaImageField::make()
                ->sectionId('opt-text-subsection-logo')
                ->name('logo_light')
                ->label(__('Logo light'))
        )
        ->setField(
            SelectField::make()
                ->sectionId('opt-text-subsection-real-estate')
                ->name('real_estate_show_map_on_single_detail_page')
                ->label(__('Show map on the property/project detail page'))
                ->defaultValue('yes')
                ->options([
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ])
        )
        ->setField(
            SelectField::make()
                ->sectionId('opt-text-subsection-general')
                ->name('enabled_back_to_top')
                ->label(__('Enable back to top button'))
                ->defaultValue('yes')
                ->options([
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ])
        )
        ->setField([
            'id' => 'blog_show_author_name',
            'section_id' => 'opt-text-subsection-blog',
            'type' => 'customSelect',
            'label' => __('Show author name?'),
            'attributes' => [
                'name' => 'blog_show_author_name',
                'list' => [
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ],
                'value' => 'yes',
                'options' => [
                    'class' => 'form-control',
                ],
            ],
        ]);
});
