<?php

namespace App\Http\Middleware;


use App\Permission\DashboardPermission;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Symfony\Component\HttpFoundation\IpUtils;

class CheckForMaintenanceMode {

    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array
     */
    protected $except = [
        '/admin/',
        '/admin/*',
        '/api/softswissCallback/*',
        '/api/slotegratorCallback',
        '/api/slotegratorCallback/*',
        '/api/phoenixCallback'
    ];

    /**
     * The application implementation.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Illuminate\Foundation\Http\Exceptions\MaintenanceModeException
     */
    public function handle($request, Closure $next) {
        if ($this->app->isDownForMaintenance() && (auth('sanctum')->guest() || !auth('sanctum')->user()->checkPermission(new DashboardPermission()))) {
            $data = json_decode(file_get_contents($this->app->storagePath().'/framework/down'), true);

            if (isset($data['allowed']) && IpUtils::checkIp($request->ip(), (array) $data['allowed'])) {
                return $next($request);
            }

            if ($this->inExceptArray($request)) {
                return $next($request);
            }

            throw new MaintenanceModeException(now()->getTimestamp(), $data['retry'], '');
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should be accessible in maintenance mode.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request) {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }

}
