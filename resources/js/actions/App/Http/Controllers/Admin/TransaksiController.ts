import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
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
export const creditTransactions = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: creditTransactions.url(options),
    method: 'get',
})

creditTransactions.definition = {
    methods: ["get","head"],
    url: '/admin/transactions/kredit',
} satisfies RouteDefinition<["get","head"]>

creditTransactions.url = (options?: RouteQueryOptions) => {
    return creditTransactions.definition.url + queryParams(options)
}

creditTransactions.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: creditTransactions.url(options),
    method: 'get',
})
creditTransactions.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: creditTransactions.url(options),
    method: 'head',
})

        const creditTransactionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: creditTransactions.url(options),
        method: 'get',
    })

                    creditTransactionsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: creditTransactions.url(options),
            method: 'get',
        })
                    creditTransactionsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: creditTransactions.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    creditTransactions.form = creditTransactionsForm
const weeklyReportaddd3925865b4f8521553db30bf213ff = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weeklyReportaddd3925865b4f8521553db30bf213ff.url(options),
    method: 'get',
})

weeklyReportaddd3925865b4f8521553db30bf213ff.definition = {
    methods: ["get","head"],
    url: '/admin/transactions/laporan-mingguan',
} satisfies RouteDefinition<["get","head"]>

weeklyReportaddd3925865b4f8521553db30bf213ff.url = (options?: RouteQueryOptions) => {
    return weeklyReportaddd3925865b4f8521553db30bf213ff.definition.url + queryParams(options)
}

weeklyReportaddd3925865b4f8521553db30bf213ff.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weeklyReportaddd3925865b4f8521553db30bf213ff.url(options),
    method: 'get',
})
weeklyReportaddd3925865b4f8521553db30bf213ff.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: weeklyReportaddd3925865b4f8521553db30bf213ff.url(options),
    method: 'head',
})

        const weeklyReportaddd3925865b4f8521553db30bf213ffForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: weeklyReportaddd3925865b4f8521553db30bf213ff.url(options),
        method: 'get',
    })

                    weeklyReportaddd3925865b4f8521553db30bf213ffForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weeklyReportaddd3925865b4f8521553db30bf213ff.url(options),
            method: 'get',
        })
                    weeklyReportaddd3925865b4f8521553db30bf213ffForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weeklyReportaddd3925865b4f8521553db30bf213ff.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    weeklyReportaddd3925865b4f8521553db30bf213ff.form = weeklyReportaddd3925865b4f8521553db30bf213ffForm
    const weeklyReport9489a022079c05cec78406fcea9f630d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weeklyReport9489a022079c05cec78406fcea9f630d.url(options),
    method: 'get',
})

weeklyReport9489a022079c05cec78406fcea9f630d.definition = {
    methods: ["get","head"],
    url: '/admin/reports/weekly',
} satisfies RouteDefinition<["get","head"]>

weeklyReport9489a022079c05cec78406fcea9f630d.url = (options?: RouteQueryOptions) => {
    return weeklyReport9489a022079c05cec78406fcea9f630d.definition.url + queryParams(options)
}

weeklyReport9489a022079c05cec78406fcea9f630d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: weeklyReport9489a022079c05cec78406fcea9f630d.url(options),
    method: 'get',
})
weeklyReport9489a022079c05cec78406fcea9f630d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: weeklyReport9489a022079c05cec78406fcea9f630d.url(options),
    method: 'head',
})

        const weeklyReport9489a022079c05cec78406fcea9f630dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: weeklyReport9489a022079c05cec78406fcea9f630d.url(options),
        method: 'get',
    })

                    weeklyReport9489a022079c05cec78406fcea9f630dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weeklyReport9489a022079c05cec78406fcea9f630d.url(options),
            method: 'get',
        })
                    weeklyReport9489a022079c05cec78406fcea9f630dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: weeklyReport9489a022079c05cec78406fcea9f630d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    weeklyReport9489a022079c05cec78406fcea9f630d.form = weeklyReport9489a022079c05cec78406fcea9f630dForm

export const weeklyReport = {
    '/admin/transactions/laporan-mingguan': weeklyReportaddd3925865b4f8521553db30bf213ff,
    '/admin/reports/weekly': weeklyReport9489a022079c05cec78406fcea9f630d,
}

const dailyReporte0ff6639af9292e10f7c78264c9ac53f = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyReporte0ff6639af9292e10f7c78264c9ac53f.url(options),
    method: 'get',
})

dailyReporte0ff6639af9292e10f7c78264c9ac53f.definition = {
    methods: ["get","head"],
    url: '/admin/transactions/laporan-harian',
} satisfies RouteDefinition<["get","head"]>

dailyReporte0ff6639af9292e10f7c78264c9ac53f.url = (options?: RouteQueryOptions) => {
    return dailyReporte0ff6639af9292e10f7c78264c9ac53f.definition.url + queryParams(options)
}

dailyReporte0ff6639af9292e10f7c78264c9ac53f.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyReporte0ff6639af9292e10f7c78264c9ac53f.url(options),
    method: 'get',
})
dailyReporte0ff6639af9292e10f7c78264c9ac53f.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dailyReporte0ff6639af9292e10f7c78264c9ac53f.url(options),
    method: 'head',
})

        const dailyReporte0ff6639af9292e10f7c78264c9ac53fForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dailyReporte0ff6639af9292e10f7c78264c9ac53f.url(options),
        method: 'get',
    })

                    dailyReporte0ff6639af9292e10f7c78264c9ac53fForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyReporte0ff6639af9292e10f7c78264c9ac53f.url(options),
            method: 'get',
        })
                    dailyReporte0ff6639af9292e10f7c78264c9ac53fForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyReporte0ff6639af9292e10f7c78264c9ac53f.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dailyReporte0ff6639af9292e10f7c78264c9ac53f.form = dailyReporte0ff6639af9292e10f7c78264c9ac53fForm
    const dailyReport836dacb93f4ec6a2a6306ee9ba77881f = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyReport836dacb93f4ec6a2a6306ee9ba77881f.url(options),
    method: 'get',
})

dailyReport836dacb93f4ec6a2a6306ee9ba77881f.definition = {
    methods: ["get","head"],
    url: '/admin/reports/daily',
} satisfies RouteDefinition<["get","head"]>

dailyReport836dacb93f4ec6a2a6306ee9ba77881f.url = (options?: RouteQueryOptions) => {
    return dailyReport836dacb93f4ec6a2a6306ee9ba77881f.definition.url + queryParams(options)
}

dailyReport836dacb93f4ec6a2a6306ee9ba77881f.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyReport836dacb93f4ec6a2a6306ee9ba77881f.url(options),
    method: 'get',
})
dailyReport836dacb93f4ec6a2a6306ee9ba77881f.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dailyReport836dacb93f4ec6a2a6306ee9ba77881f.url(options),
    method: 'head',
})

        const dailyReport836dacb93f4ec6a2a6306ee9ba77881fForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dailyReport836dacb93f4ec6a2a6306ee9ba77881f.url(options),
        method: 'get',
    })

                    dailyReport836dacb93f4ec6a2a6306ee9ba77881fForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyReport836dacb93f4ec6a2a6306ee9ba77881f.url(options),
            method: 'get',
        })
                    dailyReport836dacb93f4ec6a2a6306ee9ba77881fForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyReport836dacb93f4ec6a2a6306ee9ba77881f.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dailyReport836dacb93f4ec6a2a6306ee9ba77881f.form = dailyReport836dacb93f4ec6a2a6306ee9ba77881fForm

export const dailyReport = {
    '/admin/transactions/laporan-harian': dailyReporte0ff6639af9292e10f7c78264c9ac53f,
    '/admin/reports/daily': dailyReport836dacb93f4ec6a2a6306ee9ba77881f,
}

const monthlyReport2f9449837709975886bf2c454253c5d8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyReport2f9449837709975886bf2c454253c5d8.url(options),
    method: 'get',
})

monthlyReport2f9449837709975886bf2c454253c5d8.definition = {
    methods: ["get","head"],
    url: '/admin/transactions/laporan-bulanan',
} satisfies RouteDefinition<["get","head"]>

monthlyReport2f9449837709975886bf2c454253c5d8.url = (options?: RouteQueryOptions) => {
    return monthlyReport2f9449837709975886bf2c454253c5d8.definition.url + queryParams(options)
}

monthlyReport2f9449837709975886bf2c454253c5d8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyReport2f9449837709975886bf2c454253c5d8.url(options),
    method: 'get',
})
monthlyReport2f9449837709975886bf2c454253c5d8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: monthlyReport2f9449837709975886bf2c454253c5d8.url(options),
    method: 'head',
})

        const monthlyReport2f9449837709975886bf2c454253c5d8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: monthlyReport2f9449837709975886bf2c454253c5d8.url(options),
        method: 'get',
    })

                    monthlyReport2f9449837709975886bf2c454253c5d8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyReport2f9449837709975886bf2c454253c5d8.url(options),
            method: 'get',
        })
                    monthlyReport2f9449837709975886bf2c454253c5d8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyReport2f9449837709975886bf2c454253c5d8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    monthlyReport2f9449837709975886bf2c454253c5d8.form = monthlyReport2f9449837709975886bf2c454253c5d8Form
    const monthlyReporta99edd7237633fb80f67d7f53f4520ac = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyReporta99edd7237633fb80f67d7f53f4520ac.url(options),
    method: 'get',
})

monthlyReporta99edd7237633fb80f67d7f53f4520ac.definition = {
    methods: ["get","head"],
    url: '/admin/reports/monthly',
} satisfies RouteDefinition<["get","head"]>

monthlyReporta99edd7237633fb80f67d7f53f4520ac.url = (options?: RouteQueryOptions) => {
    return monthlyReporta99edd7237633fb80f67d7f53f4520ac.definition.url + queryParams(options)
}

monthlyReporta99edd7237633fb80f67d7f53f4520ac.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyReporta99edd7237633fb80f67d7f53f4520ac.url(options),
    method: 'get',
})
monthlyReporta99edd7237633fb80f67d7f53f4520ac.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: monthlyReporta99edd7237633fb80f67d7f53f4520ac.url(options),
    method: 'head',
})

        const monthlyReporta99edd7237633fb80f67d7f53f4520acForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: monthlyReporta99edd7237633fb80f67d7f53f4520ac.url(options),
        method: 'get',
    })

                    monthlyReporta99edd7237633fb80f67d7f53f4520acForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyReporta99edd7237633fb80f67d7f53f4520ac.url(options),
            method: 'get',
        })
                    monthlyReporta99edd7237633fb80f67d7f53f4520acForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyReporta99edd7237633fb80f67d7f53f4520ac.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    monthlyReporta99edd7237633fb80f67d7f53f4520ac.form = monthlyReporta99edd7237633fb80f67d7f53f4520acForm

export const monthlyReport = {
    '/admin/transactions/laporan-bulanan': monthlyReport2f9449837709975886bf2c454253c5d8,
    '/admin/reports/monthly': monthlyReporta99edd7237633fb80f67d7f53f4520ac,
}

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
export const exportDailyPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportDailyPdf.url(options),
    method: 'get',
})

exportDailyPdf.definition = {
    methods: ["get","head"],
    url: '/admin/reports/daily/export/pdf',
} satisfies RouteDefinition<["get","head"]>

exportDailyPdf.url = (options?: RouteQueryOptions) => {
    return exportDailyPdf.definition.url + queryParams(options)
}

exportDailyPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportDailyPdf.url(options),
    method: 'get',
})
exportDailyPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportDailyPdf.url(options),
    method: 'head',
})

        const exportDailyPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportDailyPdf.url(options),
        method: 'get',
    })

                    exportDailyPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportDailyPdf.url(options),
            method: 'get',
        })
                    exportDailyPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportDailyPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportDailyPdf.form = exportDailyPdfForm
export const exportDailyCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportDailyCsv.url(options),
    method: 'get',
})

exportDailyCsv.definition = {
    methods: ["get","head"],
    url: '/admin/reports/daily/export/csv',
} satisfies RouteDefinition<["get","head"]>

exportDailyCsv.url = (options?: RouteQueryOptions) => {
    return exportDailyCsv.definition.url + queryParams(options)
}

exportDailyCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportDailyCsv.url(options),
    method: 'get',
})
exportDailyCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportDailyCsv.url(options),
    method: 'head',
})

        const exportDailyCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportDailyCsv.url(options),
        method: 'get',
    })

                    exportDailyCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportDailyCsv.url(options),
            method: 'get',
        })
                    exportDailyCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportDailyCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportDailyCsv.form = exportDailyCsvForm
export const exportWeeklyPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportWeeklyPdf.url(options),
    method: 'get',
})

exportWeeklyPdf.definition = {
    methods: ["get","head"],
    url: '/admin/reports/weekly/export/pdf',
} satisfies RouteDefinition<["get","head"]>

exportWeeklyPdf.url = (options?: RouteQueryOptions) => {
    return exportWeeklyPdf.definition.url + queryParams(options)
}

exportWeeklyPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportWeeklyPdf.url(options),
    method: 'get',
})
exportWeeklyPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportWeeklyPdf.url(options),
    method: 'head',
})

        const exportWeeklyPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportWeeklyPdf.url(options),
        method: 'get',
    })

                    exportWeeklyPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportWeeklyPdf.url(options),
            method: 'get',
        })
                    exportWeeklyPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportWeeklyPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportWeeklyPdf.form = exportWeeklyPdfForm
export const exportWeeklyCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportWeeklyCsv.url(options),
    method: 'get',
})

exportWeeklyCsv.definition = {
    methods: ["get","head"],
    url: '/admin/reports/weekly/export/csv',
} satisfies RouteDefinition<["get","head"]>

exportWeeklyCsv.url = (options?: RouteQueryOptions) => {
    return exportWeeklyCsv.definition.url + queryParams(options)
}

exportWeeklyCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportWeeklyCsv.url(options),
    method: 'get',
})
exportWeeklyCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportWeeklyCsv.url(options),
    method: 'head',
})

        const exportWeeklyCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportWeeklyCsv.url(options),
        method: 'get',
    })

                    exportWeeklyCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportWeeklyCsv.url(options),
            method: 'get',
        })
                    exportWeeklyCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportWeeklyCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportWeeklyCsv.form = exportWeeklyCsvForm
export const exportMonthlyPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMonthlyPdf.url(options),
    method: 'get',
})

exportMonthlyPdf.definition = {
    methods: ["get","head"],
    url: '/admin/reports/monthly/export/pdf',
} satisfies RouteDefinition<["get","head"]>

exportMonthlyPdf.url = (options?: RouteQueryOptions) => {
    return exportMonthlyPdf.definition.url + queryParams(options)
}

exportMonthlyPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMonthlyPdf.url(options),
    method: 'get',
})
exportMonthlyPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMonthlyPdf.url(options),
    method: 'head',
})

        const exportMonthlyPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMonthlyPdf.url(options),
        method: 'get',
    })

                    exportMonthlyPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMonthlyPdf.url(options),
            method: 'get',
        })
                    exportMonthlyPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMonthlyPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMonthlyPdf.form = exportMonthlyPdfForm
export const exportMonthlyCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMonthlyCsv.url(options),
    method: 'get',
})

exportMonthlyCsv.definition = {
    methods: ["get","head"],
    url: '/admin/reports/monthly/export/csv',
} satisfies RouteDefinition<["get","head"]>

exportMonthlyCsv.url = (options?: RouteQueryOptions) => {
    return exportMonthlyCsv.definition.url + queryParams(options)
}

exportMonthlyCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMonthlyCsv.url(options),
    method: 'get',
})
exportMonthlyCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMonthlyCsv.url(options),
    method: 'head',
})

        const exportMonthlyCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMonthlyCsv.url(options),
        method: 'get',
    })

                    exportMonthlyCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMonthlyCsv.url(options),
            method: 'get',
        })
                    exportMonthlyCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMonthlyCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMonthlyCsv.form = exportMonthlyCsvForm
const TransaksiController = { index, creditTransactions, weeklyReport, dailyReport, monthlyReport, show, exportDailyPdf, exportDailyCsv, exportWeeklyPdf, exportWeeklyCsv, exportMonthlyPdf, exportMonthlyCsv }

export default TransaksiController