<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class PricesController extends Controller
{
    public function index()
    {
        $cfg = config('prices');

        return response()->json([
            'success' => true,
            'data'    => [
                // Direct cash amounts (INR)
                'listing_unlock_amount'       => $cfg['listing_unlock']['amount'],
                'worker_unlock_amount'        => $cfg['worker_unlock']['amount'],
                'listing_boost_amount'        => $cfg['listing_boost']['amount'],
                'listing_boost_days'          => $cfg['listing_boost']['duration_days'],

                // Vastoq Points deducted from wallet per unlock
                'listing_points_cost'         => $cfg['listing_unlock']['points_cost'],
                'worker_points_cost'          => $cfg['worker_unlock']['points_cost'],

                // Points pack (bundle purchase)
                'vastoq_points_pack_amount'   => $cfg['vastoq_points_pack']['amount'],
                'vastoq_points_pack_points'   => $cfg['vastoq_points_pack']['points'],

                // Aliases for legacy key names
                'listing_boost'               => $cfg['listing_boost']['amount'],
                'listing_boost_duration_days' => $cfg['listing_boost']['duration_days'],
                'listing_unlock'              => $cfg['listing_unlock']['amount'],
                'worker_unlock'               => $cfg['worker_unlock']['amount'],
            ],
        ]);
    }
}
