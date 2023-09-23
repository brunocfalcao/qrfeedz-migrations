<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Authorization;
use QRFeedz\Cube\Models\Category;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Locale;
use QRFeedz\Cube\Models\Page;
use QRFeedz\Cube\Models\User;
use QRFeedz\Cube\Models\Widget;

class SchemaFoundationSeeder extends Seeder
{
    public function run()
    {
        $countries = collect([
            'AF' => 'Afghanistan',
            'AX' => 'Aland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BQ' => 'Bonaire, Sint Eustatius and Saba',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Congo, Democratic Republic',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => "Cote D'Ivoire",
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CW' => 'Curaçao',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Malvinas)',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and Mcdonald Islands',
            'VA' => 'Holy See (Vatican City State)',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran, Islamic Republic Of',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle Of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KP' => "Korea, Democratic People's Republic Of",
            'KR' => 'Korea',
            'XK' => 'Kosovo',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => "Lao People's Democratic Republic",
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia, Federated States Of',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory, Occupied',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda',
            'BL' => 'Saint Barthelemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SX' => 'Sint Maarten (Dutch part)',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and Sandwich Isl.',
            'SS' => 'South Sudan',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UM' => 'United States Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'VG' => 'Virgin Islands, British',
            'VI' => 'Virgin Islands, U.S.',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        ]);

        foreach ($countries as $code => $name) {
            Country::create([
                'code' => $code,
                'name' => $name,
            ]);
        }

        /**
         * Locales creation.
         * en, pt, de, fr, it
         */
        Locale::create([
            'canonical' => 'en',
            'name' => 'English',
        ]);

        Locale::create([
            'canonical' => 'it',
            'name' => 'Italian',
        ]);

        Locale::create([
            'canonical' => 'fr',
            'name' => 'French',
        ]);

        Locale::create([
            'canonical' => 'pt',
            'name' => 'Portuguese',
        ]);

        Locale::create([
            'canonical' => 'de',
            'name' => 'German',
        ]);

        /**
         * Authorizations types creation.
         */
        Authorization::create([
            'canonical' => 'affiliate',
            'name' => 'Client Affiliate',
            'description' => 'Client affiliate, means receives monthly commissions',
        ]);

        Authorization::create([
            'canonical' => 'admin',
            'name' => 'System admin',
            'description' => 'Like the super admin, but with less specific authorizations, for instance cannot delete a client that was not created by him/her',
        ]);

        Authorization::create([
            'canonical' => 'client-admin',
            'name' => 'Client Administrator',
            'description' => 'Generic admin, can admin its own client, respective groups and questionnaires. Can delete questionnaires that dont have data yet. Can change users, and delete them, but not delete himself. Can trigger reset passwords',
        ]);

        Authorization::create([
            'canonical' => 'location-admin',
            'name' => 'Location Administrator',
            'description' => 'For persons that need to access questionnaires from a specific location (like hotel managers, McDonalds team leads)',
        ]);

        Authorization::create([
            'canonical' => 'questionnaire-admin',
            'name' => 'Questionnaire Administrator',
            'description' => 'For persons that need to admin a questionnaire, like changing notifications, or OpenAI configurations, etc',
        ]);

        Page::create([
            'name' => 'Default full screen',
            'canonical' => 'full-screen',
            'view_component_namespace' => 'pages.full-screen',
            'description' => 'A default fullscreen page, mostly used on all page instance transitions',
        ]);

        /**
         * Widgets creation.
         *
         * Emoji rating.
         * Stars rating.
         * One Liner.
         */
        Widget::create([
            'name' => 'Emoji faces rating',
            'canonical' => 'emoji-faces-rating',
            'description' => 'Emoji rating, 5 faces from very sad to very happy. Gray-based, then when the visitor touches the emoji it gets transformed into color',
            'view_component_namespace' => 'widgets.emoji-faces-rating',
        ]);

        Widget::create([
            'name' => 'Stars rating',
            'canonical' => 'stars-rating',
            'description' => 'Stars rating with stars. Visitor touches a star and it selects the right value of it',
            'view_component_namespace' => 'widgets.stars-rating',
        ]);

        Widget::create([
            'name' => 'Emoji slider rating',
            'canonical' => 'emoji-slider-rating',
            'description' => 'Slider that shows emoji faces as long as the visitor slides it',
            'view_component_namespace' => 'widgets.emoji-slider-rating',
        ]);

        Widget::create([
            'name' => 'Textarea',
            'canonical' => 'textarea',
            'description' => 'Standard textarea to store feedback text',
            'view_component_namespace' => 'widgets.textarea',
        ]);

        /** ----- Special Widgets for "full page" page types ---- */
        Widget::create([
            'name' => 'Splash 1',
            'canonical' => 'splash-1',
            'description' => 'Horizontally and centered logo + questionnaire name + footer with client name, for full screen pages. Fades in and out the logo / questionnaire title',
            'view_component_namespace' => 'widgets.splash-1',
        ]);

        Widget::create([
            'name' => 'Locales selectors',
            'canonical' => 'locale-selector-1',
            'description' => 'Big buttons for a full screen, to select a locale from a locales list',
            'view_component_namespace' => 'widgets.locale-selectors-1',
        ]);

        /**
         * Super system admin credentials registration.
         */
        $superAdmin = User::create([
            'name' => env('QRFEEDZ_SUPER_ADMIN_NAME'),
            'email' => env('QRFEEDZ_SUPER_ADMIN_EMAIL'),
            'password' => bcrypt(env('QRFEEDZ_SUPER_ADMIN_PASSWORD')),
            'is_super_admin' => true,
        ]);

        /**
         * Tester creation.
         */
        $admin = User::create([
            'name' => env('QRFEEDZ_ADMIN_NAME'),
            'email' => env('QRFEEDZ_ADMIN_EMAIL'),
            'password' => bcrypt(env('QRFEEDZ_ADMIN_PASSWORD')),
            'is_admin' => true,
        ]);

        Category::create([
            'name' => 'Hotel',
            'canonical' => 'hotel',
            'description' => 'Hotel-based feedbacks',
        ]);

        Category::create([
            'name' => 'Restaurant',
            'canonical' => 'restaurant',
            'description' => 'Restaurant-based feedbacks',
        ]);

        Category::create([
            'name' => 'Product',
            'canonical' => 'product',
            'description' => 'Product-based feedbacks',
        ]);
    }
}
