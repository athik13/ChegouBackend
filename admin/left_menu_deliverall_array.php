<?php

global $allservice_cat_data;

//Added By HJ On 08-07-2019 For Config Popular Store (service_provider.php) Screen As Per Discuss With KS Sir Start
$enableServiceProvicer = 0;
if (count($service_categories_ids_arr) == 1 && in_array(1, $service_categories_ids_arr)) {
    $enableServiceProvicer = 1;
}

//Added By HJ On 08-07-2019 For Config Popular Store (service_provider.php) Screen As Per Discuss With KS Sir End
//added by SP as discussed with bmam on 28-6-2019
$adminUsersTxt = $langage_lbl_admin['LBL_ADMIN'];
if ($PACKAGE_TYPE == 'SHARK' && ($APP_TYPE == 'Ride' || $APP_TYPE == 'Ride-Delivery-UberX') && ONLYDELIVERALL == 'No') {
    $adminUsersTxt = $langage_lbl_admin['LBL_ADMIN'] . '/Hotel';
}

//echo $PACKAGE_TYPE;die;
$menu = [
    ['title' => 'Dashboard',
        'url' => 'store-dashboard.php',
        "icon" => 'fa fa-tachometer',
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
            ],
        /* [
          'title'   => 'Admin Groups',
          'url'     => "admin_groups.php",
          "icon"    => "fa fa-certificate",
          "active"  => "AdminGroups",
          "visible" => $userObj->hasRole(1),
          ], [
          'title'   => 'Permissions',
          'url'     => "admin_permissions.php",
          "icon"    => "fa fa-certificate",
          "active"  => "AdminPermissions",
          "visible" => $userObj->hasRole(1),
          ], */
        ],
    ], [
        'title' => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'],
        "url" => "store.php",
        "icon" => "fa fa-building-o",
        "active" => "DeliverAllStore",
        "visible" => $userObj->hasPermission('view-store'),
    ], 
    [
        'title' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
        'url' => "driver.php",
        "icon" => ["class" => "icon-user1", "url" => "images/icon/driver-icon.png"],
        "active" => "Driver",
        "visible" => $userObj->hasPermission('view-providers'),
    ],[
        'title' => $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'],
        'url' => "rider.php",
        "icon" => ["class" => "icon-group1", "url" => "images/rider-icon.png"],
        "active" => "Rider",
        "visible" => $userObj->hasPermission('view-users'),
    ], [
        'title' => $langage_lbl_admin['LBL_PET_TYPE'],
        "icon" => ['class' => "icon-cogs1", 'url' => "images/reports-icon.png"],
        "visible" => ($userObj->hasPermission(['view-pet-type', 'view-users-pets']) && $APP_TYPE == 'UberX'),
        'children' => [
            [
                'title' => $langage_lbl_admin['LBL_PET_TYPE'],
                'url' => "pettype.php",
                "icon" => "icon-money",
                "active" => "PetType",
                "visible" => $userObj->hasPermission('view-pet-type'),
            ], [
                'title' => $langage_lbl_admin['LBL_USER_PETS_ADMIN'],
                'url' => "user_pets.php",
                "icon" => "fa fa-taxi",
                "active" => "userPets",
                "visible" => $userObj->hasPermission('view-users-pets'),
            ],
        ],
    ], [
        'title' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . ' Vehicles',
        "url" => "vehicles.php",
        "icon" => "fa fa-dot-circle-o",
        "active" => "Vehicle",
        "visible" => $userObj->hasPermission('view-provider-taxis'),
    ], [
        'title' => $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
        'url' => "store_vehicle_type.php",
        "icon" => ["class" => "icon-user1", "url" => "images/icon/vehicle-type-icon.png"],
        "active" => "StoreVehicleType",
        "visible" => $userObj->hasPermission('view-vehicle-type'),
    ], [
        'title' => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Items",
        "icon" => "icon-food",
        "visible" => $userObj->hasPermission('manage-store-items'),
        'children' => [
            [
                'title' => "Item Category",
                "url" => "food_menu.php",
                "icon" => "fa fa-cutlery",
                "active" => "FoodMenu",
                "visible" => $userObj->hasPermission('view-item-categories'),
            ], [
                'title' => "Items",
                "url" => "menu_item.php",
                "icon" => "fa fa-list-alt",
                "active" => "MenuItems",
                "visible" => $userObj->hasPermission('view-item'),
            ], [
                'title' => "Item Type",
                "url" => "cuisine.php",
                "icon" => "fa fa-spoon",
                "active" => "Cuisine",
                "visible" => $userObj->hasPermission('view-item-type'),
            ],
        ],
    ], [
        'title' => $langage_lbl_admin['LBL_VEHICLE_TXT_ADMIN'],
        'url' => "vehicles.php",
        "icon" => "fa fa-taxi",
        "active" => "Vehicle",
        "visible" => ($userObj->hasPermission('view-provider-taxis') && $APP_TYPE != 'Ride-Delivery-UberX'),
    ], [
        'title' => $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Orders",
        "icon" => "fa fa-first-order",
        "visible" => $userObj->hasPermission('manage-orders'),
        'children' => [
            [
                'title' => "Processing",
                "url" => "allorders.php?type=processing",
                "icon" => "fa fa-fire",
                "active" => "Processing Orders",
                "visible" => $userObj->hasPermission('view-processing-orders'),
            ], [
                'title' => "Cancelled",
                "url" => "cancelled_orders.php",
                "icon" => "fa fa-undo",
                "active" => "CancelledOrders",
                "visible" => $userObj->hasPermission('view-cancelled-orders'),
            ], [
                'title' => "All Orders",
                "url" => "allorders.php?type=allorders",
                "icon" => "fa fa-list",
                "active" => "All Orders",
                "visible" => $userObj->hasPermission('view-all-orders'),
            ],
        ],
    ], [
        'title' => $langage_lbl_admin['LBL_MANUAL_STORE_ORDER_TXT'],
        'url' => "../user-order-information?order=admin",
        "icon" => ["class" => "fa fa-taxi1", "url" => "images/shopping-cart.png"],
        "active" => "store_order_book",
        "target" => "blank",
        "visible" => ($userObj->hasPermission('manage-restaurant-order') && DELIVERALL == "Yes" && $MANUAL_STORE_ORDER_ADMIN_PANEL=="Yes"),
    ], [
        'title' => "Manage " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . " Categories",
        "url" => "store_category.php",
        "icon" => "fa fa-building-o",
        "active" => "ManageStoreCategories",
        "visible" => ($userObj->hasPermission('view-store-categories') && isStoreCategoriesEnable()),
    ], [
        'title' => $langage_lbl_admin['LBL_DELIVERY_PREF'],
        'url' => "delivery_preferences.php",
        "icon" => ["class" => "fa fa-exchange1", "url" => "images/delivery-pref.png"],
        "active" => "DeliveryPreferences",
        "visible" => ($userObj->hasPermission('view-delivery-preference') && isDeliveryPreferenceEnable()),
    ], [
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
        'title' => "Reviews",
        "url" => "store_review.php",
        "icon" => ["class" => "icon-comments1", "url" => "images/reviews-icon.png"],
        "active" => "Store Review",
        "visible" => $userObj->hasPermission('manage-reviews'),
    ],
    [
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
    ],
    [
        'title' => "Site Earning",
        "icon" => "icon-money",
        "visible" => $userObj->hasPermission('manage-site-earning'),
        'children' => [
            [
                'title' => "Admin Earning",
                "url" => "admin_payment_report.php",
                "icon" => "fa fa-fire",
                "active" => "Admin Payment_Report",
                "visible" => $userObj->hasPermission('manage-admin-earning'),
            ], [
                'title' => "Payout to " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'],
                "url" => "restaurants_pay_report.php",
                "icon" => "fa fa-undo",
                "active" => "Restaurant Payment Report",
                "visible" => $userObj->hasPermission('manage-store-payment'),
            ], [
                'title' => "Payout to " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                "url" => "store_driver_pay_report.php",
                "icon" => "fa fa-list",
                "active" => "Deliverall Driver Payment Report",
                "visible" => $userObj->hasPermission('manage-provider-payment'),
            ], [
                'title' => "Cancelled Order Report",
                "url" => "cancelled_report.php",
                "icon" => "fa fa-list",
                "active" => "Cancelled Order Report",
                "visible" => $userObj->hasPermission('manage-cancelled-order-report'),
            ],
        ],
    ],
    [
        'title' => "Reports",
        "icon" => ["class" => "icon-cogs1", "url" => "images/reports-icon.png"],
        "visible" => $userObj->hasPermission('manage-report'),
        'children' => [
            [
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
                'title' => $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Log Report",
                "url" => "driver_log_report.php",
                "icon" => "glyphicon glyphicon-list-alt",
                "active" => "Driver Log Report",
                "visible" => $userObj->hasPermission('manage-provider-log-report'),
            ], [
                'title' => "Insurance Report",
                "icon" => "fa fa-globe",
                "visible" => isInsuranceReportEnable() && $userObj->hasPermission('manage-payment-report'),
                'children' => [
                    [
                        'title' => "Idle Time",
                        "url" => "insurance_idle_report.php",
                        "icon" => "fa fa-taxi",
                        "active" => "Insurance_Idle_time_Report",
                        "visible" => $PACKAGE_TYPE == 'SHARK' && $APP_TYPE != 'UberX' && $ENABLE_INSURANCE_IDLE_REPORT == 'Yes',
                    ], [
                        'title' => "After " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Accept",
                        "url" => "insurance_accept_report.php",
                        "icon" => "fa fa-taxi",
                        "active" => "Insurance_accept_trip_Report",
                        "visible" => $PACKAGE_TYPE == 'SHARK' && $APP_TYPE != 'UberX' && $ENABLE_INSURANCE_ACCEPT_REPORT == 'Yes',
                    ], [
                        'title' => "After " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Start",
                        "url" => "insurance_trip_report.php",
                        "icon" => "fa fa-taxi",
                        "active" => "Insurance_start_trip_Report",
                        "visible" => $PACKAGE_TYPE == 'SHARK' && $APP_TYPE != 'UberX' && $ENABLE_INSURANCE_TRIP_REPORT == 'Yes',
                    ]
                ],
            ]
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
            ],
            [
                'title' => "Delivery Charges",
                "url" => "delivery_charges.php",
                "icon" => "fa fa-map-signs",
                "active" => "Delivery Charges",
                "visible" => $userObj->hasPermission('view-delivery-charges') && DELIVERALL == 'Yes',
            ],
            [
                'title' => "Locationwise Fare",
                "url" => "locationwise_fare.php",
                "icon" => "fa fa-map-signs",
                "active" => "locationwise_fare",
                "visible" => $userObj->hasPermission('view-location-wise-fare') && $APP_TYPE != 'UberX' && $APP_TYPE != "Delivery" && ONLYDELIVERALL == 'No',
            ],
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
                "visible" => $userObj->hasPermission('view-general-label'),
            ],
            [
                'title' => "Email Templates",
                "url" => "email_template.php",
                "icon" => "fa fa-envelope",
                "active" => "email_templates",
                "visible" => $userObj->hasPermission('view-email-templates'),
            ],
            [
                'title' => "SMS Templates",
                "url" => "sms_template.php",
                "icon" => "fa fa-comment",
                "active" => "sms_templates",
                "visible" => $userObj->hasPermission('view-sms-templates'),
            ],
            [
                'title' => "Manage Documents",
                "url" => "document_master_list.php",
                "icon" => "fa fa-file-text",
                "active" => "Document Master",
                "visible" => $userObj->hasPermission('view-documents'),
            ], [
                'title' => "Language Label",
                "icon" => "fa fa-language",
                "visible" => $userObj->hasPermission('manage-language-label') && (DELIVERALL == 'Yes' || ONLYDELIVERALL == 'Yes'),
                'children' => function () {
                    global $allservice_cat_data, $userObj;
                    $languages_childs = [
                        [
                            'title' => "General Label",
                            "url" => "languages.php",
                            "icon" => "fa fa-language",
                            "active" => "language_label",
                            "visible" => $userObj->hasPermission('view-general-label'),
                        ],
                    ];
                    if (count($allservice_cat_data) >= 1 && !empty($allservice_cat_data)) {
                        foreach ($allservice_cat_data as $key => $value) {
                            $languages_childs[] = [
                                'title' => $value['vServiceName'] . " Label",
                                'url' => "languages.php?selectedlanguage=" . $value['iServiceId'],
                                "icon" => 'fa fa-dot-circle-o',
                                "active" => "language_label_" . $value['iServiceId'],
                            ];
                        }
                    }
                    return $languages_childs;
                }
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
                "url" => "store_banner.php",
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
                        "visible" => $userObj->hasPermission('view-city') && ($SHOW_CITY_FIELD=='Yes'),
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
                "url" => "homecontent.php",
                "icon" => "fa fa-file-text-o",
                "active" => "homecontent",
                "visible" => $userObj->hasPermission('view-home-page-content') && ($APP_TYPE == 'Ride-Delivery-UberX') && $leftdeliverallxthemeon!=1,
            ], [
                'title' => "Edit Home Page",
                "url" => "home_content_deliverallx.php",
                "icon" => "fa fa-file-text-o",
                "active" => "homecontent",
                "visible" => $userObj->hasPermission('view-home-page-content') && $leftdeliverallxthemeon==1,
            ], [
                'title' => $langage_lbl_admin['LBL_CAR_MAKE_ADMIN'],
                "url" => "make.php",
                "icon" => "fa fa-car",
                "active" => "Make",
                "visible" => $userObj->hasPermission('view-vehicle-make') && ($APP_TYPE != 'UberX'),
            ], [
                'title' => $langage_lbl_admin['LBL_CAR_MODEL_ADMIN'],
                "url" => "model.php",
                "icon" => "fa fa-taxi",
                "active" => "Model",
                "visible" => $userObj->hasPermission('view-vehicle-model') && ($APP_TYPE != 'UberX'),
            ], [
                'title' => "Donation",
                "url" => "donation.php",
                "icon" => "fa fa-money",
                "active" => "Donation",
                "visible" => $userObj->hasPermission('view-donation') && ($DONATION == 'Yes' && $DONATION_ENABLE == 'Yes'),
            ], [
                'title' => "DeliveryAll Service Category",
                "url" => "service_category.php",
                "icon" => "fa fa-question",
                "active" => "service_category",
                "visible" => $userObj->hasPermission('view-service-category') && (count($allservice_cat_data) > 1),
            ], [
                'title' => "Order Status",
                "url" => "order_status.php",
                "icon" => "fa fa-first-order",
                "active" => "order_status",
                "visible" => $userObj->hasPermission('view-order-status'),
            ], /* [
              'title' => "Cancel Reason",
              "url" => "cancel_reason.php",
              "icon" => "fa fa-question",
              "active" => "cancel_reason",
              "visible" => $userObj->hasPermission('view-cancel-reasons'),
              ], */ [
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
            ], [
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
            ],
            [
                'title' => $langage_lbl_admin['LBL_PACKAGE_TYPE_ADMIN'],
                "url" => "package_type.php",
                "icon" => "fa fa-globe",
                "active" => "Package",
                "visible" => $userObj->hasPermission('view-package-type') && ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Delivery') && ONLYDELIVERALL != "Yes" && $leftdeliveryEnable == "Yes", // ONLYDELIVERALL != "Yes Added By HJ On 05-07-2019 As Per Discuss With KS Sir
            ],
            [
                'title' => "Popular " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'],
                "url" => "service_provider.php",
                "icon" => "fa fa-users",
                "active" => "service_provider",
                "visible" => $userObj->hasPermission('view-popular-stores') && $enableServiceProvicer == 1,
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
        'title' => "Manage Ride Profiles",
        "icon" => "fa fa-taxi",
        "visible" => $userObj->hasPermission('manage-ride-profiles') && isCorporateProfileEnable() && $leftrideEnable == "Yes",
        'children' => [
            [
                'title' => "Organization",
                "url" => "organization.php",
                "icon" => "fa fa-globe",
                "active" => "Organization",
                "visible" => $userObj->hasPermission('view-organization'),
            ], [
                'title' => "Ride Profile Type",
                "url" => "user_profile_master.php",
                "icon" => "fa fa-file-text-o",
                "active" => "RideProfileType",
                "visible" => $userObj->hasPermission('view-user-profile'),
            ], [
                'title' => "Business Trip Reason",
                "url" => "trip_reason.php",
                "icon" => "fa fa-book",
                "active" => "BusinessTripReason",
                "visible" => $userObj->hasPermission('view-trip-reason'),
            ], [
                'title' => "Organization Payment Report",
                "url" => "org_payment_report.php",
                "icon" => "fa fa-taxi",
                "active" => "OrganizationPaymentReport",
                "visible" => $userObj->hasPermission('manage-organization-payment-report'),
            ]
        ],
    ], [
        'title' => "Logout",
        "url" => "logout.php",
        "icon" => ["class" => "icon-signin1", "url" => "images/logout-icon.png"],
    ],
];
return $menu;
