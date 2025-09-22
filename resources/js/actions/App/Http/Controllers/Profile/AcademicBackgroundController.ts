import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Profile\AcademicBackgroundController::store
* @see app/Http/Controllers/Profile/AcademicBackgroundController.php:24
* @route '/profile/academic-background'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/profile/academic-background',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Profile\AcademicBackgroundController::store
* @see app/Http/Controllers/Profile/AcademicBackgroundController.php:24
* @route '/profile/academic-background'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Profile\AcademicBackgroundController::store
* @see app/Http/Controllers/Profile/AcademicBackgroundController.php:24
* @route '/profile/academic-background'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Profile\AcademicBackgroundController::destroy
* @see app/Http/Controllers/Profile/AcademicBackgroundController.php:38
* @route '/profile/academic-background/{academicBackground}'
*/
export const destroy = (args: { academicBackground: number | { id: number } } | [academicBackground: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/profile/academic-background/{academicBackground}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Profile\AcademicBackgroundController::destroy
* @see app/Http/Controllers/Profile/AcademicBackgroundController.php:38
* @route '/profile/academic-background/{academicBackground}'
*/
destroy.url = (args: { academicBackground: number | { id: number } } | [academicBackground: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { academicBackground: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { academicBackground: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            academicBackground: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        academicBackground: typeof args.academicBackground === 'object'
        ? args.academicBackground.id
        : args.academicBackground,
    }

    return destroy.definition.url
            .replace('{academicBackground}', parsedArgs.academicBackground.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Profile\AcademicBackgroundController::destroy
* @see app/Http/Controllers/Profile/AcademicBackgroundController.php:38
* @route '/profile/academic-background/{academicBackground}'
*/
destroy.delete = (args: { academicBackground: number | { id: number } } | [academicBackground: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const AcademicBackgroundController = { store, destroy }

export default AcademicBackgroundController