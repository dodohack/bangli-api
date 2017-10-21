<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Advertisement positions definitions
     |--------------------------------------------------------------------------
     |
     | We do not define the positions in database because the client has
     | hardcoded positions into it's template, if the position changes,
     | the client should also be updated.
     |
     |
     | legend: ['position id', suggested width, suggested height, 'title' ]
     |
     |  * position id: we use pc, sm, xs to indicate 3 different screen sizes
     |  * suggested width/height: displayed on client as a guide
     |  * title: displayed on client as a guide
     */

    'positions' => [
        // Desktop only ads
        ['ad-pc-top-banner', 728, 80, '桌面顶部位置横幅'],
        ['ad-pc-sidebar-banner-1', 400, 200, '桌面侧栏广告位1'],
        ['ad-pc-sidebar-banner-2', 400, 300, '桌面侧栏广告位2'],
        ['ad-pc-sidebar-banner-3', 400, 300, '桌面侧栏广告位3'],

        // Mobile only ads
        ['ad-xs-top-banner', 500, 200, '手机版顶部横幅'],
        ['ad-xs-bottom-banner', 500, 200, '手机版底部横幅'],

        // Common ads
        ['ad-all-in-offer-list', 700, 300, '优惠列表中的广告位'],
    ]
];
