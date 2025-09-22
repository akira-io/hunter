import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../wayfinder';
import education001c63 from './education';
/**
 * @see \App\Http\Controllers\Profile\AboutController::__invoke
 * @see app/Http/Controllers/Profile/AboutController.php:20
 * @route '/profile/about'
 */
export const about = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: about.url(options),
    method: 'patch',
});

about.definition = {
    methods: ['patch'],
    url: '/profile/about',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Profile\AboutController::__invoke
 * @see app/Http/Controllers/Profile/AboutController.php:20
 * @route '/profile/about'
 */
about.url = (options?: RouteQueryOptions) => {
    return about.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Profile\AboutController::__invoke
 * @see app/Http/Controllers/Profile/AboutController.php:20
 * @route '/profile/about'
 */
about.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: about.url(options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Profile\AcademicBackgroundController::education
 * @see app/Http/Controllers/Profile/AcademicBackgroundController.php:24
 * @route '/profile/academic-background'
 */
export const education = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: education.url(options),
    method: 'post',
});

education.definition = {
    methods: ['post'],
    url: '/profile/academic-background',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Profile\AcademicBackgroundController::education
 * @see app/Http/Controllers/Profile/AcademicBackgroundController.php:24
 * @route '/profile/academic-background'
 */
education.url = (options?: RouteQueryOptions) => {
    return education.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Profile\AcademicBackgroundController::education
 * @see app/Http/Controllers/Profile/AcademicBackgroundController.php:24
 * @route '/profile/academic-background'
 */
education.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: education.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Profile\HighlightSkillController::__invoke
 * @see app/Http/Controllers/Profile/HighlightSkillController.php:20
 * @route '/profile/highlight-skills'
 */
export const highlightSkills = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: highlightSkills.url(options),
    method: 'post',
});

highlightSkills.definition = {
    methods: ['post'],
    url: '/profile/highlight-skills',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Profile\HighlightSkillController::__invoke
 * @see app/Http/Controllers/Profile/HighlightSkillController.php:20
 * @route '/profile/highlight-skills'
 */
highlightSkills.url = (options?: RouteQueryOptions) => {
    return highlightSkills.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Profile\HighlightSkillController::__invoke
 * @see app/Http/Controllers/Profile/HighlightSkillController.php:20
 * @route '/profile/highlight-skills'
 */
highlightSkills.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: highlightSkills.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Profile\LinksController::__invoke
 * @see app/Http/Controllers/Profile/LinksController.php:20
 * @route '/profile/links'
 */
export const links = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: links.url(options),
    method: 'patch',
});

links.definition = {
    methods: ['patch'],
    url: '/profile/links',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Profile\LinksController::__invoke
 * @see app/Http/Controllers/Profile/LinksController.php:20
 * @route '/profile/links'
 */
links.url = (options?: RouteQueryOptions) => {
    return links.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Profile\LinksController::__invoke
 * @see app/Http/Controllers/Profile/LinksController.php:20
 * @route '/profile/links'
 */
links.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: links.url(options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Settings\ProfileController::edit
 * @see app/Http/Controllers/Settings/ProfileController.php:26
 * @route '/settings/profile'
 */
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
});

edit.definition = {
    methods: ['get', 'head'],
    url: '/settings/profile',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Settings\ProfileController::edit
 * @see app/Http/Controllers/Settings/ProfileController.php:26
 * @route '/settings/profile'
 */
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Settings\ProfileController::edit
 * @see app/Http/Controllers/Settings/ProfileController.php:26
 * @route '/settings/profile'
 */
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Settings\ProfileController::edit
 * @see app/Http/Controllers/Settings/ProfileController.php:26
 * @route '/settings/profile'
 */
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Settings\ProfileController::update
 * @see app/Http/Controllers/Settings/ProfileController.php:47
 * @route '/settings/profile'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
});

update.definition = {
    methods: ['post'],
    url: '/settings/profile',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Settings\ProfileController::update
 * @see app/Http/Controllers/Settings/ProfileController.php:47
 * @route '/settings/profile'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Settings\ProfileController::update
 * @see app/Http/Controllers/Settings/ProfileController.php:47
 * @route '/settings/profile'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Settings\ProfileController::destroy
 * @see app/Http/Controllers/Settings/ProfileController.php:62
 * @route '/settings/profile'
 */
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
});

destroy.definition = {
    methods: ['delete'],
    url: '/settings/profile',
} satisfies RouteDefinition<['delete']>;

/**
 * @see \App\Http\Controllers\Settings\ProfileController::destroy
 * @see app/Http/Controllers/Settings/ProfileController.php:62
 * @route '/settings/profile'
 */
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Settings\ProfileController::destroy
 * @see app/Http/Controllers/Settings/ProfileController.php:62
 * @route '/settings/profile'
 */
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
});

const profile = {
    about: Object.assign(about, about),
    education: Object.assign(education, education001c63),
    highlightSkills: Object.assign(highlightSkills, highlightSkills),
    links: Object.assign(links, links),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
};

export default profile;
