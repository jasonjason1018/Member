<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\AddressType;
use App\Models\Area;
use App\Models\City;
use App\Models\Document;
use App\Models\User;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $path;
    protected $address;
    protected $address_type;
    protected $area;
    protected $city;
    protected $document;
    protected $user;
    protected $request;
    protected $post;
    protected $get;

    public function __construct(request $request)
    {
        $uri = explode('/', request()->path());
        $this->path = $uri[0] == ''?request()->path():$uri[0];
        $this->address = new Address();
        $this->address_type = new AddressType();
        $this->area = new Area();
        $this->city = new City();
        $this->document = new Document();
        $this->user = new User();
        $this->request = $request;
        $this->post = $request->input();
        $this->get = $request->query();
    }

    protected function view()
    {
        return view($this->path);
    }
}
