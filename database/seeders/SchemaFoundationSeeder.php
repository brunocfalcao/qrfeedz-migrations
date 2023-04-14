<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Authorization;
use QRFeedz\Cube\Models\Category;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Locale;
use QRFeedz\Cube\Models\PageType;
use QRFeedz\Cube\Models\User;
use QRFeedz\Cube\Models\WidgetType;

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
         * Super system admin credentials registration.
         */
        $sysadmin = User::create([
            'name' => env('QRFEEDZ_SUPER_ADMIN_NAME'),
            'email' => env('QRFEEDZ_SUPER_ADMIN_EMAIL'),
            'password' => bcrypt(env('QRFEEDZ_SUPER_ADMIN_PASSWORD')),
            'is_admin' => true,
        ]);

        /**
         * Add default categories.
         */
        Category::create(['name' => 'Event']);
        Category::create(['name' => 'Hotel']);
        Category::create(['name' => 'Cantine']);
        Category::create(['name' => 'Restaurant']);
        Category::create(['name' => 'Store']);

        /**
         * Authorizations creation.
         * admin
         * gpdr
         * affiliate
         */
        Authorization::create([
            'canonical' => 'admin',
            'name' => 'Client Administrator',
            'description' => 'Generic admin, can admin its own client, respective groups and questionnaires. Can delete questionnaires that dont have data yet. Can change users, and delete them, but not delete himself. Can trigger reset passwords']);

        Authorization::create([
            'canonical' => 'questionnaire-admin',
            'name' => 'Questionnaire Administrator',
            'description' => 'Can only admin questionnaires, create, change and delete (both data-limited) from its own client',
        ]);

        Authorization::create([
            'canonical' => 'affiliate',
            'name' => 'Affiliate',
            'description' => 'Its an admin for the clients that are attached to the affiliate. Used for commercial reasons',
        ]);

        Authorization::create([
            'canonical' => 'gdpr',
            'name' => 'GDPR',
            'description' => 'The GDPR role allows the user to see personal data, like visitors emails, widgets instances marked as having personal data',
        ]);

        Authorization::create([
            'canonical' => 'questionnaire-view',
            'name' => 'View-Only to Questionnaires',
            'description' => 'Standard questionnaire role, for view access only',
        ]);

        Authorization::create([
            'canonical' => 'view',
            'name' => 'View generic access',
            'description' => 'Access to view a specific asset. Cannot update or delete it',
        ]);

        Authorization::create([
            'canonical' => 'update',
            'name' => 'Update generic access',
            'description' => 'Access to update a specific asset. Cannot delete it',
        ]);

        Authorization::create([
            'canonical' => 'delete',
            'name' => 'Delete generic access',
            'description' => 'Access to delete a specific asset. Normally for admins',
        ]);

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
         * Pages creation.
         *
         * 'splash-page' - Blank splash page with logo + questionnaire title.
         * 'locale-select' - Flags, oneliner for locale selection.
         * 'survey' - Default survey form (header, content, voice recorder).
         * 'promo'  - Default promo page (header, message title, email field).
         * 'social' - Social sharing page, normally the last page.
         *
         * There are the following sliding contexts:
         * 'global' - The slide/paging will happen at the highest level.
         * 'survey' - The slide/paging will happen at the survey pages.
         *
         * The transitions are calculated by the UI framework, but mostly
         * between globals are full page transitions, and between surveys
         * are component transitions.
         * A transition can also reload the page. Like for instance when the
         * visitor selects a new language. The page is reloaded to a specific
         * page rendering (not to the beginning of the 1st page).
         *
         * A page reload is used as:
         * <url>.ai/<uuid>?p=<uuid>&locale=??
         *
         * This will reload a survey directly to the uuid page, from the
         * qrcode uuid. They both need to math in the same data model.
         * The locale querystring will render the survey with that locale.
         */
        PageType::create([
            'name' => 'Splash page - 5 seconds',
            'canonical' => 'splash-page-5-secs',
            'sliding_context' => 'global',
            'description' => 'A splash full page, with logo or questionnaire title, lasts 5 seconds',
            'view_component_namespace' => 'qrfeedz::splash-5-secs',
        ]);

        PageType::create([
            'name' => 'Local selection page',
            'canonical' => 'locale-select-page',
            'sliding_context' => 'global',
            'description' => 'A list of locales that are available for the questionnaire',
            'view_component_namespace' => 'qrfeedz::locale-select',
        ]);

        PageType::create([
            'name' => 'Survey page (default)',
            'canonical' => 'survey-page-default',
            'sliding_context' => 'survey',
            'description' => 'Survey structure page - default questions structure',
            'view_component_namespace' => 'qrfeedz::survey-page-default',
        ]);

        PageType::create([
            'name' => 'Promo page',
            'canonical' => 'promo-page-default',
            'sliding_context' => 'global',
            'description' => 'Promotional default page',
            'view_component_namespace' => 'qrfeedz::promo-page-default',
        ]);

        PageType::create([
            'name' => 'Social sharing page',
            'canonical' => 'social-page-default',
            'sliding_context' => 'global',
            'description' => 'Social sharing default page',
            'view_component_namespace' => 'qrfeedz::social-page-default',
        ]);

        /**
         * Widgets creation.
         *
         * Emoji rating.
         * Stars rating.
         * One Liner.
         */
        WidgetType::create([
            'name' => 'Emoji faces rating',
            'canonical' => 'emoji-faces-rating',
            'description' => 'An emoji rating, 5 faces from very sad to very happy. Gray-based, then when the visitor touches the emoji it gets transformed into color',
            'view_component_namespace' => 'emoji-faces-rating',
        ]);

        WidgetType::create([
            'name' => 'Stars rating',
            'canonical' => 'stars-rating',
            'description' => 'A stars rating with stars. Visitor touches a star and it selects the right value of it',
            'view_component_namespace' => 'emoji-faces-rating',
        ]);

        WidgetType::create([
            'name' => 'Emoji slider rating',
            'canonical' => 'emoji-slider-rating',
            'description' => 'A slider that shows emoji faces as long as the visitor slides it',
            'view_component_namespace' => 'emoji-slider-rating',
        ]);

        WidgetType::create([
            'name' => 'Promo Coupon Page',
            'canonical' => 'promo-coupon-page',
            'description' => 'Offers a coupon to the visitor, after entering the email. Will have the social links from the questionnaire in the bottom',
            'view_component_namespace' => 'promo-coupon-page',
            'is_countable' => false,
            'is_full_page' => true,
        ]);
    }
}
