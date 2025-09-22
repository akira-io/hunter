import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Profile\AboutController::__invoke
* @see app/Http/Controllers/Profile/AboutController.php:20
* @route '/profile/about'
*/
const AboutController = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: AboutController.url(options),
    method: 'patch',
})

AboutController.definition = {
    methods: ["patch"],
    url: '/profile/about',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Profile\AboutController::__invoke
* @see app/Http/Controllers/Profile/AboutController.php:20
* @route '/profile/about'
*/
AboutController.url = (options?: RouteQueryOptions) => {
    return AboutController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Profile\AboutController::__invoke
* @see app/Http/Controllers/Profile/AboutController.php:20
* @route '/profile/about'
*/
AboutController.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: AboutController.url(options),
    method: 'patch',
})

export default AboutController