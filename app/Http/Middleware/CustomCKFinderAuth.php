<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Helper\CKFinder as CKFinderHelper;

class CustomCKFinderAuth
{
 /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        # Set the authentication configuration for CKFinder
        config(['ckfinder.authentication' => function() {
            return Auth::check();
        }]);
        # Configure the private folders for the user
        if (Auth::check()) {
            $this->configureUserPrivateFolders(Auth::user()->id);
        }
        return $next($request);
    }

    /**
     * Configure CKFinder private folders per user.
     *
     * @param string $userDirectory
     * @return void
     */
    protected function configureUserPrivateFolders(string $userDirectory): void
    {
        $ckfinder = new CKFinderHelper($userDirectory);
        $config   = $ckfinder->getConfiguration();
        config([
            'ckfinder.privateDir'       => $config['privateDir'],
            'ckfinder.backends.default' => $config['backends'],
        ]);
    }
}
