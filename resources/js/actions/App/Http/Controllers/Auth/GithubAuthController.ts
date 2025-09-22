import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Auth\GithubAuthController::redirect
 * @see app/Http/Controllers/Auth/GithubAuthController.php:20
 * @route '/auth/github'
 */
export const redirect = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: redirect.url(options),
    method: 'get',
});

redirect.definition = {
    methods: ['get', 'head'],
    url: '/auth/github',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Auth\GithubAuthController::redirect
 * @see app/Http/Controllers/Auth/GithubAuthController.php:20
 * @route '/auth/github'
 */
redirect.url = (options?: RouteQueryOptions) => {
    return redirect.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Auth\GithubAuthController::redirect
 * @see app/Http/Controllers/Auth/GithubAuthController.php:20
 * @route '/auth/github'
 */
redirect.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: redirect.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Auth\GithubAuthController::redirect
 * @see app/Http/Controllers/Auth/GithubAuthController.php:20
 * @route '/auth/github'
 */
redirect.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: redirect.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Auth\GithubAuthController::callback
 * @see app/Http/Controllers/Auth/GithubAuthController.php:31
 * @route '/auth/github/callback'
 */
export const callback = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: callback.url(options),
    method: 'get',
});

callback.definition = {
    methods: ['get', 'head'],
    url: '/auth/github/callback',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Auth\GithubAuthController::callback
 * @see app/Http/Controllers/Auth/GithubAuthController.php:31
 * @route '/auth/github/callback'
 */
callback.url = (options?: RouteQueryOptions) => {
    return callback.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Auth\GithubAuthController::callback
 * @see app/Http/Controllers/Auth/GithubAuthController.php:31
 * @route '/auth/github/callback'
 */
callback.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: callback.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Auth\GithubAuthController::callback
 * @see app/Http/Controllers/Auth/GithubAuthController.php:31
 * @route '/auth/github/callback'
 */
callback.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: callback.url(options),
    method: 'head',
});

const GithubAuthController = { redirect, callback };

export default GithubAuthController;
