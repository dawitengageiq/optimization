<?php

return [
    'legends' => [
        'all_clicks' => [
            'alias' => 'All Clicks',
            'desc' => 'All clicks from cake. Is different than clicks. Where all clicks is every click and clicks is what cake bills.',
            'percentage' => false,
            // 'color' => '#c09000'
            'color' => '#ffc000',
        ],
        'survey_takers' => [
            'alias' => 'Survey Takers',
            'desc' => 'Total number of Survey Takers.',
            'percentage' => false,
            // 'color' => '#315493'
            'color' => '#ffc000',
        ],

        'source_revenue' => [
            'alias' => 'Total Revenue',
            'desc' => 'Revenue for the day of this traffic source.',
            'percentage' => false,
            // 'color' => '#b35d22'
            'color' => '#ffff00',
        ],
        'margin' => [
            'alias' => 'Margin',
            'desc' => 'Margin: publisher margin per sub id.',
            'percentage' => true,
            // 'color' => '#538233'
            'color' => '#ffff00',
        ],
        'source_revenue_per_all_clicks' => [
            'alias' => 'Revenue / All Clicks',
            'desc' => 'Revenenue per all clicks. Revenue divided by all clicks.',
            'percentage' => true,
            // 'color' => '#4374a0'
            'color' => '#ccf2ec',
        ],
        'source_revenue_per_survey_takers' => [
            'alias' => 'Revenue / Survey Takers',
            'desc' => 'Revenenue per survey taker. Done by revenue divided by survey takers.',
            'percentage' => true,
            // 'color' => '#7b7b7b'
            'color' => '#ccf2ec',
        ],
        'pd_revenue' => [
            'alias' => 'Permission Data Revenue',
            'desc' => 'Permission Data Revenue.',
            'percentage' => false,
            // 'color' => '#62993e'
            'color' => '#8c9ab3',
        ],
        'pd_revenue_vs_views' => [
            'alias' => 'Permission Data Revenue / Views',
            'desc' => 'Permsion data revenue vs data pass view.',
            'percentage' => true,
            // 'color' => '#4472c4'
            'color' => '#8c9ab3',
        ],
        'tb_revenue' => [
            'alias' => 'Tiburon Revenue',
            'desc' => 'Tiburon revenue',
            'percentage' => false,
            // 'color' => '#ed7d31'
            'color' => '#8c9ab3',
        ],
        'tb_revenue_vs_views' => [
            'alias' => 'Tiburon Revenue / Views',
            'desc' => 'Tiburon revenue vs data pass views.',
            'percentage' => true,
            // 'color' => '#a5a5a5'
            'color' => '#8c9ab3',
        ],
        'iff_revenue' => [
            'alias' => 'Ifficent Revenue',
            'desc' => 'Ifficient Revenue',
            'percentage' => false,
            // 'color' => '#ffc000'
            'color' => '#8c9ab3',
        ],
        'iff_revenue_vs_views' => [
            'alias' => 'Ifficent Revenue / Views',
            'desc' => 'Ifficent revenue vs data pass views.',
            'percentage' => true,
            // 'color' => '#5b9bd5'
            'color' => '#8c9ab3',
        ],
        'rexadz_revenue' => [
            'alias' => 'Rexadz Revenue',
            'desc' => 'Rexads revenue',
            'percentage' => false,
            // 'color' => '#70ad47'
            'color' => '#8c9ab3',
        ],
        'rexadz_revenue_vs_views' => [
            'alias' => 'Rexadz Revenue / Views',
            'desc' => 'Rexadz revenue vs data pass views.',
            'percentage' => true,
            // 'color' => '#8fa2d4'
            'color' => '#8c9ab3',
        ],
        // 'adsmith_revenue' => [
        //     'alias' =>  'Adsmith Revenue',
        //     'desc' => 'Adsmith revenue',
        //     'percentage' => false,
        //     // 'color' => '#70ad47'
        //     'color' => '#8c9ab3'
        // ],
        // 'adsmith_revenue_vs_views' => [
        //     'alias' =>  'Adsmith Revenue / Views',
        //     'desc' => 'Adsmith revenue vs data pass views.',
        //     'percentage' => true,
        //     // 'color' => '#8fa2d4'
        //     'color' => '#8c9ab3'
        // ],
        'all_inbox_revenue' => [
            'alias' => 'All Inbox',
            'desc' => 'All inbox: this we get manually.',
            'percentage' => false,
            // 'color' => '#f1a78a'
            'color' => '#e2efda',
        ],
        'coreg_p1_revenue' => [
            'alias' => 'Coreg P1 Rev',
            'desc' => 'NLR Revenue co-re 1: so this is co-reg page 1 revenue.',
            'percentage' => false,
            'color' => '#bfbfbf',
        ],
        'coreg_p1_revenue_vs_views' => [
            'alias' => 'Coreg P1 Revenue / Views',
            'desc' => 'Co-reg 1 views: this co-reg page 1 views according to NLR.',
            'percentage' => true,
            // 'color' => '#ffd184'
            'color' => '#c9c9c9',
        ],
        'coreg_p2_revenue' => [
            'alias' => 'Coreg P2 Revenue',
            'desc' => 'C-reg 2  revenue',
            'percentage' => false,
            'color' => '#97b9e0',
        ],
        'coreg_p2_revenue_vs_views' => [
            'alias' => 'Coreg P2 Revenue / Views.',
            'desc' => 'Coreg 2 rev vs views',
            'percentage' => true,
            // 'color' => '#a1c490'
            'color' => '#c9c9c9',
        ],
        'coreg_p3_revenue_vs_views' => [
            'alias' => 'Coreg P3 Revenue / Views.',
            'desc' => 'Coreg 3 rev vs views',
            'percentage' => true,
            'color' => '#c9c9c9',
        ],
        'coreg_p4_revenue_vs_views' => [
            'alias' => 'Coreg P4 Revenue / Views.',
            'desc' => 'Coreg 4 rev vs views',
            'percentage' => true,
            'color' => '#c9c9c9',
        ],
        'all_mp_revenue' => [
            'alias' => 'All Mid Paths Revenue',
            'desc' => 'Midpath divided by views.',
            'percentage' => true,
            'color' => '#9cb5d8',
        ],
        'survey_takers_per_clicks' => [
            'alias' => 'Survey Takers / Clicks',
            'desc' => 'Suveytakers/clicks  : so this is number of registeration divided by all clicks.',
            'percentage' => true,
            // 'color' => '#bac4e2'
            'color' => '#ffc000',
        ],
        'cpa_per_survey_takers' => [
            'alias' => 'CPA / ST',
            'desc' => 'CPA/survey takers. Taken by # of registeratoins divided by clicks.',
            'percentage' => true,
            'color' => '#d885a8',
        ],
        'mp_per_views' => [
            'alias' => 'All Mid Path Revenue / Views',
            'desc' => 'Midpath divided by views.',
            'percentage' => true,
            // 'color' => '#9e504e'
            'color' => '#9cb5d8',
        ],
        'cost_per_all_clicks' => [
            'alias' => 'Cost / All Clicks',
            'desc' => 'Cost of this Affilaites / All Clicks',
            'percentage' => true,
            'color' => '#ffc000',
        ],
        'cost' => [
            'alias' => 'Cost',
            'desc' => 'Cost of this Affiliates',
            'percentage' => false,
            'color' => '#ffff00',
        ],
        'all_inbox_per_survey_takers' => [
            'alias' => 'All Inbox / Survey Takers',
            'desc' => 'All inbox Revenue / Survey Takers',
            'percentage' => true,
            'color' => '#e2efda',
        ],
        'all_coreg_revenue' => [
            'alias' => 'All Coreg Revenue',
            'desc' => 'All Coreg Revenue of all coreg offers',
            'percentage' => false,
            'color' => '#ffccff',
        ],
        'all_coreg_revenue_per_all_coreg_views' => [
            'alias' => 'All Coreg Revenue / All Views',
            'desc' => 'All coreg revenue of all coreg offers / all views of coreg pages',
            'percentage' => false,
            'color' => '#ffccff',
        ],
        'push_revenue' => [
            'alias' => 'Push Revenue',
            'desc' => 'Midpath divided by views.',
            'percentage' => true,
            'color' => '#7db558',
        ],
        'push_cpa_revenue_per_survey_takers' => [
            'alias' => 'Push CPA Revenue / ST',
            'desc' => 'Midpath divided by views.',
            'percentage' => true,
            'color' => '#7db558',
        ],
        'cpa_revenue' => [
            'alias' => 'CPA Revenue',
            'desc' => 'CPA Revenue: Total link out page revenue, submit page not included, meaning no jobs to shop revenue here.',
            'percentage' => false,
            // 'color' => '#3b64ad'
            'color' => '#ffccff',
        ],
        'cpa_revenue_per_views' => [
            'alias' => 'CPA Revenue / Views',
            'desc' => 'CPA Revenue / Views on the link out page.',
            'percentage' => true,
            // 'color' => '#d26e2a'
            'color' => '#ffffff',
        ],
        'lsp_revenue' => [
            'alias' => 'Last Page Revenue',
            'desc' => 'Submit page Revenue. Jobs to shop.',
            'percentage' => false,
            // 'color' => '#929292'
            'color' => '#ffffff',
        ],
        'lsp_revenue_vs_views' => [
            'alias' => 'Last Page Revenue / Views',
            'desc' => 'Submit page revenue vs views.',
            'percentage' => true,
            // 'color' => '#5089bc'
            'color' => '#ffccff',
        ],
        'lsp_views' => [
            'alias' => 'Last Page Views',
            'desc' => 'Last Submit page views: as tracked by NLR.',
            'percentage' => false,
            'color' => '#e2aa00',
        ],
        'total_allocation' => [
            'alias' => 'Allocation of revenue to line below',
            'desc' => 'Allocation of revenue to line below from all inbox',
            'percentage' => false,
            'color' => '#f2dcdb',
        ],
        'total_revenue_after_allocation' => [
            'alias' => 'Total Revenue per Sub ID after allocation',
            'desc' => 'Total Revenue per Sub ID after allocation from all inbox',
            'percentage' => false,
            'color' => '#f2dcdb',
        ],
        'revenue_per_survey_taker_after_allocation' => [
            'alias' => 'Total Revenue/Survey taker after allocation',
            'desc' => 'Total Revenue/Survey taker after allocation from all inbox',
            'percentage' => false,
            'color' => '#f2dcdb',
        ],
        'survey_taker_per_allocation' => [
            'alias' => 'Allocated Revenue/Survey taker after allocation',
            'desc' => 'Allocated Revenue/Survey taker after allocation from all inbox',
            'percentage' => false,
            'color' => '#f2dcdb',
        ],
    ],

    'categories' => [
        'slide_0' => [
            '2017-11-01',
            '2017-11-02',
            '2017-11-03',
            '2017-11-04',
        ],
    ],

    'series' => [
        'slide_0' => [
            [
                'name' => 'ST',
                'data' => [5887, 6031, 6133, 6983],
            ], [
                'name' => 'Revenue',
                'data' => [5396.032, 5533.321, 5967.306, 7161.643],
            ], [
                'name' => 'Rev Per ST',
                'data' => [0.916601325, 0.917479854, 0.972983206, 1.025582558],
            ], [
                'name' => 'All Clicks',
                'data' => [6351, 5888, 6702, 7546],
            ], [
                'name' => 'Rev Per AC',
                'data' => [0.849635018, 0.939762398, 0.890376902, 0.949064803],
            ], [
                'name' => 'Margin',
                'data' => [12.00, 21.00, 18.00, 23.00],
            ], [
                'name' => 'CPA Revenue',
                'data' => [883.700, 864.950, 1062.750, 1280.150],
            ], [
                'name' => 'CPA Revenue / Views',
                'data' => [0.290118188, 0.447002584, 0.876856436, 0.946856509],
            ], [
                'name' => 'LSP Revenue',
                'data' => [553.000, 536.200, 513.800, 599.200],
            ], [
                'name' => 'LSP Views',
                'data' => [1596, 1078, 614, 683],
            ], [
                'name' => 'LSP Rev / Views',
                'data' => [0.346491230, 0.497402600, 0.836807820, 0.877306000],
            ], [
                'name' => 'PD Rev',
                'data' => [315.742, 310.961, 429.786, 516.803],
            ], [
                'name' => 'PD Rev / Views',
                'data' => [0.088916362, 0.138759929, 0.299085595, 0.313975091],
            ], [
                'name' => 'TB Rev',
                'data' => [465.210, 488.720, 511.050, 541.800],
            ], [
                'name' => 'TB Rev / Views',
                'data' => [0.064684372, 0.107885210, 0.179883844, 0.168627451],
            ], [
                'name' => 'Iff Rev',
                'data' => [91.910, 74.380, 107.360, 126.010],
            ], [
                'name' => 'Iff Rev / Views',
                'data' => [0.028332306, 0.036353861, 0.083875000, 0.087628651],
            ], [
                'name' => 'Rexadz Rev',
                'data' => [125.120, 70.200, 0.000, 0.000],
            ], [
                'name' => 'Rexadz Rev / Views',
                'data' => [0.039482487, 0.035294118, 0.000000000, 0.000000000],
            ], [
                'name' => 'All Inbox Rev',
                'data' => [1832.000, 2126.000, 2005.500, 1930.500],
            ], [
                'name' => 'Coreg P1 Rev',
                'data' => [450.000, 450.000, 450.000, 450.000],
            ], [
                'name' => 'Coreg P1 Rev / Views',
                'data' => [0.081330201, 0.129945134, 0.219298246, 0.197715290],
            ], [
                'name' => 'Coreg P2 Rev',
                'data' => [450.000, 450.000, 450.000, 450.000],
            ], [
                'name' => 'Coreg P2 Rev / Views',
                'data' => [0.096566520, 0.154162380, 0.256118380, 0.229591840],
            ], [
                'name' => 'ST / Clicks',
                'data' => [93.00, 102.00, 92.00, 93.00],
            ], [
                'name' => 'CPA / ST',
                'data' => [0.15, 0.14, 0.17, 0.18],
            ], [
                'name' => 'MP / Views',
                'data' => [0.45, 0.52, 0.46, 0.41],
            ],
        ],
    ],
];
