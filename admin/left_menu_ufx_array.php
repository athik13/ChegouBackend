<?php

//added by SP as discussed with bmam on 28-6-2019
$adminUsersTxt = $langage_lbl_admin['LBL_ADMIN'];
if ($PACKAGE_TYPE == 'SHARK' && ($APP_TYPE == 'Ride' || $APP_TYPE == 'Ride-Delivery-UberX') && ONLYDELIVERALL == 'No') {
    $adminUsersTxt = $langage_lbl_admin['LBL_ADMIN'] . '/Hotel';
}
$menu = [
    [
        'title' => 'Dashboard',
        'url' => "dashboard.php",
        "icon" => 'fa fa-tachometer',
		], [
        'title' => 'Site Statistics',
        'url' => "dashboard-a.php",
        "icon" => 'fa fa-sitemap',
        "active" => "site",
        "visible" => $userObj->hasPermission('view-site-statistics'),
    ], [
        'title' => 'Admin',
        'url' => "javascript:",
        "icon" => ['class' => "icon-user1", 'url' => "images/icon/admin-icon.png"],
        "visible" => ($userObj->hasRole(1) || $userObj->hasPermission('view-admin')),
        'children' => [
            [
                'title' => $adminUsersTxt,
                'url' => "admin.php",
                "icon" => "fa fa-certificate",
                "active" => "Admin",
            ], [
                'title' => 'Admin Groups',
                'url' => "admin_groups.php",
                "icon" => "fa fa-certificate",
                "active" => "AdminGroups",
                "visible" => $userObj->hasRole(1) && $PACKAGE_TYPE == 'SHARK',
            ], /* [
          'title'   => 'Permissions',
          'url'     => "admin_permissions.php",
          "icon"    => "fa fa-certificate",
          "active"  => "AdminPermissions",
          "visible" => $userObj->hasRole(1),
          ], */
        ],
    ], [
        'title' => 'Company',
        'url' => "company.php",
        "icon" => "fa fa-building-o",
        "active" => "Company",
        "visible" => $userObj->hasPermission('view-company'),
    ], [
        'title' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
        'url' => "driver.php",
        "icon" => ["class" => "icon-user1", "url" => "images/icon/driver-icon.png"],
        "active" => "Driver",
        "visible" => $userObj->hasPermission('view-providers'),
    ], 
	[
        'title' => $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'],
        'url' => "rider.php",
        "icon" => ["class" => "icon-group1", "url" => "images/rider-icon.png"],
        "active" => "Rider",
        "visible" => $userObj->hasPermission('view-users'),
    ],
	[
        'title' => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION'],
        'url' => "javascript:",
        "icon" => ['class' => "icon-user1", 'url' => "images/icon/subscription-icon.png"],
        "visible" => (($userObj->hasPermission('manage-driver-subscription') || $userObj->hasPermission('manage-driver-subscription-report')) && $DRIVER_SUBSCRIPTION_ENABLE == 'Yes' && ONLYDELIVERALL != "Yes"),
        'children' => [
            [
                'title' => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_PLAN'],
                'url' => "driver_subscription.php",
                "icon" => "fa fa-building-o",
                "active" => "DriverSubscriptionPlan",
                "visible" => ($userObj->hasPermission('manage-driver-subscription') && $DRIVER_SUBSCRIPTION_ENABLE == 'Yes' && ONLYDELIVERALL != "Yes"),
            ],
            [
                'title' => $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_REPORT'],
                'url' => "driver_subscription_report.php",
                "icon" => ["class" => "icon-user1", "url" => "images/icon/subscriptionreport-icon.png"],
                "active" => "DriverSubscriptionReport",
                "visible" => ($userObj->hasPermission('manage-driver-subscription-report') && $DRIVER_SUBSCRIPTION_ENABLE == 'Yes' && ONLYDELIVERALL != "Yes"),
            ],
        ]
    ],
    [
        'title' => "Manage Services",
        "icon" => "fa fa-wrench",
        "visible" => ($userObj->hasPermission('manage-services') && $APP_TYPE != 'Ride-Delivery-UberX'),
        'children' => [
            [
                'title' => 'Service Category',
                "url" => "vehicle_category.php",
                "icon" => "fa fa-certificate",
                "active" => "VehicleCategory",
                "visible" => $userObj->hasPermission('view-provider-taxis'),
            ], [
                'title' => "Service Type",
                "url" => "service_type.php",
                "icon" => "fa fa-wrench",
                "active" => "ServiceType",
                "visible" => $userObj->hasPermission('view-provider-taxis'),
            ], [
                'title' => "App Main Screen Settings",
                'url' => "app_home_settings.php",
                "icon" => "fa fa-globe",
                "active" => "App Main Screen Settings",
                "visible" => ($userObj->hasPermission('manage-app-main-screen-settings') && $APP_TYPE == 'Ride-Delivery-UberX'),
            ], [
                'title' => $langage_lbl_admin['LBL_DRIVER_SERVICE_REQUESTS_TXT'],
                "url" => "driver_service_request.php",
                "icon" => "fa fa-wrench",
                "active" => "DriverRequest",
                "visible" => $userObj->hasPermission('view-driver-service-request') && ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == "UberX"),
            ],
        ],
    ], [
        'title' => "Manual Booking",
        'url' => "add_booking.php",
        "icon" => ["class" => "fa fa-taxi1", "url" => "images/manual-taxi-icon.png"],
        "active" => "booking",
        "visible" => ($userObj->hasPermission('manage-manual-booking')),
    ], [
        'title' => $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'],
        'url' => "trip.php",
        "icon" => ["class" => "fa fa-exchange1", "url" => "images/trips-icon.png"],
        "active" => "Trips",
        "visible" => $userObj->hasPermission('manage-trip-jobs'),
    ], [
        'title' => $langage_lbl_admin['LBL_RIDE_LATER_BOOKINGS_ADMIN'],
        'url' => "cab_booking.php",
        "icon" => ["class" => "icon-book1", "url" => "images/ride-later-bookings.png"],
        "active" => "CabBooking",
        "visible" => ($userObj->hasPermission('manage-ride-job-later-bookings') && $RIDE_LATER_BOOKING_ENABLED == 'Yes'),
    ], [
        'title' => $langage_lbl_admin['LBL_MANUAL_STORE_ORDER_TXT'],
        'url' => "../user-order-information?order=admin",
        "icon" => ["class" => "fa fa-taxi1", "url" => "images/shopping-cart.png"],
        "active" => "store_order_book",
        "target" => "blank",
        "visible" => ($userObj->hasPermission('manage-restaurant-order') && DELIVERALL == "Yes" && $MANUAL_STORE_ORDER_ADMIN_PANEL=="Yes"),
    ]
    , [
        'title' => "PromoCode",
        "url" => "coupon.php",
        "icon" => ["class" => "fa fa-product-hunt1", "url" => "images/promo-code-icon.png"],
        "active" => "Coupon",
        "visible" => $userObj->hasPermission('view-promocode'),
    ], [
        'title' => "God's View",
        "url" => "map.php",
        "icon" => ["class" => "icon-map-marker1", "url" => "images/god-view-icon.png"],
        "active" => "LiveMap",
        "visible" => $userObj->hasPermission('manage-gods-view'),
    ], [
        'title' => "Heat View",
        "url" => "heatmap.php",
        "icon" => ["class" => "fa-header1", "url" => "images/heat-icon.png"],
        "active" => "Heat Map",
        "visible" => $userObj->hasPermission('manage-heat-view'),
    ], [
        'title' => "Reviews",
        "url" => "review.php",
        "icon" => ["class" => "icon-comments1", "url" => "images/reviews-icon.png"],
        "active" => "Review",
        "visible" => $userObj->hasPermission('manage-reviews'),
    ], [
        'title' => "Advertisement Banners",
        'url' => "advertise_banners.php",
        "icon" => "fa fa-bullhorn",
        "active" => "Advertisement Banners",
        "visible" => $userObj->hasPermission('view-advertise-banner') && ($ADVERTISEMENT_TYPE != 'Disable' && $PACKAGE_TYPE == 'SHARK'),
    ],
    [
        'title' => 'Decline/Cancelled Alert',
        'url' => "blocked_driver.php",
        "icon" => "fa fa-bullhorn",
        "active" => "Driver",
        "visible" => $userObj->hasPermission('view-blocked-driver')&& $PACKAGE_TYPE == 'SHARK' && strtoupper(ONLYDELIVERALL) != "YES", //This Module Enable For Shark Package As Per Discss With KS Sir By HJ On 05-11-2019
        'children' => [
            [
                'title' => 'Alert For ' . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                'url' => "blocked_driver.php",
                "icon" => "fa fa-user",
                "active" => "blockeddriver",
                "visible" => $userObj->hasPermission('view-blocked-driver'),
            ], [
                'title' => 'Alert For ' . $langage_lbl_admin['LBL_RIDER'],
                'url' => "blocked_rider.php",
                "icon" => "fa fa-user",
                "active" => "blockedrider",
                "visible" => $userObj->hasPermission('view-blocked-rider'),
            ]
        ],
    ]
    , [
        'title' => "Reports",
        "icon" => ["class" => "icon-cogs1", "url" => "images/reports-icon.png"],
        "visible" => $userObj->hasPermission('manage-report'),
        'children' => [
            [
                'title' => "Payment Report",
                "url" => "payment_report.php",
                "icon" => "icon-money",
                "active" => "Payment_Report",
                "visible" => $userObj->hasPermission('manage-payment-report'),
            ], [
                'title' => "Referral Report",
                "url" => "referrer.php",
                "icon" => "fa fa-hand-peace-o",
                "active" => "referrer",
                "visible" => ($userObj->hasPermission('manage-referral-report') && $REFERRAL_SCHEME_ENABLE == 'Yes'),
            ], [
                'title' => "Wallet Report",
                "url" => "wallet_report.php",
                "icon" => "fa fa-google-wallet",
                "active" => "Wallet Report",
                "visible" => ($userObj->hasPermission('manage-user-wallet-report') && $WALLET_ENABLE == 'Yes'),
            ], [
                'title' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Payment Report",
                "url" => "driver_pay_report.php",
                "icon" => "icon-money",
                "active" => "Driver Payment Report",
                "visible" => $userObj->hasPermission('manage-provider-payment-report'),
            ],
        ],
    ], [
        'title' => "Manage Locations",
        "icon" => ["class" => "fa fa-header1", "url" => "images/location-icon.png"],
        "visible" => $userObj->hasPermission('manage-locations'),
        'children' => [
            [
                'title' => "Geo Fence Location",
                "url" => "location.php",
                "icon" => "fa fa-map-marker",
                "active" => "Location",
                "visible" => $userObj->hasPermission('view-geo-fence-locations'),
            ],
            [
                'title' => "Restricted Area",
                "url" => "restricted_area.php",
                "icon" => "fa fa-map-signs",
                "active" => "Restricted Area",
                "visible" => $userObj->hasPermission('view-restricted-area'),
            ]
        ],
    ], [
        'title' => "Settings",
        "icon" => ["class" => "icon-cogs1", "url" => "images/settings-icon.png"],
        "visible" => $userObj->hasPermission('manage-settings'),
        'children' => [
            [
                'title' => "General",
                "url" => "general.php",
                "icon" => "fa-cogs fa",
                "active" => "General",
                "visible" => $userObj->hasPermission('manage-general-settings'),
            ],
            [
                'title' => "Email Templates",
                "url" => "email_template.php",
                "icon" => "fa fa-envelope",
                "active" => "Email Templates",
                "visible" => $userObj->hasPermission('view-email-templates'),
            ],
            [
                'title' => "SMS Templates",
                "url" => "sms_template.php",
                "icon" => "fa fa-comment",
                "active" => "SMS Templates",
                "visible" => $userObj->hasPermission('view-sms-templates'),
            ],
            [
                'title' => "Manage Documents",
                "url" => "document_master_list.php",
                "icon" => "fa fa-file-text",
                "active" => "Manage Documents",
                "visible" => $userObj->hasPermission('view-documents'),
            ], [
                'title' => "Language Label",
                "url" => "languages.php",
                "icon" => "fa fa-language",
                "active" => "language_label",
                "visible" => $userObj->hasPermission('manage-language-label'),
            ], [
                'title' => "Currency",
                "url" => "currency.php",
                "icon" => "fa fa-usd",
                "active" => "Currency",
                "visible" => $userObj->hasPermission('manage-currency'),
            ], [
                'title' => "Language",
                "url" => "language.php",
                "icon" => "fa fa-language",
                "active" => "Language",
                "visible" => $userObj->hasPermission('manage-language'),
            ], [
                'title' => "SEO Settings",
                "url" => "seo_setting.php",
                "icon" => "fa fa-info",
                "active" => "seo_setting",
                "visible" => $userObj->hasPermission('view-seo-setting'),
            ], [
                'title' => "Banner",
                "url" => "banner.php",
                "icon" => "icon-angle-right",
                "active" => "Banner",
                "visible" => $userObj->hasPermission('view-banner'),
            ],
        ],
    ], [
        'title' => "Utility",
        "icon" => "fa fa-wrench",
        "visible" => $userObj->hasPermission('manage-utility'),
        'children' => [
            [
                'title' => "Localization",
                "icon" => "fa fa-globe",
                "visible" => $userObj->hasPermission('manage-localization'),
                'children' => [
                    [
                        'title' => "Country",
                        "url" => "country.php",
                        "icon" => "fa fa-dot-circle-o",
                        "active" => "country",
                        "visible" => $userObj->hasPermission('view-country'),
                    ],
                    [
                        'title' => "State",
                        "url" => "state.php",
                        "icon" => "fa fa-dot-circle-o",
                        "active" => "state",
                        "visible" => $userObj->hasPermission('view-state'),
                    ],
                    [
                        'title' => "City",
                        "url" => "city.php",
                        "icon" => "fa fa-dot-circle-o",
                        "active" => "city",
                        "visible" => $userObj->hasPermission('view-city') && ($SHOW_CITY_FIELD == 'Yes'),
                    ],
                ],
            ], [
                'title' => "Pages",
                "url" => "page.php",
                "icon" => "fa fa-file-text-o",
                "active" => "page",
                "visible" => $userObj->hasPermission('view-pages'),
            ], [
                'title' => "Edit Home Page",
                "url" => "home_content.php",
                "icon" => "fa fa-file-text-o",
                "active" => "home_content",
                "visible" => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE != 'Ride-Delivery-UberX'),
            ],
            [
                'title' => "News",
                "url" => "news.php",
                "icon" => "fa fa-file-text-o",
                "active" => "news",
                "visible" => $userObj->hasPermission('view-news') && ($ENABLE_NEWS_SECTION == 'Yes'),
            ], [
                'title' => "Newsletter Subscribers",
                "url" => "newsletter.php",
                "icon" => "fa fa-file-text-o",
                "active" => "newsletters-subscribers",
                "visible" => $userObj->hasPermission('manage-newsletter') && ($ENABLE_NEWSLETTERS_SUBSCRIPTION_SECTION == 'Yes'),
            ],
            [
                'title' => "Faq",
                "url" => "faq.php",
                "icon" => "fa fa-question",
                "active" => "Faq",
                "visible" => $userObj->hasPermission('view-faq'),
            ], [
                'title' => "Faq Categories",
                "url" => "faq_categories.php",
                "icon" => "fa fa-question-circle-o",
                "active" => "faq_categories",
                "visible" => $userObj->hasPermission('view-faq-categories'),
            ], [
                'title' => "Help Topics",
                "url" => "help_detail.php",
                "icon" => "fa fa-question",
                "active" => "help_detail",
                "visible" => $userObj->hasPermission('view-help-detail'),
            ], [
                'title' => "Help Topic Categories",
                "url" => "help_detail_categories.php",
                "icon" => "fa fa-question-circle-o",
                "active" => "help_detail_categories",
                "visible" => $userObj->hasPermission('view-help-detail-category'),
            ], [
                'title' => "Cancel Reason",
                "url" => "cancellation_reason.php",
                "icon" => "fa fa-question",
                "active" => "cancel_reason",
                "visible" => $userObj->hasPermission('view-cancel-reasons'),
            ], [
                'title' => "Donation",
                "url" => "donation.php",
                "icon" => "fa fa-money",
                "active" => "Donation",
                "visible" => $userObj->hasPermission('view-donation') && ($DONATION == 'Yes' && $DONATION_ENABLE == 'Yes'),
            ],
            [
                'title' => $langage_lbl_admin['LBL_PACKAGE_TYPE_ADMIN'],
                "url" => "package_type.php",
                "icon" => "fa fa-globe",
                "active" => "Package",
                "visible" => $userObj->hasPermission('view-package-type') && ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Delivery') && ONLYDELIVERALL != "Yes" && $leftdeliveryEnable == "Yes", // ONLYDELIVERALL != "Yes Added By HJ On 05-07-2019 As Per Discuss With KS Sir
            ],
           [
                'title' => $langage_lbl_admin['LBL_MULTI_DELIVERY_FORM'],
                "url" => "delivery_fields.php",
                "icon" => "fa fa-globe",
                "active" => "delivery_package",
                "visible" => $userObj->hasPermission('view-delivery-field') && ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Delivery') && ONLYDELIVERALL != "Yes" && $leftdeliveryEnable == "Yes", 
            ],
            [
                'title' => "Our " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                "url" => "home_driver.php",
                "icon" => "fa fa-users",
                "active" => "home_driver",
                "visible" => $userObj->hasPermission('view-our-provider') && ($APP_TYPE != 'Ride-Delivery-UberX' && $APP_TYPE != 'Ride'),
            ], [
                'title' => "Send Push-Notification",
                "url" => "send_notifications.php",
                "icon" => "fa fa-globe",
                "active" => "Push Notification",
                "visible" => $userObj->hasPermission('manage-send-push-notification'),
            ], [
                'title' => "DB Backup",
                "url" => "backup.php",
                "icon" => "fa fa-database",
                "active" => "Back-up",
                "visible" => $userObj->hasPermission('view-db-backup'),
            ],
            [
                "title" => "System Diagnostic",
                "url" => "system_diagnostic.php",
                "icon" => "fa fa-sitemap",
                "active" => "site",
            //"visible" => $userObj->hasPermission('view-site-statistics'),
            ],
        ],
    ], [
        'title' => "Logout",
        "url" => "logout.php",
        "icon" => ["class" => "icon-signin1", "url" => "images/logout-icon.png"],
    ]
];
return $menu;
