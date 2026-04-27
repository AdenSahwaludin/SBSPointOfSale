import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
export const daily = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: daily.url(options),
    method: 'get',
})

daily.definition = {
    methods: ["get","head"],
    url: '/admin/reports/daily',
} satisfies RouteDefinition<["get","head"]>

daily.url = (options?: RouteQueryOptions) => {
    return daily.definition.url + queryParams(options)
}

daily.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: daily.url(options),
    method: 'get',
})
daily.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: daily.url(options),
    method: 'head',
})

        const dailyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: daily.url(options),
        method: 'get',
    })

                    dailyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: daily.url(options),
            method: 'get',
        })
                    dailyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: daily.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    daily.form = dailyForm
export const weekly = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weekly.url(options),
    method: 'get',
})

weekly.definition = {
    methods: ["get","head"],
    url: '/admin/reports/weekly',
} satisfies RouteDefinition<["get","head"]>

weekly.url = (options?: RouteQueryOptions) => {
    return weekly.definition.url + queryParams(options)
}

weekly.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weekly.url(options),
    method: 'get',
})
weekly.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: weekly.url(options),
    method: 'head',
})

        const weeklyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: weekly.url(options),
        method: 'get',
    })

                    weeklyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weekly.url(options),
            method: 'get',
        })
                    weeklyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weekly.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    weekly.form = weeklyForm
export const monthly = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthly.url(options),
    method: 'get',
})

monthly.definition = {
    methods: ["get","head"],
    url: '/admin/reports/monthly',
} satisfies RouteDefinition<["get","head"]>

monthly.url = (options?: RouteQueryOptions) => {
    return monthly.definition.url + queryParams(options)
}

monthly.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthly.url(options),
    method: 'get',
})
monthly.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: monthly.url(options),
    method: 'head',
})

        const monthlyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: monthly.url(options),
        method: 'get',
    })

                    monthlyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthly.url(options),
            method: 'get',
        })
                    monthlyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthly.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    monthly.form = monthlyForm
export const exportPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

exportPdf.definition = {
    methods: ["get","head"],
    url: '/admin/reports/export/pdf',
} satisfies RouteDefinition<["get","head"]>

exportPdf.url = (options?: RouteQueryOptions) => {
    return exportPdf.definition.url + queryParams(options)
}

exportPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})
exportPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportPdf.url(options),
    method: 'head',
})

        const exportPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportPdf.url(options),
        method: 'get',
    })

                    exportPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportPdf.url(options),
            method: 'get',
        })
                    exportPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportPdf.form = exportPdfForm
export const exportCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})

exportCsv.definition = {
    methods: ["get","head"],
    url: '/admin/reports/export/csv',
} satisfies RouteDefinition<["get","head"]>

exportCsv.url = (options?: RouteQueryOptions) => {
    return exportCsv.definition.url + queryParams(options)
}

exportCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})
exportCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportCsv.url(options),
    method: 'head',
})

        const exportCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportCsv.url(options),
        method: 'get',
    })

                    exportCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportCsv.url(options),
            method: 'get',
        })
                    exportCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportCsv.form = exportCsvForm
export const dailyExportPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyExportPdf.url(options),
    method: 'get',
})

dailyExportPdf.definition = {
    methods: ["get","head"],
    url: '/admin/reports/daily/export/pdf',
} satisfies RouteDefinition<["get","head"]>

dailyExportPdf.url = (options?: RouteQueryOptions) => {
    return dailyExportPdf.definition.url + queryParams(options)
}

dailyExportPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyExportPdf.url(options),
    method: 'get',
})
dailyExportPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dailyExportPdf.url(options),
    method: 'head',
})

        const dailyExportPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dailyExportPdf.url(options),
        method: 'get',
    })

                    dailyExportPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyExportPdf.url(options),
            method: 'get',
        })
                    dailyExportPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyExportPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dailyExportPdf.form = dailyExportPdfForm
export const dailyExportCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyExportCsv.url(options),
    method: 'get',
})

dailyExportCsv.definition = {
    methods: ["get","head"],
    url: '/admin/reports/daily/export/csv',
} satisfies RouteDefinition<["get","head"]>

dailyExportCsv.url = (options?: RouteQueryOptions) => {
    return dailyExportCsv.definition.url + queryParams(options)
}

dailyExportCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyExportCsv.url(options),
    method: 'get',
})
dailyExportCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dailyExportCsv.url(options),
    method: 'head',
})

        const dailyExportCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dailyExportCsv.url(options),
        method: 'get',
    })

                    dailyExportCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyExportCsv.url(options),
            method: 'get',
        })
                    dailyExportCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyExportCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dailyExportCsv.form = dailyExportCsvForm
export const weeklyExportPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weeklyExportPdf.url(options),
    method: 'get',
})

weeklyExportPdf.definition = {
    methods: ["get","head"],
    url: '/admin/reports/weekly/export/pdf',
} satisfies RouteDefinition<["get","head"]>

weeklyExportPdf.url = (options?: RouteQueryOptions) => {
    return weeklyExportPdf.definition.url + queryParams(options)
}

weeklyExportPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weeklyExportPdf.url(options),
    method: 'get',
})
weeklyExportPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: weeklyExportPdf.url(options),
    method: 'head',
})

        const weeklyExportPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: weeklyExportPdf.url(options),
        method: 'get',
    })

                    weeklyExportPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weeklyExportPdf.url(options),
            method: 'get',
        })
                    weeklyExportPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weeklyExportPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    weeklyExportPdf.form = weeklyExportPdfForm
export const weeklyExportCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weeklyExportCsv.url(options),
    method: 'get',
})

weeklyExportCsv.definition = {
    methods: ["get","head"],
    url: '/admin/reports/weekly/export/csv',
} satisfies RouteDefinition<["get","head"]>

weeklyExportCsv.url = (options?: RouteQueryOptions) => {
    return weeklyExportCsv.definition.url + queryParams(options)
}

weeklyExportCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weeklyExportCsv.url(options),
    method: 'get',
})
weeklyExportCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: weeklyExportCsv.url(options),
    method: 'head',
})

        const weeklyExportCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: weeklyExportCsv.url(options),
        method: 'get',
    })

                    weeklyExportCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weeklyExportCsv.url(options),
            method: 'get',
        })
                    weeklyExportCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weeklyExportCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    weeklyExportCsv.form = weeklyExportCsvForm
export const monthlyExportPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyExportPdf.url(options),
    method: 'get',
})

monthlyExportPdf.definition = {
    methods: ["get","head"],
    url: '/admin/reports/monthly/export/pdf',
} satisfies RouteDefinition<["get","head"]>

monthlyExportPdf.url = (options?: RouteQueryOptions) => {
    return monthlyExportPdf.definition.url + queryParams(options)
}

monthlyExportPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyExportPdf.url(options),
    method: 'get',
})
monthlyExportPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: monthlyExportPdf.url(options),
    method: 'head',
})

        const monthlyExportPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: monthlyExportPdf.url(options),
        method: 'get',
    })

                    monthlyExportPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyExportPdf.url(options),
            method: 'get',
        })
                    monthlyExportPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyExportPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    monthlyExportPdf.form = monthlyExportPdfForm
export const monthlyExportCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyExportCsv.url(options),
    method: 'get',
})

monthlyExportCsv.definition = {
    methods: ["get","head"],
    url: '/admin/reports/monthly/export/csv',
} satisfies RouteDefinition<["get","head"]>

monthlyExportCsv.url = (options?: RouteQueryOptions) => {
    return monthlyExportCsv.definition.url + queryParams(options)
}

monthlyExportCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyExportCsv.url(options),
    method: 'get',
})
monthlyExportCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: monthlyExportCsv.url(options),
    method: 'head',
})

        const monthlyExportCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: monthlyExportCsv.url(options),
        method: 'get',
    })

                    monthlyExportCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyExportCsv.url(options),
            method: 'get',
        })
                    monthlyExportCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyExportCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    monthlyExportCsv.form = monthlyExportCsvForm
const reports = {
    daily: Object.assign(daily, daily),
weekly: Object.assign(weekly, weekly),
monthly: Object.assign(monthly, monthly),
exportPdf: Object.assign(exportPdf, exportPdf),
exportCsv: Object.assign(exportCsv, exportCsv),
dailyExportPdf: Object.assign(dailyExportPdf, dailyExportPdf),
dailyExportCsv: Object.assign(dailyExportCsv, dailyExportCsv),
weeklyExportPdf: Object.assign(weeklyExportPdf, weeklyExportPdf),
weeklyExportCsv: Object.assign(weeklyExportCsv, weeklyExportCsv),
monthlyExportPdf: Object.assign(monthlyExportPdf, monthlyExportPdf),
monthlyExportCsv: Object.assign(monthlyExportCsv, monthlyExportCsv),
}

export default reports