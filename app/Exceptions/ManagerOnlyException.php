<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;

class ManagerOnlyException extends AuthorizationException
{
    public function render($request)
    {
        return response()->json([
            'message' => 'Only managers are allowed.'
        ], 403);
    }
}
