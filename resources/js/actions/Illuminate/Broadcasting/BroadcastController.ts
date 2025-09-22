import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/broadcasting/auth'
*/
export const authenticate = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: authenticate.url(options),
    method: 'get',
})

authenticate.definition = {
    methods: ["get","post","head"],
    url: '/broadcasting/auth',
} satisfies RouteDefinition<["get","post","head"]>

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/broadcasting/auth'
*/
authenticate.url = (options?: RouteQueryOptions) => {
    return authenticate.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/broadcasting/auth'
*/
authenticate.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: authenticate.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/broadcasting/auth'
*/
authenticate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: authenticate.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route '/broadcasting/auth'
*/
authenticate.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: authenticate.url(options),
    method: 'head',
})

const BroadcastController = { authenticate }

export default BroadcastController