<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    private const SKIP_ROUTES = [
        'login', 'logout',
        'api.server-resources', 'api.health', 'api.notifications.unread',
        'api.notifications.mark-read', 'api.reports.status',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log write operations by authenticated users
        if (!Auth::check() || in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $response;
        }

        $routeName = $request->route()?->getName() ?? '';

        if (in_array($routeName, self::SKIP_ROUTES)) {
            return $response;
        }

        // Do NOT log operations that failed validation — they redirected back with
        // errors in the flash session, meaning no data actually changed.
        if ($response instanceof RedirectResponse) {
            $session = $response->getSession();
            if ($session && $session->has('errors')) {
                return $response;
            }
        }

        // Skip server-error responses
        if ($response->getStatusCode() >= 500) {
            return $response;
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => $this->buildActionDescription($request),
            'model_type' => $this->inferModelType($routeName),
            'model_id'   => $this->extractModelId($request),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return $response;
    }

    private function buildActionDescription(Request $request): string
    {
        $method = $request->method();
        $path   = $request->path();

        $verbMap = [
            'POST'   => 'Membuat data baru',
            'PUT'    => 'Memperbarui data',
            'PATCH'  => 'Memperbarui data',
            'DELETE' => 'Menghapus data',
        ];

        $verb = $verbMap[$method] ?? $method;

        return "{$verb} — {$path}";
    }

    private function inferModelType(string $routeName): ?string
    {
        $map = [
            'products'     => 'Product',
            'categories'   => 'Category',
            'warehouses'   => 'Warehouse',
            'suppliers'    => 'Supplier',
            'transactions' => 'InventoryTransaction',
            'transfers'    => 'InventoryTransaction',
            'users'        => 'User',
        ];

        foreach ($map as $segment => $model) {
            if (str_contains($routeName, $segment)) {
                return $model;
            }
        }

        return null;
    }

    /**
     * Extract the primary key of the bound model from the route parameters.
     * Works for both Eloquent route-model binding and plain integer IDs.
     */
    private function extractModelId(Request $request): ?int
    {
        $params = $request->route()?->parameters() ?? [];

        foreach ($params as $value) {
            if ($value instanceof Model) {
                return (int) $value->getKey();
            }

            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }
}
