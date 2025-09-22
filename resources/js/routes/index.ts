import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../wayfinder';
/**
 * @see vendor/laravel/pulse/src/PulseServiceProvider.php:106
 * @route '/pulse'
 */
export const pulse = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pulse.url(options),
    method: 'get',
});

pulse.definition = {
    methods: ['get', 'head'],
    url: '/pulse',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see vendor/laravel/pulse/src/PulseServiceProvider.php:106
 * @route '/pulse'
 */
pulse.url = (options?: RouteQueryOptions) => {
    return pulse.definition.url + queryParams(options);
};

/**
 * @see vendor/laravel/pulse/src/PulseServiceProvider.php:106
 * @route '/pulse'
 */
pulse.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pulse.url(options),
    method: 'get',
});

/**
 * @see vendor/laravel/pulse/src/PulseServiceProvider.php:106
 * @route '/pulse'
 */
pulse.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pulse.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Welcome\WelcomeController::home
 * @see app/Http/Controllers/Welcome/WelcomeController.php:20
 * @route '/'
 */
export const home = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
});

home.definition = {
    methods: ['get', 'head'],
    url: '/',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Welcome\WelcomeController::home
 * @see app/Http/Controllers/Welcome/WelcomeController.php:20
 * @route '/'
 */
home.url = (options?: RouteQueryOptions) => {
    return home.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Welcome\WelcomeController::home
 * @see app/Http/Controllers/Welcome/WelcomeController.php:20
 * @route '/'
 */
home.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Welcome\WelcomeController::home
 * @see app/Http/Controllers/Welcome/WelcomeController.php:20
 * @route '/'
 */
home.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: home.url(options),
    method: 'head',
});

/**
 * @see routes/settings.php:20
 * @route '/settings/appearance'
 */
export const appearance = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: appearance.url(options),
    method: 'get',
});

appearance.definition = {
    methods: ['get', 'head'],
    url: '/settings/appearance',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see routes/settings.php:20
 * @route '/settings/appearance'
 */
appearance.url = (options?: RouteQueryOptions) => {
    return appearance.definition.url + queryParams(options);
};

/**
 * @see routes/settings.php:20
 * @route '/settings/appearance'
 */
appearance.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: appearance.url(options),
    method: 'get',
});

/**
 * @see routes/settings.php:20
 * @route '/settings/appearance'
 */
appearance.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: appearance.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:23
 * @route '/register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
});

register.definition = {
    methods: ['get', 'head'],
    url: '/register',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:23
 * @route '/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:23
 * @route '/register'
 */
register.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:23
 * @route '/register'
 */
register.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: register.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
});

login.definition = {
    methods: ['get', 'head'],
    url: '/login',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '/login'
 */
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '/login'
 */
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:43
 * @route '/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
});

logout.definition = {
    methods: ['post'],
    url: '/logout',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:43
 * @route '/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:43
 * @route '/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
});
