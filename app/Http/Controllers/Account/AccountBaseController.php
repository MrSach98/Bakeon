<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\StorefrontController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

abstract class AccountBaseController extends StorefrontController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);

        if (! auth()->check()) {
            abort(redirect('/'));
        }

        View::share('accountUser', auth()->user());
    }
}