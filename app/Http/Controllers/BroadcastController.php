<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class BroadcastController extends Controller
{
    protected $host;
    protected $headers;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->host = env('API_URL', 'https://dev.api.customerpay.me');
        $this->headers = ['headers' => ['x-access-token' => Cookie::get('api_token')]];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $url = env('API_URL', 'https://dev.api.customerpay.me') . '/store';

        $all_messages_url = env('API_URL', 'https://dev.api.customerpay.me') . '/message/get';

        try {

            $client = new Client;
            $payload = ['headers' => ['x-access-token' => Cookie::get('api_token')]];

            $response = $client->request("GET", $url, $payload);
            $all_messages_response = $client->request("GET", $all_messages_url, $payload);

            $statusCode = $response->getStatusCode();
            $all_messages_statusCode = $all_messages_response->getStatusCode();

            $body = $response->getBody();
            $all_messages_body = $all_messages_response->getBody();

            

            $Stores = json_decode($body);
            $broadcasts_body = json_decode($all_messages_body);

            $broadcasts = $broadcasts_body->data->broadcasts;
            // return $broadcasts;
            $data = [
                'stores' => $Stores->data->stores,
                'broadcasts' => $broadcasts
            ];

            if ($statusCode == 200) {
                // return $Stores->data->stores;
                return view('backend.broadcasts.index')->with('data', $data);
            }
        } catch (RequestException $e) {
            Log::error('Catch error: Create Broadcast' . $e->getMessage());
            $request->session()->flash('message', 'Failed to fetch customer, please try again');
            return view('backend.broadcasts.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $client = new Client();
            $response = $client->get($this->host . '/message/numbers', ['headers' => ['x-access-token' => Cookie::get('api_token')]]);
            $template = $request->input("temp");



            if ($response->getStatusCode() == 200) {

                // dd($template);
                $res = json_decode($response->getBody());
                $customers = get_object_vars($res->data);
                return view('backend.broadcasts.index')->with(['customers' => $customers, "template" => $template]);
            }
        } catch (RequestException $e) {
            Log::error('Catch error: Create Broadcast' . $e->getMessage());
            $request->session()->flash('message', 'Failed to fetch customer, please try again');
            return view('backend.broadcasts.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request);
        // return $request->input('customer');
        if ($request->input('message') == 'other') {
            $cook = $request->input('txtmessage');
        } else {
            $cook = $request->input('message');
        }
        $user_id = Cookie::get('user_id');
        $url = env('API_URL', 'https://dev.api.customerpay.me') . "/message/send";

        try {
            $client = new Client();
            $payload = [
                'headers' => [
                    'x-access-token' => Cookie::get('api_token')
                ],
                "json" => [
                    "numbers" => $request->input('customer'),
                    "message" => $cook
                ]
            ];

            $req = $client->request('POST', $url, $payload);
            $statusCode = $req->getStatusCode();
            $response = json_decode($req->getBody()->getContents());

            if ($statusCode == 200) {

                $request->session()->flash('success', $response->message);
                return back();
            } else {

                $message = isset($response->Message) ? $response->Message : $response->message;
                $request->session()->flash('message', $message);
                return back();
            }
        } catch (RequestException $e) {

            //log error;
            Log::error('Catch error: BroadcastController - ' . $e->getMessage());

            if ($e->hasResponse()) {
                // get response to catch 4xx errors
                $response = json_decode($e->getResponse()->getBody());
                $request->session()->flash('error', "Make sure all fields are filled .\n Make sure the description is more than 10 characters");
                return back();
            }
            // check for 500 server error
            return view('errors.500');
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function template(Request $request)
    {
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
