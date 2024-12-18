<?php

namespace Botble\Setting\Forms;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Media\Facades\RvMedia;
use Botble\Media\Models\MediaFolder;
use Botble\Setting\Http\Requests\MediaSettingRequest;

class MediaSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        Assets::addScriptsDirectly('vendor/core/core/setting/js/media.js');

        $folders = MediaFolder::query()
            ->where('parent_id', 0)
            ->pluck('name', 'id')
            ->all();
        $folderIds = old($key = 'media_folders_can_add_watermark', json_decode((string) setting($key), true));

        $this
            ->setSectionTitle(trans('core/setting::setting.media.title'))
            ->setSectionDescription(trans('core/setting::setting.media.description'))
            ->setUrl(route('settings.media.update'))
            ->when(setting('media_enable_thumbnail_sizes', true), function () {
                $this->setActionButtons(
                    view('core/setting::partials.media.action-buttons', ['form' => $this->getFormOption('id')])->render()
                );
            })
            ->setValidatorClass(MediaSettingRequest::class)
            ->contentOnly()
            ->columns(6)
            ->add('media_driver', 'html', [
                'html' => view('core/setting::partials.media.media-driver-field')->render(),
                'colspan' => 6,
            ])
            ->add(
                'media_use_original_name_for_file_path',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('core/setting::setting.media.use_original_name_for_file_path'))
                    ->value(setting('media_use_original_name_for_file_path'))
                    ->colspan(6)
            )
            ->add(
                'media_keep_original_file_size_and_quality',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('core/setting::setting.media.keep_original_file_size_and_quality'))
                    ->value(setting('media_keep_original_file_size_and_quality'))
                    ->colspan(6)
            )
            ->add(
                'media_turn_off_automatic_url_translation_into_latin',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('core/setting::setting.media.turn_off_automatic_url_translation_into_latin'))
                    ->value(RvMedia::turnOffAutomaticUrlTranslationIntoLatin())
                    ->colspan(6)
            )
            ->add(
                'user_can_only_view_own_media',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('core/setting::setting.media.user_can_only_view_own_media'))
                    ->value(setting('user_can_only_view_own_media', false))
                    ->helperText(trans('core/setting::setting.media.user_can_only_view_own_media_helper'))
                    ->colspan(6)
            )
            ->add('media_default_placeholder_image', MediaImageField::class, [
                'label' => trans('core/setting::setting.media.default_placeholder_image'),
                'value' => setting('media_default_placeholder_image'),
                'colspan' => 6,
            ])
            ->add('max_upload_filesize', 'number', [
                'label' => trans('core/setting::setting.media.max_upload_filesize'),
                'value' => setting('max_upload_filesize'),
                'attr' => [
                    'placeholder' => trans('core/setting::setting.media.max_upload_filesize_placeholder', [
                        'size' => ($maxSize = BaseHelper::humanFilesize(RvMedia::getServerConfigMaxUploadFileSize())),
                    ]),
                    'step' => 0.01,
                ],
                'help_block' => [
                    'text' => trans('core/setting::setting.media.max_upload_filesize_helper', ['size' => $maxSize]),
                ],
                'colspan' => 6,
            ])
            ->add('chunk_size_upload_file', 'html', [
                'html' => view('core/setting::partials.media.chunk-size-upload-field')->render(),
                'colspan' => 6,
            ])
            ->add(
                'media_watermark_warning',
                AlertField::class,
                AlertFieldOption::make()
                    ->type('warning')
                    ->content(trans('core/setting::setting.watermark_description'))
                    ->colspan(6)
            )
            ->add(
                $mediaWatermarkEnabled = 'media_watermark_enabled',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('core/setting::setting.media.enable_watermark'))
                    ->value($mediaWatermarkEnabledValue = setting('media_watermark_enabled'))
                    ->colspan(6)
            )
            ->addOpenCollapsible($mediaWatermarkEnabled, '1', $mediaWatermarkEnabledValue, ['class' => 'form-fieldset col-lg-12'])
            ->add('media_folders_can_add_watermark_field', 'html', [
                'html' => view(
                    'core/setting::partials.media.media-folders-can-add-watermark-field',
                    compact('folders', 'folderIds')
                )->render(),
            ])
            ->add('row_1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add(
                'media_watermark_source',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('core/setting::setting.media.watermark_source'))
                    ->value(setting('media_watermark_source'))
                    ->colspan(6)
            )
            ->add('media_watermark_size', 'number', [
                'label' => trans('core/setting::setting.media.watermark_size'),
                'value' => setting('media_watermark_size', RvMedia::getConfig('watermark.size')),
                'attr' => [
                    'placeholder' => trans('core/setting::setting.media.watermark_size_placeholder'),
                ],
                'colspan' => 3,
            ])
            ->add('media_watermark_opacity', 'number', [
                'label' => trans('core/setting::setting.media.watermark_opacity'),
                'value' => setting(
                    'media_watermark_opacity',
                    setting('watermark_opacity') ?: RvMedia::getConfig('watermark.opacity')
                ),
                'attr' => [
                    'placeholder' => trans('core/setting::setting.media.watermark_opacity_placeholder'),
                ],
                'colspan' => 3,
            ])
            ->add('media_watermark_position', SelectField::class, [
                'label' => trans('core/setting::setting.media.watermark_position'),
                'selected' => setting(
                    'media_watermark_position',
                    RvMedia::getConfig('watermark.position')
                ),
                'choices' => [
                    'top-left' => trans('core/setting::setting.media.watermark_position_top_left'),
                    'top-right' => trans('core/setting::setting.media.watermark_position_top_right'),
                    'bottom-left' => trans(
                        'core/setting::setting.media.watermark_position_bottom_left'
                    ),
                    'bottom-right' => trans(
                        'core/setting::setting.media.watermark_position_bottom_right'
                    ),
                    'center' => trans('core/setting::setting.media.watermark_position_center'),
                ],
                'colspan' => 2,
            ])
            ->add('media_watermark_position_x', 'number', [
                'label' => trans('core/setting::setting.media.watermark_position_x'),
                'value' => setting(
                    'media_watermark_position_x',
                    setting('watermark_position_x') ?: RvMedia::getConfig('watermark.x')
                ),
                'attr' => [
                    'placeholder' => trans('core/setting::setting.media.watermark_position_x'),
                ],
                'colspan' => 2,
            ])
            ->add('media_watermark_position_y', 'number', [
                'label' => trans('core/setting::setting.media.watermark_position_y'),
                'value' => setting(
                    'media_watermark_position_y',
                    setting('watermark_position_y') ?: RvMedia::getConfig('watermark.y')
                ),
                'attr' => [
                    'placeholder' => trans('core/setting::setting.media.watermark_position_y'),
                ],
                'colspan' => 2,
            ])
            ->add('row_1_close', 'html', [
                'html' => '</div>',
            ])
            ->addCloseCollapsible($mediaWatermarkEnabled, '1')
            ->add('media_image_processing_library', 'customRadio', [
                'label' => trans('core/setting::setting.media.image_processing_library'),
                'selected' => RvMedia::getImageProcessingLibrary(),
                'choices' => array_merge(
                    ['gd' => 'GD Library'],
                    extension_loaded('imagick')
                        ? ['imagick' => 'Imagick']
                        : [],
                ),
                'colspan' => 6,
            ])
            ->add(
                'media_enable_thumbnail_sizes',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('core/setting::setting.media.enable_thumbnail_sizes'))
                    ->helperText(trans('core/setting::setting.media.enable_thumbnail_sizes_helper'))
                    ->value($enabledThumbnailSizes = setting('media_enable_thumbnail_sizes', true))
                    ->colspan(6)
            )
            ->addOpenCollapsible('media_enable_thumbnail_sizes', '1', $enabledThumbnailSizes, ['class' => 'form-fieldset col-lg-12'])
            ->add('open_row_2', 'html', [
                'html' => '<div class="row row-cols-lg-6">',
            ])
            ->add('title_media_size', 'html', [
                'html' => '<h4>' . trans('core/setting::setting.media.sizes') . ':</h4>',
                'colspan' => 6,
            ]);

        foreach (RvMedia::getSizes() as $name => $size) {
            $sizeExploded = explode('x', $size);

            $this->add(
                sprintf('media_size_%s_label', $name),
                HtmlField::class,
                HtmlFieldOption::make()
                    ->view('core/setting::includes.form-media-size-label', compact('name'))
                    ->colspan(6)
            );

            if (count($sizeExploded) === 2) {
                $this
                    ->add($nameWidth = sprintf('media_sizes_%s_width', $name), 'number', [
                        'label' => false,
                        'value' => setting($nameWidth, $sizeExploded[0]),
                        'attr' => [
                            'placeholder' => 0,
                        ],
                        'colspan' => 3,
                    ])
                    ->add($nameHeight = sprintf('media_sizes_%s_height', $name), 'number', [
                        'label' => false,
                        'value' => setting($nameHeight, $sizeExploded[1]),
                        'attr' => [
                            'placeholder' => 0,
                        ],
                        'colspan' => 3,
                    ]);
            }
        }

        $this
            ->add(
                'media_thumbnail_crop_position',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('core/setting::setting.media.thumbnail_crop_position'))
                    ->helperText(trans('core/setting::setting.media.thumbnail_crop_position_helper'))
                    ->selected(setting('media_thumbnail_crop_position', 'center'))
                    ->choices([
                        'left' => trans('core/setting::setting.media.thumbnail_crop_position_left'),
                        'right' => trans('core/setting::setting.media.thumbnail_crop_position_right'),
                        'top' => trans('core/setting::setting.media.thumbnail_crop_position_top'),
                        'bottom' => trans('core/setting::setting.media.thumbnail_crop_position_bottom'),
                        'center' => trans('core/setting::setting.media.thumbnail_crop_position_center'),
                    ])
                    ->colspan(6)
            )
            ->add(
                'media_sizes_helper',
                AlertField::class,
                AlertFieldOption::make()
                    ->content(trans('core/setting::setting.media.media_sizes_helper'))
                    ->addAttribute('class', 'mb-0')
                    ->colspan(6)
            )
            ->add(
                'update_thumbnail_sizes_warning',
                AlertField::class,
                AlertFieldOption::make()
                    ->type('warning')
                    ->content(trans('core/setting::setting.media.update_thumbnail_sizes_warning', ['button_text' => trans('core/setting::setting.generate_thumbnails')]))
                    ->colspan(6)
            )
            ->add('close_row_2', 'html', [
                'html' => '</div>',
            ])
            ->addCloseCollapsible('media_enable_thumbnail_sizes', '1');
    }
}
