<?php

return [
    'listing_unlock' => [
        'amount'      => 25, // Direct per-unlock cash price (INR) — individual checkout
        'currency'    => 'INR',
        'name'        => 'Listing Unlock - Contact & Location',
        'points_cost' => 20, // Vastoq Points deducted per listing unlock (wallet rate)
    ],
    'worker_unlock' => [
        'amount'      => 15, // Direct per-unlock cash price (INR) — individual checkout
        'currency'    => 'INR',
        'name'        => 'Worker Unlock - Contact Details',
        'points_cost' => 10, // Vastoq Points deducted per worker unlock (wallet rate)
    ],
    'listing_boost' => [
        'amount'        => 99,
        'currency'      => 'INR',
        'name'          => 'Boost Listing - 7 Days Featured',
        'duration_days' => 7,
    ],
    'vastoq_points_pack' => [
        'amount'   => 59,   // Price in INR
        'currency' => 'INR',
        'points'   => 60,   // Vastoq Points granted on purchase
        'name'     => 'Vastoq Points Pack — 60 Points (₹59)',
    ],
    // Legacy alias kept for backward compat
    'premium_unlock_package' => [
        'amount'   => 59,
        'currency' => 'INR',
        'points'   => 60,
        'unlocks'  => 3,
        'name'     => 'Vastoq Points Pack — 60 Points (₹59)',
    ],
];
