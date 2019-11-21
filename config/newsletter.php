<?php

return [

    'driver' => env('MAIL_NEWSLETTER_DRIVER', 'null'),

    /*
     * The list to use when no list has been specified in a method.
     */
    'defaultList' => 'subscribers',

    /*
     * Here you can define properties of the lists.
     */
    'lists' => [

        /*
         * This key is used to identify this list. It can be used
         * as the list parameter provided in the various methods.
         *
         * You can set it to any string you want and you can add
         * as many lists as you want.
         */
        'subscribers' => [

            /*
             * A list id.
             */
            'id' => env('MAIL_NEWSLETTER_LIST_ID'),
        ],
    ],


    'mailchimp' => [
        /*
         * The API key of a MailChimp account. You can find yours at
         * https://us10.admin.mailchimp.com/account/api-key-popup/.
         */
        'apiKey' => env('MAILCHIMP_APIKEY'),

        /*
        * If you're having trouble with https connections, set this to false.
        */
        'ssl' => true,
    ],

    'mailjet' => [
        'key' => env('MJ_APIKEY_PUBLIC'),
        'secret' => env('MJ_APIKEY_PRIVATE'),
    ]

];
