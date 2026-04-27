import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
const Controllerb6041c76e8e1cd791f8f89d035d48611 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerb6041c76e8e1cd791f8f89d035d48611.url(options),
    method: 'get',
})

Controllerb6041c76e8e1cd791f8f89d035d48611.definition = {
    methods: ["get","head"],
    url: '/login',
} satisfies RouteDefinition<["get","head"]>

Controllerb6041c76e8e1cd791f8f89d035d48611.url = (options?: RouteQueryOptions) => {
    return Controllerb6041c76e8e1cd791f8f89d035d48611.definition.url + queryParams(options)
}

Controllerb6041c76e8e1cd791f8f89d035d48611.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerb6041c76e8e1cd791f8f89d035d48611.url(options),
    method: 'get',
})
Controllerb6041c76e8e1cd791f8f89d035d48611.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerb6041c76e8e1cd791f8f89d035d48611.url(options),
    method: 'head',
})

        const Controllerb6041c76e8e1cd791f8f89d035d48611Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerb6041c76e8e1cd791f8f89d035d48611.url(options),
        method: 'get',
    })

                    Controllerb6041c76e8e1cd791f8f89d035d48611Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerb6041c76e8e1cd791f8f89d035d48611.url(options),
            method: 'get',
        })
                    Controllerb6041c76e8e1cd791f8f89d035d48611Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerb6041c76e8e1cd791f8f89d035d48611.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerb6041c76e8e1cd791f8f89d035d48611.form = Controllerb6041c76e8e1cd791f8f89d035d48611Form
    const Controllerde7b92f5d57ab3be25571f27f05793f8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'get',
})

Controllerde7b92f5d57ab3be25571f27f05793f8.definition = {
    methods: ["get","head"],
    url: '/admin/users',
} satisfies RouteDefinition<["get","head"]>

Controllerde7b92f5d57ab3be25571f27f05793f8.url = (options?: RouteQueryOptions) => {
    return Controllerde7b92f5d57ab3be25571f27f05793f8.definition.url + queryParams(options)
}

Controllerde7b92f5d57ab3be25571f27f05793f8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'get',
})
Controllerde7b92f5d57ab3be25571f27f05793f8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerde7b92f5d57ab3be25571f27f05793f8.url(options),
    method: 'head',
})

        const Controllerde7b92f5d57ab3be25571f27f05793f8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerde7b92f5d57ab3be25571f27f05793f8.url(options),
        method: 'get',
    })

                    Controllerde7b92f5d57ab3be25571f27f05793f8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerde7b92f5d57ab3be25571f27f05793f8.url(options),
            method: 'get',
        })
                    Controllerde7b92f5d57ab3be25571f27f05793f8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerde7b92f5d57ab3be25571f27f05793f8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerde7b92f5d57ab3be25571f27f05793f8.form = Controllerde7b92f5d57ab3be25571f27f05793f8Form
    const Controller2b603298152ec5dd9b14768a8a90e70d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller2b603298152ec5dd9b14768a8a90e70d.url(options),
    method: 'get',
})

Controller2b603298152ec5dd9b14768a8a90e70d.definition = {
    methods: ["get","head"],
    url: '/admin/profile',
} satisfies RouteDefinition<["get","head"]>

Controller2b603298152ec5dd9b14768a8a90e70d.url = (options?: RouteQueryOptions) => {
    return Controller2b603298152ec5dd9b14768a8a90e70d.definition.url + queryParams(options)
}

Controller2b603298152ec5dd9b14768a8a90e70d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller2b603298152ec5dd9b14768a8a90e70d.url(options),
    method: 'get',
})
Controller2b603298152ec5dd9b14768a8a90e70d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller2b603298152ec5dd9b14768a8a90e70d.url(options),
    method: 'head',
})

        const Controller2b603298152ec5dd9b14768a8a90e70dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller2b603298152ec5dd9b14768a8a90e70d.url(options),
        method: 'get',
    })

                    Controller2b603298152ec5dd9b14768a8a90e70dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller2b603298152ec5dd9b14768a8a90e70d.url(options),
            method: 'get',
        })
                    Controller2b603298152ec5dd9b14768a8a90e70dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller2b603298152ec5dd9b14768a8a90e70d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller2b603298152ec5dd9b14768a8a90e70d.form = Controller2b603298152ec5dd9b14768a8a90e70dForm

const Controller = {
    '/login': Controllerb6041c76e8e1cd791f8f89d035d48611,
    '/admin/users': Controllerde7b92f5d57ab3be25571f27f05793f8,
    '/admin/profile': Controller2b603298152ec5dd9b14768a8a90e70d,
}

export default Controller