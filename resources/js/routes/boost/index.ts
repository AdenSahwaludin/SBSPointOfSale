import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
export const browserLogs = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: browserLogs.url(options),
    method: 'post',
})

browserLogs.definition = {
    methods: ["post"],
    url: '/_boost/browser-logs',
} satisfies RouteDefinition<["post"]>

browserLogs.url = (options?: RouteQueryOptions) => {
    return browserLogs.definition.url + queryParams(options)
}

browserLogs.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: browserLogs.url(options),
    method: 'post',
})

        const browserLogsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: browserLogs.url(options),
        method: 'post',
    })

                    browserLogsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: browserLogs.url(options),
            method: 'post',
        })
    
    browserLogs.form = browserLogsForm
const boost = {
    browserLogs: Object.assign(browserLogs, browserLogs),
}

export default boost