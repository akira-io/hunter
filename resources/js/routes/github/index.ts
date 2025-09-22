import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\GithubAuthController::login
* @see app/Http/Controllers/Auth/GithubAuthController.php:20
* @route '/auth/github'
*/
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

login.definition = {
    methods: ["get","head"],
    url: '/auth/github',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\GithubAuthController::login
* @see app/Http/Controllers/Auth/GithubAuthController.php:20
* @route '/auth/github'
*/
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\GithubAuthController::login
* @see app/Http/Controllers/Auth/GithubAuthController.php:20
* @route '/auth/github'
*/
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\GithubAuthController::login
* @see app/Http/Controllers/Auth/GithubAuthController.php:20
* @route '/auth/github'
*/
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\GithubAuthController::callback
* @see app/Http/Controllers/Auth/GithubAuthController.php:31
* @route '/auth/github/callback'
*/
export const callback = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: callback.url(options),
    method: 'get',
})

callback.definition = {
    methods: ["get","head"],
    url: '/auth/github/callback',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\GithubAuthController::callback
* @see app/Http/Controllers/Auth/GithubAuthController.php:31
* @route '/auth/github/callback'
*/
callback.url = (options?: RouteQueryOptions) => {
    return callback.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\GithubAuthController::callback
* @see app/Http/Controllers/Auth/GithubAuthController.php:31
* @route '/auth/github/callback'
*/
callback.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: callback.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\GithubAuthController::callback
* @see app/Http/Controllers/Auth/GithubAuthController.php:31
* @route '/auth/github/callback'
*/
callback.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: callback.url(options),
    method: 'head',
})

const github = {
    login: Object.assign(login, login),
    callback: Object.assign(callback, callback),
}

export default github