import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/login',
} satisfies RouteDefinition<["post"]>

store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

        const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

                    storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const destroyf732b903d9f8919b4c24bef1f8bb897a = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: destroyf732b903d9f8919b4c24bef1f8bb897a.url(options),
    method: 'post',
})

destroyf732b903d9f8919b4c24bef1f8bb897a.definition = {
    methods: ["post"],
    url: '/logout',
} satisfies RouteDefinition<["post"]>

destroyf732b903d9f8919b4c24bef1f8bb897a.url = (options?: RouteQueryOptions) => {
    return destroyf732b903d9f8919b4c24bef1f8bb897a.definition.url + queryParams(options)
}

destroyf732b903d9f8919b4c24bef1f8bb897a.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: destroyf732b903d9f8919b4c24bef1f8bb897a.url(options),
    method: 'post',
})

        const destroyf732b903d9f8919b4c24bef1f8bb897aForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyf732b903d9f8919b4c24bef1f8bb897a.url(options),
        method: 'post',
    })

                    destroyf732b903d9f8919b4c24bef1f8bb897aForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyf732b903d9f8919b4c24bef1f8bb897a.url(options),
            method: 'post',
        })
    
    destroyf732b903d9f8919b4c24bef1f8bb897a.form = destroyf732b903d9f8919b4c24bef1f8bb897aForm
    const destroyf732b903d9f8919b4c24bef1f8bb897a = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyf732b903d9f8919b4c24bef1f8bb897a.url(options),
    method: 'delete',
})

destroyf732b903d9f8919b4c24bef1f8bb897a.definition = {
    methods: ["delete"],
    url: '/logout',
} satisfies RouteDefinition<["delete"]>

destroyf732b903d9f8919b4c24bef1f8bb897a.url = (options?: RouteQueryOptions) => {
    return destroyf732b903d9f8919b4c24bef1f8bb897a.definition.url + queryParams(options)
}

destroyf732b903d9f8919b4c24bef1f8bb897a.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyf732b903d9f8919b4c24bef1f8bb897a.url(options),
    method: 'delete',
})

        const destroyf732b903d9f8919b4c24bef1f8bb897aForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyf732b903d9f8919b4c24bef1f8bb897a.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    destroyf732b903d9f8919b4c24bef1f8bb897aForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyf732b903d9f8919b4c24bef1f8bb897a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyf732b903d9f8919b4c24bef1f8bb897a.form = destroyf732b903d9f8919b4c24bef1f8bb897aForm

export const destroy = {
    '/logout': destroyf732b903d9f8919b4c24bef1f8bb897a,
    '/logout': destroyf732b903d9f8919b4c24bef1f8bb897a,
}

const LoginController = { store, destroy }

export default LoginController