<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::view('/home', 'home')->name('home');

// test scrape routes
Route::get('/scrape', 'Scrape\HtmlParserController@test');

// test sitemap routes
Route::get('/scrape/cdk-sitemap/{cdk_sitemap_id}', 'Scrape\HtmlParserController@getCdkSitemap');



/*******************************************************************
 * Open Weather
 *******************************************************************/
Route::resource('/weather', 'Weather\OpenweatherController');


/*******************************************************************
 * CDK Sitemaps
 *******************************************************************/
// protected
Route::group(['middleware' => ['auth']], function () {
    Route::get('/scrape/cdk/create', 'Scrape\CdkController@create');
    Route::post('/scrape/cdk', 'Scrape\CdkController@store');
    Route::get('/sitemap/cdk/{id}', 'Scrape\CdkController@edit')->name('sitemap.cdk.edit');
    Route::put('/sitemap/cdk/{id}', 'Scrape\CdkController@update')->name('sitemap.cdk.update');
});
Route::get('/scrape/cdk', 'Scrape\CdkController@index')->name('scrape.cdk');
Route::get('/scrape/cdk/{id}', 'Scrape\CdkController@show');
Route::get('/scrape/cdk/{id}/edit', 'Scrape\CdkController@edit');
Route::patch('/scrape/cdk/{id}', 'Scrape\CdkController@update');
Route::delete('/scrape/cdk/{id}', 'Scrape\CdkController@destroy');
Route::get('/scrape/count', 'Scrape\HtmlParserController@getNumberToCrawl');
// process all sitemaps
Route::get('/scrape/sitemaps/all', 'Scrape\HtmlParserController@processSitemaps');


/*******************************************************************
 * Dealer Inspire (/dealer-inspire-inventory/inventory_sitemap)
 *******************************************************************/
// Sitemap
Route::get('/scrape/sitemap/dealer-inspire', 'Scrape\DealerInspireSitemapController@index')->name('sitemap.dealer-inspire.index');
Route::get('/scrape/sitemap/dealer-inspire/create', 'Scrape\DealerInspireSitemapController@create')->name('sitemap.dealer-inspire.create');
Route::post('/scrape/sitemap/dealer-inspire', 'Scrape\DealerInspireSitemapController@store')->name('sitemap.dealer-inspire.store');
Route::get('/scrape/sitemap/dealer-inspire/{id}', 'Scrape\DealerInspireSitemapController@show')->name('sitemap.dealer-inspire.show');
// VDP
Route::get('/scrape/vdp/dealer-inspire', 'Scrape\DealerInspireVdpController@index')->name('vdp.dealer-inspire.index');
Route::get('/scrape/vdp/dealer-inspire/crawl', 'Scrape\DealerInspireVdpController@crawl')->name('vdp.dealer-inspire.crawl');
Route::get('/scrape/vdp/dealer-inspire/{id}', 'Scrape\DealerInspireVdpController@show')->name('vdp.dealer-inspire.show');



/*******************************************************************
 * Scraped Vehicles
 *******************************************************************/
Route::resource('/vehicles', 'Scrape\VehicleController');
// this is a test route
Route::get('/vehicle/check-link', 'Vehicle\ActiveLinkController@check');
// deleted vehicles
Route::get('/deleted/vehicles', 'Scrape\DeletedVehicleController@index')->name('vehicles.deleted.index');


/*******************************************************************
 * NHTSA VIN Decode
 *******************************************************************/
Route::get('/nhtsa/decode/{vin}/{year}', 'Nhtsa\NhtsaController@decode');


/*******************************************************************
 * Laracasts Practical Vue Components
 *******************************************************************/
Route::view('/vue-components/smooth-scrolling', 'practical-vue-components.smooth-scrolling');
Route::view('/vue-components/context-menu', 'practical-vue-components.context-menu');
Route::view('/vue-components/confirmation-button', 'practical-vue-components.confirmation-button');
Route::view('/vue-components/conditional-visibility', 'practical-vue-components.conditional-visibility');
Route::view('/vue-components/modal', 'practical-vue-components.modal');


/*******************************************************************
 * Security Test Routes
 *******************************************************************/
Route::get('/security/generate-uuid', 'Security\UuidController@generate');



/*******************************************************************
 * TESTS
 *******************************************************************/
Route::view('/something.php', 'Tests.something');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/test', 'Tests\MysqlVehicleController@index');
Route::get('/test/move', 'Tests\MysqlVehicleController@move')->middleware('auth');
Route::get('/test/move/cdklink', 'Tests\MysqlVehicleController@moveCdkLink')->middleware('auth');
