<?php

return [
    'listing_unlock' => [
        'amount'      => 20, // Fallback per-unlock direct payment (INR) — rarely used
        'currency'    => 'INR',
        'name'        => 'Listing Unlock - Contact & Location',
        'points_cost' => 20, // Vastoq Points deducted per listing unlock
    ],
    'worker_unlock' => [
        'amount'      => 10, // Fallback per-unlock direct payment (INR) — rarely used
        'currency'    => 'INR',
        'name'        => 'Worker Unlock - Contact Details',
        'points_cost' => 10, // Vastoq Points deducted per worker unlock
    ],
    'listing_boost' => [
        'amount'        => 99,
        'currency'      => 'INR',
        'name'          => 'Boost Listing - 7 Days Featured',
        'duration_days' => 7,
    ],
    'vastoq_points_pack' => [
        'amount'   => 99,   // Price in INR
        'currency' => 'INR',
        'points'   => 100,  // Vastoq Points granted on purchase
        'name'     => 'Vastoq Points Pack — 100 Points (₹99)',
    ],
    // Legacy alias kept for backward compat during transition
    'premium_unlock_package' => [
        'amount'   => 99,
        'currency' => 'INR',
        'points'   => 100,
        'unlocks'  => 5,
        'name'     => 'Vastoq Points Pack — 100 Points (₹99)',
    ],
];
