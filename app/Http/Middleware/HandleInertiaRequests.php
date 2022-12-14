<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user()
            ],
            'user' => $request->user(),
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success')
            ],
            'online' => User::where('online', 1)
                    ->where('id', '!=',$request->user()->id ?? "")
                    ->with(['conversation' => function ($q){
                        $q->whereHas('conversationUsers', function ($q) {
                            $q->where('user_id',  request()->user()->id ?? "");
                        });
                    }])
                    ->get() ?? [],
            'offline' => User::where('online', 0)
                    ->where('id','!=', $request->user()->id?? "")
                    ->with(['conversation' => function ($q){
                        $q->whereHas('conversationUsers', function ($q) {
                            $q->where('user_id',  request()->user()->id ?? "");
                        });
                    }])
                    ->get() ?? [],
            'public_menus' => [
                "About Us" => url('about_us')
            ]
        ]);
    }
}
