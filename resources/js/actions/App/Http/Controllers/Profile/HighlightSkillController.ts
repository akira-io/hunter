import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Profile\HighlightSkillController::__invoke
* @see app/Http/Controllers/Profile/HighlightSkillController.php:20
* @route '/profile/highlight-skills'
*/
const HighlightSkillController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: HighlightSkillController.url(options),
    method: 'post',
})

HighlightSkillController.definition = {
    methods: ["post"],
    url: '/profile/highlight-skills',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Profile\HighlightSkillController::__invoke
* @see app/Http/Controllers/Profile/HighlightSkillController.php:20
* @route '/profile/highlight-skills'
*/
HighlightSkillController.url = (options?: RouteQueryOptions) => {
    return HighlightSkillController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Profile\HighlightSkillController::__invoke
* @see app/Http/Controllers/Profile/HighlightSkillController.php:20
* @route '/profile/highlight-skills'
*/
HighlightSkillController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: HighlightSkillController.url(options),
    method: 'post',
})

export default HighlightSkillController