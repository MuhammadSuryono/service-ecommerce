<?php


namespace App\Http\Controllers\RajaOngkir;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RajaOngkirController extends Controller
{
    private $API_KEY;
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->API_KEY = $this->API_KEY_RAJA_ONGKIR;
    }

    public function GetProvince()
    {
        $req = $this->http_request_get($this->BaseUrlRajaOngkir().'province',[],["key: ".$this->API_KEY]);
        return $this->BuildResponse(true, "Success", $req, 200);
    }

    public function GetCity()
    {
        $req = $this->http_request_get($this->BaseUrlRajaOngkir().'city',[],["key: ".$this->API_KEY]);
        return $this->BuildResponse(true, "Success", $req, 200);
    }

    public function GetCost()
    {
        $this->validate($this->request, [
            'origin' => 'required',
            'destination' => 'required',
            'weight' => 'required',
            'courier' => 'required',
        ]);

        $origin = $this->request->input('origin');
        $destination = $this->request->input('destination');
        $weight = $this->request->input('weight');
        $courier = $this->request->input('courier');

        $body = [
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier,
        ];

        $req = $this->request_API_POST($body, $this->BaseUrlRajaOngkir().'cost', ['key: '.$this->API_KEY]);
        return $this->BuildResponse(true, "Success", $req, 200);
    }
}
