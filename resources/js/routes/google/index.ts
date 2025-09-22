import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../wayfinder';
/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::login
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:21
 * @route '/auth/google'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
});

login.definition = {
    methods: ['get', 'head'],
    url: '/auth/google',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::login
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:21
 * @route '/auth/google'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::login
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:21
 * @route '/auth/google'
 */
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::login
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:21
 * @route '/auth/google'
 */
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::callback
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:32
 * @route '/auth/google/callback'
 */
export const callback = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: callback.url(options),
    method: 'get',
});

callback.definition = {
    methods: ['get', 'head'],
    url: '/auth/google/callback',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::callback
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:32
 * @route '/auth/google/callback'
 */
callback.url = (options?: RouteQueryOptions) => {
    return callback.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::callback
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:32
 * @route '/auth/google/callback'
 */
callback.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: callback.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::callback
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:32
 * @route '/auth/google/callback'
 */
callback.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: callback.url(options),
    method: 'head',
});

const google = {
    login: Object.assign(login, login),
    callback: Object.assign(callback, callback),
};

export default google;
