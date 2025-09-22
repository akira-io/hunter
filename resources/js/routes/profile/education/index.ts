import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Profile\AcademicBackgroundController::deleteMethod
* @see app/Http/Controllers/Profile/AcademicBackgroundController.php:38
* @route '/profile/academic-background/{academicBackground}'
*/
export const deleteMethod = (args: { academicBackground: number | { id: number } } | [academicBackground: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/profile/academic-background/{academicBackground}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Profile\AcademicBackgroundController::deleteMethod
* @see app/Http/Controllers/Profile/AcademicBackgroundController.php:38
* @route '/profile/academic-background/{academicBackground}'
*/
deleteMethod.url = (args: { academicBackground: number | { id: number } } | [academicBackground: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return deleteMethod.definition.url
            .replace('{academicBackground}', parsedArgs.academicBackground.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Profile\AcademicBackgroundController::deleteMethod
* @see app/Http/Controllers/Profile/AcademicBackgroundController.php:38
* @route '/profile/academic-background/{academicBackground}'
*/
deleteMethod.delete = (args: { academicBackground: number | { id: number } } | [academicBackground: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

const education = {
    delete: Object.assign(deleteMethod, deleteMethod),
}

export default education