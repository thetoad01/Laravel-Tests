<?php

namespace App\Http\Controllers\Scrape;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// models
use App\Models\DealerInspireVdp;
use App\Models\DealerInspireVehicle;

use Carbon\Carbon;
use PHPHtmlParser\Dom;
use GuzzleHttp\Client;

class DealerInspireVdpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DealerInspireVdp::where('visited', 0)->get();

        return view('scrape.dealer-inspire.vdps.index', [
            'vdp_count' => count($data),
            'vdps' => $data,
        ]);
    }

    /**
     * Crawl vdp links
     *
     * @return \Illuminate\Http\Response
     */
    public function crawl()
    {
        $dealerInspireVdp = DealerInspireVdp::where('visited', 0)
            // ->where('vdp_url', 'like', 'https://www.kengarffvw.com%')
            ->inRandomOrder()
            ->first();

        if (!$dealerInspireVdp) {
            return redirect()->route('vdp.dealer-inspire.index');
        }

        // set current timestamp
        $now = Carbon::now()->toDateTimeString();

        // Try using guzzle
        $client = new Client();
        // make try request and abort on exception
        try {
            $response = $client->request('GET', $dealerInspireVdp->vdp_url, [
                'allow_redirects' => false,
                'timeout' => 240,
            ]);
            $status_code = $response->getStatusCode();
        } catch (GuzzleException $e) {
            abort(404);
        }

        // mark vdp visited
        $dealerInspireVdp->visited = 1;
        $dealerInspireVdp->http_response_code = $status_code;
        $dealerInspireVdp->updated_at = $now;
        $dealerInspireVdp->save();

        if ($status_code == 200) {
            $data = $response->getBody()->getContents();
        } else {
            return view('scrape.dealer-inspire.vdps.crawl', [
                'vehicle' => '',
                'error' => 'Failed to parse VDP.',
                'http_status_code' => $status_code,
            ]);
            // abort(404);
        }

        // dd($data);

        $dom = new Dom;
        try {
            $dom->loadStr($data);
        } catch (\Throwable $th) {
            dd($th);
            return view('scrape.dealer-inspire.vdps.crawl', [
                'vehicle' => '',
                'error' => 'Failed to parse VDP.',
                'http_status_code' => $status_code,
            ]);
        }

        try {
            $vin = $dom->find('meta[itemprop=serialNumber]')->content;
        } catch (\Throwable $th) {
            return view('scrape.dealer-inspire.vdps.crawl', [
                'vehicle' => '',
                'error' => 'Failed to parse itemprop=serialNumber',
                'http_status_code' => $status_code,
            ]);
        }

        // insert the vehicle in db
        $result = DealerInspireVehicle::firstOrCreate(
            ['url' => $dealerInspireVdp->vdp_url],
            [
                'dealer' => $dom->find('meta[property=og:site_name]')->content ?? '',
                // 'vin' => $dom->find('meta[itemprop=serialNumber]')->content ?? '',
                'vin' => $vin,
                'year' => $dom->find('meta[itemprop=releaseDate]')->content ?? '',
                'make' => $dom->find('meta[itemprop=brand]')->content ?? '',
                'model' => $dom->find('meta[itemprop=model]')->content ?? '',
                'trim' => null,
                'name' => $dom->find('meta[itemprop=name]')->content ?? '',
                'exterior_color' => $dom->find('meta[itemprop=color]')->content ?? '',
                'interior_color' => null,
                'stock_number' => $dom->find('meta[itemprop=sku]')->content ?? '',
            ]
        );

        return view('scrape.dealer-inspire.vdps.crawl', [
            'vehicle' => $result,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // set current timestamp
        $now = Carbon::now()->toDateTimeString();
        // get url
        $dealerInspireVdp = DealerInspireVdp::find($id);

        // Try using guzzle
        $client = new Client();
        // make try request and abort on exception
        try {
            $response = $client->request('GET', $dealerInspireVdp->vdp_url, ['allow_redirects' => false]);
            $status_code = $response->getStatusCode();
        } catch (GuzzleException $e) {
            abort(404);
        }

        // mark vdp visited
        $dealerInspireVdp->visited = 1;
        $dealerInspireVdp->http_response_code = $status_code;
        $dealerInspireVdp->updated_at = $now;
        $dealerInspireVdp->save();

        if ($status_code == 200) {
            $data = $response->getBody()->getContents();
        } else {
            return response()->json([
                'error' => 'Failed to parse VDP.',
                'http_status_code' => $status_code,
            ]);
            // abort(404);
        }

        $dom = new Dom;
        try {
            $dom->loadStr($data);
        } catch (\Throwable $th) {
            dd($th);
        }

        // insert the vehicle in db
        $result = DealerInspireVehicle::firstOrCreate(
            ['url' => $dealerInspireVdp->vdp_url],
            [
                'dealer' => $dom->find('meta[property=og:site_name]')->content ?? '',
                'vin' => $dom->find('meta[itemprop=serialNumber]')->content ?? '',
                'year' => $dom->find('meta[itemprop=releaseDate]')->content ?? '',
                'make' => $dom->find('meta[itemprop=brand]')->content ?? '',
                'model' => $dom->find('meta[itemprop=model]')->content ?? '',
                'trim' => null,
                'name' => $dom->find('meta[itemprop=name]')->content ?? '',
                'exterior_color' => $dom->find('meta[itemprop=color]')->content ?? '',
                'interior_color' => null,
                'stock_number' => $dom->find('meta[itemprop=sku]')->content ?? '',
            ]
        );

        // return response()->json($result);
        return view('scrape.dealer-inspire.vdps.show', [
            'vehicle' => $result,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
