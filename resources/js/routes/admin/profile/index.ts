import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/admin/profile',
} satisfies RouteDefinition<["patch"]>

update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

        const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    updateForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
export const password = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: password.url(options),
    method: 'patch',
})

password.definition = {
    methods: ["patch"],
    url: '/admin/profile/password',
} satisfies RouteDefinition<["patch"]>

password.url = (options?: RouteQueryOptions) => {
    return password.definition.url + queryParams(options)
}

password.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: password.url(options),
    method: 'patch',
})

        const passwordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: password.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    passwordForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: password.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    password.form = passwordForm
const profile = {
    update: Object.assign(update, update),
password: Object.assign(password, password),
}

export default profile