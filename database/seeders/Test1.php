<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Client;
use QRFeedz\Cube\Models\Country;

class Test1 extends Seeder
{
    public function run()
    {
        /**
         * 3 Clients: McDonalds, Porto Novo and Roche.
         * 2 Users (std and admin) per client (@mcd.com, @portonovo.ch, @roche.ch).
         * 3 affiliates, 1 with Mcd and Porto Novo, 2nd with Roche,
         * 3rd with none. (affmcd@qrfeedz.ai, affpn@qrfeedz.ai, affrch@qrfeedz.ai)
         */
        $clientRoche = Client::create([
            'name' => 'Roche',
            'address' => 'Wurmisweg',
            'postal_code' => '4303',
            'locality' => 'Kaiseraugst',
            'default_locale' => 'en',
            'country_id' => Country::firstWhere('name', 'Switzerland')->id,
            'vat_number' => '507643121',
        ]);

        $clientRoche = Client::create([
            'name' => 'Porto Novo',
            'address' => 'Rua das Tasquinhas',
            'postal_code' => '1240-111',
            'locality' => 'Lisboa',
            'default_locale' => 'pt',
            'country_id' => Country::firstWhere('name', 'Portugal')->id,
            'vat_number' => '214066750',
        ]);
    }
}
