import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/transactions',
} satisfies RouteDefinition<["get","head"]>

index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

        const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

                    indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
                    indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
export const credit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: credit.url(options),
    method: 'get',
})

credit.definition = {
    methods: ["get","head"],
    url: '/admin/transactions/kredit',
} satisfies RouteDefinition<["get","head"]>

credit.url = (options?: RouteQueryOptions) => {
    return credit.definition.url + queryParams(options)
}

credit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: credit.url(options),
    method: 'get',
})
credit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: credit.url(options),
    method: 'head',
})

        const creditForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: credit.url(options),
        method: 'get',
    })

                    creditForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: credit.url(options),
            method: 'get',
        })
                    creditForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: credit.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    credit.form = creditForm
export const weeklyReport = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weeklyReport.url(options),
    method: 'get',
})

weeklyReport.definition = {
    methods: ["get","head"],
    url: '/admin/transactions/laporan-mingguan',
} satisfies RouteDefinition<["get","head"]>

weeklyReport.url = (options?: RouteQueryOptions) => {
    return weeklyReport.definition.url + queryParams(options)
}

weeklyReport.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weeklyReport.url(options),
    method: 'get',
})
weeklyReport.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: weeklyReport.url(options),
    method: 'head',
})

        const weeklyReportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: weeklyReport.url(options),
        method: 'get',
    })

                    weeklyReportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weeklyReport.url(options),
            method: 'get',
        })
                    weeklyReportForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weeklyReport.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    weeklyReport.form = weeklyReportForm
export const dailyReport = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyReport.url(options),
    method: 'get',
})

dailyReport.definition = {
    methods: ["get","head"],
    url: '/admin/transactions/laporan-harian',
} satisfies RouteDefinition<["get","head"]>

dailyReport.url = (options?: RouteQueryOptions) => {
    return dailyReport.definition.url + queryParams(options)
}

dailyReport.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyReport.url(options),
    method: 'get',
})
dailyReport.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dailyReport.url(options),
    method: 'head',
})

        const dailyReportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dailyReport.url(options),
        method: 'get',
    })

                    dailyReportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyReport.url(options),
            method: 'get',
        })
                    dailyReportForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyReport.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dailyReport.form = dailyReportForm
export const monthlyReport = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyReport.url(options),
    method: 'get',
})

monthlyReport.definition = {
    methods: ["get","head"],
    url: '/admin/transactions/laporan-bulanan',
} satisfies RouteDefinition<["get","head"]>

monthlyReport.url = (options?: RouteQueryOptions) => {
    return monthlyReport.definition.url + queryParams(options)
}

monthlyReport.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyReport.url(options),
    method: 'get',
})
monthlyReport.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: monthlyReport.url(options),
    method: 'head',
})

        const monthlyReportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: monthlyReport.url(options),
        method: 'get',
    })

                    monthlyReportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyReport.url(options),
            method: 'get',
        })
                    monthlyReportForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyReport.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    monthlyReport.form = monthlyReportForm
export const show = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/transactions/{nomorTransaksi}',
} satisfies RouteDefinition<["get","head"]>

show.url = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { nomorTransaksi: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    nomorTransaksi: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        nomorTransaksi: args.nomorTransaksi,
                }

    return show.definition.url
            .replace('{nomorTransaksi}', parsedArgs.nomorTransaksi.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

show.get = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
show.head = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

        const showForm = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

                    showForm.get = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
                    showForm.head = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const transactions = {
    index: Object.assign(index, index),
credit: Object.assign(credit, credit),
weeklyReport: Object.assign(weeklyReport, weeklyReport),
dailyReport: Object.assign(dailyReport, dailyReport),
monthlyReport: Object.assign(monthlyReport, monthlyReport),
show: Object.assign(show, show),
}

export default transactions