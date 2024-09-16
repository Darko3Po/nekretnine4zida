<?php

use Botble\Base\Http\Middleware\RequiresJsonRequestMiddleware;
use Botble\RealEstate\Facades\RealEstateHelper;
use Botble\RealEstate\Http\Controllers\CustomFieldController;
use Botble\RealEstate\Http\Controllers\Fronts\AccountPropertyController;
use Botble\RealEstate\Http\Controllers\Fronts\ConsultController;
use Botble\RealEstate\Http\Controllers\Fronts\CouponController;
use Botble\RealEstate\Http\Controllers\Fronts\ForgotPasswordController;
use Botble\RealEstate\Http\Controllers\Fronts\InvoiceController;
use Botble\RealEstate\Http\Controllers\Fronts\LoginController;
use Botble\RealEstate\Http\Controllers\Fronts\PublicAccountController;
use Botble\RealEstate\Http\Controllers\Fronts\RegisterController;
use Botble\RealEstate\Http\Controllers\Fronts\ResetPasswordController;
use Botble\RealEstate\Http\Controllers\Fronts\ReviewController;
use Botble\RealEstate\Http\Middleware\EnsureAccountIsApproved;
use Botble\RealEstate\Models\Account;
use Botble\RealEstate\Models\Project;
use Botble\RealEstate\Models\Property;
use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

if (defined('THEME_MODULE_SCREEN_NAME')) {
    Route::group(['namespace' => 'Botble\RealEstate\Http\Controllers\Fronts'], function () {
        Theme::registerRoutes(function () {
            $projectsPrefix = SlugHelper::getPrefix(Project::class, 'projects') ?: 'projects';

            $propertiesPrefix = SlugHelper::getPrefix(Property::class, 'properties') ?: 'properties';

            Route::match(theme_option('projects_list_page_id') ? ['POST'] : ['POST', 'GET'], $projectsPrefix, 'PublicController@getProjects')
                ->name('public.projects');

            Route::match(theme_option('properties_list_page_id') ? ['POST'] : ['POST', 'GET'], $propertiesPrefix, 'PublicController@getProperties')
                ->name('public.properties');

            Route::match(['POST', 'GET'], RealEstateHelper::getPageSlug('projects_city') . '/{slug}', 'PublicController@getProjectsByCity')
                ->name('public.projects-by-city');

            Route::match(['POST', 'GET'], RealEstateHelper::getPageSlug('properties_city') . '/{slug}', 'PublicController@getPropertiesByCity')
                ->name('public.properties-by-city');

            Route::match(['POST', 'GET'], RealEstateHelper::getPageSlug('projects_state') . '/{slug}', 'PublicController@getProjectsByState')
                ->name('public.projects-by-state');

            Route::match(['POST', 'GET'], RealEstateHelper::getPageSlug('properties_state') . '/{slug}', 'PublicController@getPropertiesByState')
                ->name('public.properties-by-state');

            if (! RealEstateHelper::isDisabledPublicProfile()) {
                Route::get(SlugHelper::getPrefix(Account::class, 'agents') ?: 'agents', 'PublicController@getAgents')
                    ->name('public.agents');
            }

            Route::post('send-consult', 'PublicController@postSendConsult')
                ->name('public.send.consult');

            Route::get('currency/switch/{code?}', [
                'as' => 'public.change-currency',
                'uses' => 'PublicController@changeCurrency',
            ]);

            Route::name('public.account.')->group(function () {
                Route::middleware('account.guest')->group(function () {
                    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
                    Route::post('login', [LoginController::class, 'login'])->name('login.post');
                    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
                    Route::post('register', [RegisterController::class, 'register'])->name('register.post');
                    Route::get('verify', [RegisterController::class, 'getVerify'])->name('verify');
                    Route::get('password/request', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
                    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
                    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
                    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
                });

                Route::prefix('register/confirm')
                    ->middleware(setting('verify_account_email', false) ? 'account.guest' : 'account')
                    ->group(function () {
                        Route::get('resend', [RegisterController::class, 'resendConfirmation'])->name('resend_confirmation');
                        Route::get('{user}', [RegisterController::class, 'confirm'])->name('confirm');
                    });

                Route::post('account/logout', [LoginController::class, 'logout'])
                    ->middleware('account')
                    ->name('logout');

                Route::prefix('account')->middleware(['account', EnsureAccountIsApproved::class])->group(function () {
                    Route::get('pending-approval', [PublicAccountController::class, 'getPendingApproval'])->name('pending-approval');
                    Route::get('dashboard', [PublicAccountController::class, 'getDashboard'])->name('dashboard');
                    Route::get('settings', [PublicAccountController::class, 'getSettings'])->name('settings');
                    Route::post('settings', [PublicAccountController::class, 'postSettings'])->name('post.settings');
                    Route::put('security', [PublicAccountController::class, 'postSecurity'])->name('post.security');
                    Route::post('avatar', [PublicAccountController::class, 'postAvatar'])->name('avatar');
                    Route::get('packages', [PublicAccountController::class, 'getPackages'])->name('packages');
                    Route::get('transactions', [PublicAccountController::class, 'getTransactions'])->name('transactions');
                    Route::prefix('coupon')->name('coupon.')->group(function () {
                        Route::post('apply', [CouponController::class, 'apply'])->name('apply');
                        Route::post('remove', [CouponController::class, 'remove'])->name('remove');
                        Route::get('refresh/{id}', [CouponController::class, 'refresh'])->name('refresh');
                    });
                    Route::match(['GET', 'POST'], 'consults', [ConsultController::class, 'index'])->name('consults.index');
                    Route::get('consults/{id}', [ConsultController::class, 'show'])->name('consults.show')->wherePrimaryKey();

                    Route::prefix('ajax')->group(function () {
                        Route::get('activity-logs', [PublicAccountController::class, 'getActivityLogs'])->name('activity-logs');
                        Route::get('transactions', [PublicAccountController::class, 'ajaxGetTransactions'])->name('ajax.transactions');
                        Route::post('upload', [PublicAccountController::class, 'postUpload'])->name('upload');
                        Route::post('upload-from-editor', [PublicAccountController::class, 'postUploadFromEditor'])->name('upload-from-editor');
                        Route::get('packages', [PublicAccountController::class, 'ajaxGetPackages'])->name('ajax.packages');
                        Route::put('packages', [PublicAccountController::class, 'ajaxSubscribePackage'])->name('ajax.package.subscribe');
                    });

                    Route::get('packages/{id}/subscribe', [PublicAccountController::class, 'getSubscribePackage'])
                        ->name('package.subscribe')
                        ->wherePrimaryKey();
                    Route::get('packages/{id}/subscribe/callback', [PublicAccountController::class, 'getPackageSubscribeCallback'])
                        ->name('package.subscribe.callback')
                        ->wherePrimaryKey();

                    Route::prefix('properties')->name('properties.')->group(function () {
                        Route::resource('', AccountPropertyController::class)->parameters(['' => 'property']);
                        Route::post('renew/{id}', [AccountPropertyController::class, 'renew'])->name('renew')->wherePrimaryKey();
                    });

                    Route::prefix('invoices')
                        ->name('invoices.')
                        ->controller(InvoiceController::class)
                        ->group(function () {
                            Route::match(['GET', 'POST'], '/', 'index')->name('index');
                            Route::get('{id}', 'show')->name('show')
                                ->wherePrimaryKey();
                            Route::get('{id}/generate', [InvoiceController::class, 'generate'])
                                ->name('generate')
                                ->wherePrimaryKey();
                        });

                    Route::get('custom-fields/info', [CustomFieldController::class, 'getInfo'])
                        ->name('custom-fields.get-info');
                });
            });

            Route::post('ajax/review/{slug}', [ReviewController::class, 'store'])
                ->middleware(['account', RequiresJsonRequestMiddleware::class])
                ->name('public.ajax.review.store');

            Route::get('ajax/review/{slug}', [ReviewController::class, 'index'])
                ->middleware(RequiresJsonRequestMiddleware::class)
                ->name('public.ajax.review.index');
        });

        Route::group(['prefix' => 'payments', 'middleware' => ['web', 'core']], function () {
            Route::post('checkout', 'CheckoutController@postCheckout')->name('payments.checkout');
        });
    });
}
