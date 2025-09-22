import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Followable\GetHuntingsController::__invoke
* @see app/Http/Controllers/Followable/GetHuntingsController.php:23
* @route '/followable/followings'
*/
const GetHuntingsController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: GetHuntingsController.url(options),
    method: 'get',
})

GetHuntingsController.definition = {
    methods: ["get","head"],
    url: '/followable/followings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Followable\GetHuntingsController::__invoke
* @see app/Http/Controllers/Followable/GetHuntingsController.php:23
* @route '/followable/followings'
*/
GetHuntingsController.url = (options?: RouteQueryOptions) => {
    return GetHuntingsController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Followable\GetHuntingsController::__invoke
* @see app/Http/Controllers/Followable/GetHuntingsController.php:23
* @route '/followable/followings'
*/
GetHuntingsController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: GetHuntingsController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Followable\GetHuntingsController::__invoke
* @see app/Http/Controllers/Followable/GetHuntingsController.php:23
* @route '/followable/followings'
*/
GetHuntingsController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: GetHuntingsController.url(options),
    method: 'head',
})

export default GetHuntingsController