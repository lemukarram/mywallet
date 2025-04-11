<?php
use Illuminate\Auth\AuthenticationException;

// Add this to the renderable method
$this->renderable(function (AuthenticationException $e, $request) {
    if ($request->is('api/*')) {
        return response()->json([
            'message' => 'Unauthenticated. Please log in.',
            'error' => 'authentication_required'
        ], 401);
    }
    
    // Only include this if you have web routes
    return redirect()->guest(route('login'));
});