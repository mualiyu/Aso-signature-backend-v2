<?php

return [
    'customers' => [
        'forgot-password' => [
            'already-sent'         => 'Already Password Reset Mail Sent.',
            'back'                 => 'Back to sign In ?',
            'bagisto'              => 'Bagisto',
            'email'                => 'Email',
            'email-not-exist'      => 'We cannot find a user with that email address.',
            'footer'               => '© Copyright :current_year, AsoSignature. All rights reserved.',
            'forgot-password-text' => 'If you forgot your password, recover it by entering your email address.',
            'page-title'           => 'Forgot your password ?',
            'reset-link-sent'      => 'We have e-mailed your reset password link.',
            'sign-in-button'       => 'Sign In',
            'submit'               => 'Reset Password',
            'title'                => 'Recover Password',
        ],

        'reset-password' => [
            'back-link-title'  => 'Back to Sign In',
            'bagisto'          => 'Bagisto',
            'confirm-password' => 'Confirm Password',
            'email'            => 'Registered Email',
            'footer'           => '© Copyright :current_year, AsoSignature. All rights reserved.',
            'password'         => 'Password',
            'submit-btn-title' => 'Reset Password',
            'success'          => 'Your password has been reset successfully. Please sign in with your new password.',
            'title'            => 'Reset Password',
        ],

        'login-form' => [
            'bagisto'             => 'Bagisto',
            'button-title'        => 'Sign In',
            'create-your-account' => 'Create your account',
            'email'               => 'Email',
            'footer'              => '© Copyright :current_year, AsoSignature. All rights reserved.',
            'forgot-pass'         => 'Forgot Password?',
            'form-login-text'     => 'If you have an account, sign in with your email address.',
            'invalid-credentials' => 'Please check your credentials and try again.',
            'new-customer'        => 'New customer?',
            'not-activated'       => 'Your activation seeks admin approval',
            'page-title'          => 'Customer Login',
            'password'            => 'Password',
            'show-password'       => 'Show Password',
            'title'               => 'Sign In',
            'verify-first'        => 'Verify your email account first.',
        ],

        'signup-form' => [
            'account-exists'              => 'Already have an account ?',
            'agree-to-terms'              => 'I have read and agree to the',
            'bagisto'                     => 'Bagisto',
            'button-title'                => 'Register',
            'confirm-pass'                => 'Confirm Password',
            'email'                       => 'Email',
            'first-name'                  => 'First Name',
            'footer'                      => '© Copyright :current_year, AsoSignature. All rights reserved.',
            'form-signup-text'            => 'If you are new to our store, we glad to have you as member.',
            'last-name'                   => 'Last Name',
            'page-title'                  => 'Become User',
            'password'                    => 'Password',
            'sign-in-button'              => 'Sign In',
            'subscribe-to-newsletter'     => 'Subscribe to newsletter',
            'success'                     => 'Account created successfully.',
            'success-verify'              => 'Account created successfully, an e-mail has been sent for verification.',
            'success-verify-email-unsent' => 'Account created successfully, but verification e-mail unsent.',
            'terms-conditions'            => 'Terms & Conditions',
            'terms-required'              => 'You must agree to the Terms & Conditions to create an account.',
            'verification-not-sent'       => 'Error! Problem in sending verification email, please try again later.',
            'verification-sent'           => 'Verification email sent',
            'verified'                    => 'Your account has been verified, try to login now.',
            'verify-failed'               => 'We cannot verify your mail account.',
        ],

        'account' => [
            'home' => 'Home',

            'profile' => [
                'index' => [
                    'delete'         => 'Delete',
                    'delete-failed'  => 'Error encountered while deleting customer.',
                    'delete-profile' => 'Delete Profile',
                    'delete-success' => 'Customer deleted successfully',
                    'dob'            => 'Date of Birth',
                    'edit'           => 'Edit',
                    'edit-success'   => 'Profile Updated Successfully',
                    'email'          => 'Email',
                    'enter-password' => 'Enter Your password',
                    'first-name'     => 'First Name',
                    'gender'         => 'Gender',
                    'last-name'      => 'Last Name',
                    'order-pending'  => 'Cannot delete customer account because some Order(s) are pending or processing state.',
                    'title'          => 'Profile',
                    'unmatched'      => 'The old password does not match.',
                    'wrong-password' => 'Wrong Password !',
                ],

                'edit' => [
                    'confirm-password'        => 'Confirm Password',
                    'current-password'        => 'Current Password',
                    'dob'                     => 'Date of Birth',
                    'edit'                    => 'Edit',
                    'edit-profile'            => 'Edit Profile',
                    'email'                   => 'Email',
                    'female'                  => 'Female',
                    'first-name'              => 'First Name',
                    'gender'                  => 'Gender',
                    'last-name'               => 'Last Name',
                    'male'                    => 'Male',
                    'new-password'            => 'New Password',
                    'other'                   => 'Other',
                    'phone'                   => 'Phone',
                    'save'                    => 'Save',
                    'subscribe-to-newsletter' => 'Subscribe to newsletter',
                ],
            ],

            'addresses' => [
                'index' => [
                    'add-address'      => 'Add Address',
                    'create-success'   => 'Address have been successfully added.',
                    'default-address'  => 'Default Address',
                    'default-delete'   => 'Default address cannot be changed.',
                    'delete'           => 'Delete',
                    'delete-success'   => 'Address successfully deleted',
                    'edit'             => 'Edit',
                    'edit-success'     => 'Address updated successfully.',
                    'empty-address'    => 'You have not added an address to your account yet.',
                    'security-warning' => 'Suspicious activity found!!!',
                    'set-as-default'   => 'Set as Default',
                    'title'            => 'Address',
                    'update-success'   => 'Address have been updated successfully.',
                ],

                'create' => [
                    'add-address'    => 'Add Address',
                    'city'           => 'City',
                    'company-name'   => 'Company Name',
                    'country'        => 'Country',
                    'email'          => 'Email',
                    'first-name'     => 'First Name',
                    'last-name'      => 'Last Name',
                    'phone'          => 'Phone',
                    'post-code'      => 'Post Code',
                    'save'           => 'Save',
                    'select-country' => 'Select Country',
                    'set-as-default' => 'Set as Default',
                    'state'          => 'State',
                    'street-address' => 'Street Address',
                    'title'          => 'Address',
                    'vat-id'         => 'Vat ID',
                ],

                'edit' => [
                    'city'           => 'City',
                    'company-name'   => 'Company Name',
                    'country'        => 'Country',
                    'edit'           => 'Edit',
                    'email'          => 'Email',
                    'first-name'     => 'First Name',
                    'last-name'      => 'Last Name',
                    'phone'          => 'Phone',
                    'post-code'      => 'Post Code',
                    'select-country' => 'Select Country',
                    'state'          => 'State',
                    'street-address' => 'Street Address',
                    'title'          => 'Address',
                    'update-btn'     => 'Update',
                    'vat-id'         => 'Vat ID',
                ],
            ],

            'orders' => [
                'action'      => 'Action',
                'action-view' => 'View',
                'empty-order' => 'You have not ordered any product yet',
                'order'       => 'Order',
                'order-date'  => 'Order Date',
                'order-id'    => 'Order ID',
                'subtotal'    => 'Subtotal',
                'title'       => 'Orders',
                'total'       => 'Total',

                'status' => [
                    'title' => 'Status',

                    'options' => [
                        'canceled'            => 'Canceled',
                        'closed'              => 'Closed',
                        'completed'           => 'Delivered / Completed',
                        'fraud'               => 'Fraud',
                        'pending'             => 'Pending',
                        'pending-payment'     => 'Pending Payment',
                        'processing'          => 'Processing',
                        'ready-for-shipment'  => 'Ready for Shipment',
                        'sent-for-production' => 'Sent for Production',
                        'shipped'             => 'Shipped',
                    ],
                ],

                'view' => [
                    'billing-address'      => 'Billing Address',
                    'cancel-btn-title'     => 'Cancel',
                    'cancel-confirm-msg'   => 'Are you sure you want to cancel this order ?',
                    'cancel-error'         => 'Your order can not be canceled.',
                    'cancel-success'       => 'Your order has been canceled',
                    'contact'              => 'Contact',
                    'item-invoiced'        => 'Item Invoiced',
                    'item-refunded'        => 'Item Refunded',
                    'item-shipped'         => 'Item Shipped',
                    'item-ordered'         => 'Item Ordered',
                    'order-id'             => 'Order Id',
                    'page-title'           => 'Order #:order_id',
                    'payment-method'       => 'Payment Method',
                    'reorder-btn-title'    => 'Reorder',
                    'shipping-address'     => 'Shipping Address',
                    'shipping-method'      => 'Shipping Method',
                    'shipping-and-payment' => 'Shipping & Payment Details',
                    'status'               => 'Status',
                    'title'                => 'View',
                    'total'                => 'Total',

                    'information' => [
                        'discount'                   => 'Discount',
                        'excl-tax'                   => 'Excl Tax:',
                        'grand-total'                => 'Grand Total',
                        'info'                       => 'Information',
                        'item-canceled'              => 'Canceled (:qty_canceled)',
                        'item-refunded'              => 'Refunded (:qty_refunded)',
                        'invoiced-item'              => 'Invoiced (:qty_invoiced)',
                        'item-shipped'               => 'shipped (:qty_shipped)',
                        'item-status'                => 'Item Status',
                        'ordered-item'               => 'Ordered (:qty_ordered)',
                        'placed-on'                  => 'Placed On',
                        'price'                      => 'Price',
                        'product-name'               => 'Name',
                        'shipping-handling'          => 'Shipping & Handling',
                        'shipping-handling-excl-tax' => 'Shipping & Handling (Excl. Tax)',
                        'shipping-handling-incl-tax' => 'Shipping & Handling (Incl. Tax)',
                        'sku'                        => 'SKU',
                        'subtotal'                   => 'Subtotal',
                        'subtotal-excl-tax'          => 'Subtotal (Excl. Tax)',
                        'subtotal-incl-tax'          => 'Subtotal (Incl. Tax)',
                        'order-summary'              => 'Order Summary',
                        'tax'                        => 'Tax',
                        'tax-amount'                 => 'Tax Amount',
                        'tax-percent'                => 'Tax Percent',
                        'total-due'                  => 'Total Due',
                        'total-paid'                 => 'Total Paid',
                        'total-refunded'             => 'Total Refunded',
                    ],

                    'invoices' => [
                        'discount'                   => 'Discount',
                        'excl-tax'                   => 'Excl Tax:',
                        'grand-total'                => 'Grand Total',
                        'individual-invoice'         => 'Invoice #:invoice_id',
                        'invoices'                   => 'Invoices',
                        'price'                      => 'Price',
                        'print'                      => 'Print',
                        'product-name'               => 'Name',
                        'products-ordered'           => 'Products Ordered',
                        'qty'                        => 'Qty',
                        'shipping-handling-excl-tax' => 'Shipping & Handling (Excl. Tax)',
                        'shipping-handling-incl-tax' => 'Shipping & Handling (Incl. Tax)',
                        'shipping-handling'          => 'Shipping & Handling',
                        'sku'                        => 'SKU',
                        'subtotal-excl-tax'          => 'Subtotal (Excl. Tax)',
                        'subtotal-incl-tax'          => 'Subtotal (Incl. Tax)',
                        'subtotal'                   => 'Subtotal',
                        'tax'                        => 'Tax',
                        'tax-amount'                 => 'Tax Amount',
                    ],

                    'shipments' => [
                        'individual-shipment'   => 'Shipment #:shipment_id',
                        'product-name'          => 'Name',
                        'qty'                   => 'Qty',
                        'shipments'             => 'Shipments',
                        'sku'                   => 'SKU',
                        'subtotal'              => 'Subtotal',
                        'tracking-number'       => 'Tracking Number',
                        'track-online'          => 'Track shipment online',
                        'dhl-download'          => 'Download DHL paperwork (PDF)',
                        'dhl-status'            => 'Shipping status',
                        'shipping-updates'      => 'Shipping updates',
                        'dhl-documents-pending' => 'DHL paperwork is being prepared. Refresh this page shortly or contact support if it does not appear.',
                    ],

                    'refunds' => [
                        'adjustment-fee'             => 'Adjustment Fee',
                        'adjustment-refund'          => 'Adjustment Refund',
                        'discount'                   => 'Discount',
                        'grand-total'                => 'Grand Total',
                        'individual-refund'          => 'Refund #:refund_id',
                        'no-result-found'            => 'We could not find any records.',
                        'order-summary'              => 'Order Summary',
                        'price'                      => 'Price',
                        'product-name'               => 'Name',
                        'qty'                        => 'Qty',
                        'refunds'                    => 'Refunds',
                        'shipping-handling'          => 'Shipping & Handling',
                        'shipping-handling-excl-tax' => 'Shipping & Handling (Excl. Tax)',
                        'shipping-handling-incl-tax' => 'Shipping & Handling (Incl. Tax)',
                        'sku'                        => 'SKU',
                        'subtotal'                   => 'Subtotal',
                        'subtotal-excl-tax'          => 'Subtotal (Excl. Tax)',
                        'subtotal-incl-tax'          => 'Subtotal (Incl. Tax)',
                        'tax'                        => 'Tax',
                        'tax-amount'                 => 'Tax Amount',
                    ],
                ],

                'invoice-pdf' => [
                    'bank-details'               => 'Bank Details',
                    'bill-to'                    => 'Bill to',
                    'contact-number'             => 'Contact Number',
                    'contact'                    => 'Contact',
                    'date'                       => 'Invoice Date',
                    'discount'                   => 'Discount',
                    'excl-tax'                   => 'Excl. Tax:',
                    'grand-total'                => 'Grand Total',
                    'invoice-id'                 => 'Invoice ID',
                    'invoice'                    => 'Invoice',
                    'order-date'                 => 'Order Date',
                    'order-id'                   => 'Order ID',
                    'payment-method'             => 'Payment Method',
                    'payment-terms'              => 'Payment Terms',
                    'price'                      => 'Price',
                    'product-name'               => 'Product Name',
                    'qty'                        => 'Quantity',
                    'ship-to'                    => 'Ship to',
                    'shipping-handling-excl-tax' => 'Shipping Handling (Excl. Tax)',
                    'shipping-handling-incl-tax' => 'Shipping Handling (Incl. Tax)',
                    'shipping-handling'          => 'Shipping Handling',
                    'shipping-method'            => 'Shipping Method',
                    'sku'                        => 'SKU',
                    'subtotal-excl-tax'          => 'Subtotal (Excl. Tax)',
                    'subtotal-incl-tax'          => 'Subtotal (Incl. Tax)',
                    'subtotal'                   => 'Subtotal',
                    'tax-amount'                 => 'Tax Amount',
                    'tax'                        => 'Tax',
                    'vat-number'                 => 'Vat Number',
                ],
            ],

            'reviews' => [
                'empty-review' => 'You have not reviewed any product yet',
                'title'        => 'Reviews',
            ],

            'downloadable-products' => [
                'available'           => 'Available',
                'completed'           => 'Completed',
                'date'                => 'Date',
                'download-error'      => 'Download link has been expired.',
                'expired'             => 'Expired',
                'empty-product'       => 'You don’t have a product to download',
                'name'                => 'Downloadable Products',
                'orderId'             => 'Order Id',
                'pending'             => 'Pending',
                'payment-error'       => 'Payment has not been done for this download.',
                'records-found'       => 'Record(s) found',
                'remaining-downloads' => 'Remaining Downloads',
                'status'              => 'Status',
                'title'               => 'Title',
            ],

            'wishlist' => [
                'color'              => 'Color',
                'delete-all'         => 'Delete All',
                'empty'              => 'No products were added to the wishlist page.',
                'move-to-cart'       => 'Move To Cart',
                'moved'              => 'Item successfully moved To cart',
                'moved-success'      => 'Item Successfully Moved to Cart',
                'page-title'         => 'Wishlist',
                'product-removed'    => 'Product Is No More Available As Removed By Admin',
                'profile'            => 'Profile',
                'remove'             => 'Remove',
                'remove-all-success' => 'All the items from your wishlist have been removed',
                'remove-fail'        => 'Item Cannot Be Removed From Wishlist',
                'removed'            => 'Item Successfully Removed From Wishlist',
                'see-details'        => 'See Details',
                'success'            => 'Item Successfully Added To Wishlist',
                'title'              => 'Wishlist',
            ],
        ],
    ],

    'components' => [
        'accordion' => [
            'default-content' => 'Default Content',
            'default-header'  => 'Default Header',
        ],

        'drawer' => [
            'default-toggle' => 'Default Toggle',
        ],

        'media' => [
            'index' => [
                'add-attachments' => 'Add attachments',
                'add-image'       => 'Add Image/Video',
            ],
        ],

        'layouts' => [
            'header' => [
                'account'           => 'Account',
                'bagisto'           => 'Bagisto',
                'cart'              => 'Cart',
                'compare'           => 'Compare',
                'dropdown-text'     => 'Manage Cart, Orders & Wishlist',
                'logout'            => 'Logout',
                'no-category-found' => 'No category found.',
                'orders'            => 'Orders',
                'profile'           => 'Profile',
                'search'            => 'Search',
                'search-text'       => 'Search products here',
                'sign-in'           => 'Sign In',
                'sign-up'           => 'Sign Up',
                'submit'            => 'Submit',
                'title'             => 'Account',
                'welcome'           => 'Welcome',
                'welcome-guest'     => 'Welcome Guest',
                'wishlist'          => 'Wishlist',

                'desktop' => [
                    'top' => [
                        'default-locale' => 'Default locale',
                    ],
                ],

                'mobile' => [
                    'currencies' => 'Currencies',
                    'locales'    => 'Locales',
                    'login'      => 'Sign up or Login',
                ],
            ],

            'footer' => [
                'about-us'               => 'About Us',
                'contact-us'             => 'Contact Us',
                'currency'               => 'Currency',
                'customer-service'       => 'Customer Service',
                'email'                  => 'Email',
                'footer-content'         => 'Footer Content',
                'footer-text'            => '© Copyright :current_year, AsoSignature. All rights reserved.',
                'locale'                 => 'Locale',
                'newsletter-text'        => 'Get Ready for our Fun Newsletter!',
                'order-return'           => 'Order and Returns',
                'payment-policy'         => 'Payment Policy',
                'privacy-cookies-policy' => 'Privacy and Cookies Policy',
                'shipping-policy'        => 'Shipping Policy',
                'subscribe'              => 'Subscribe',
                'subscribe-newsletter'   => 'Subscribe Newsletter',
                'subscribe-stay-touch'   => 'Subscribe to stay in touch.',
                'whats-new'              => 'What’s New',
            ],
        ],

        'datagrid' => [
            'toolbar' => [
                'length-of' => ':length of',
                'results'   => ':total Results',
                'selected'  => ':total Selected',

                'mass-actions' => [
                    'must-select-a-mass-action'        => 'You must select a mass action.',
                    'must-select-a-mass-action-option' => 'You must select a mass action\'s option.',
                    'no-records-selected'              => 'No records have been selected.',
                    'select-action'                    => 'Select Action',
                ],

                'search' => [
                    'title' => 'Search',
                ],

                'filter' => [
                    'apply-filter' => 'Apply Filters',
                    'title'        => 'Filter',

                    'dropdown' => [
                        'select' => 'Select',

                        'searchable' => [
                            'at-least-two-chars' => 'Type at-least 2 characters...',
                            'no-results'         => 'No result found...',
                        ],
                    ],

                    'custom-filters' => [
                        'clear-all' => 'Clear All',
                    ],
                ],
            ],

            'table' => [
                'actions'              => 'Actions',
                'next-page'            => 'Next Page',
                'no-records-available' => 'No Records Available.',
                'of'                   => 'of :total entries',
                'page-navigation'      => 'Page Navigation',
                'page-number'          => 'Page Number',
                'previous-page'        => 'Previous Page',
                'showing'              => 'Showing :firstItem',
                'to'                   => 'to :lastItem',
            ],
        ],

        'modal' => [
            'default-content' => 'Default Content',
            'default-header'  => 'Default Header',

            'confirm' => [
                'agree-btn'    => 'Agree',
                'disagree-btn' => 'Disagree',
                'message'      => 'Are you sure you want to perform this action?',
                'title'        => 'Are you sure?',
            ],
        ],

        'products' => [
            'card' => [
                'add-to-cart'            => 'Add To Cart',
                'add-to-compare'         => 'Add To Compare',
                'add-to-compare-success' => 'Item added successfully to compare list.',
                'add-to-wishlist'        => 'Add To Wishlist',
                'already-in-compare'     => 'Item is already added to compare list.',
                'new'                    => 'New',
                'review-description'     => 'Be the first to review this product',
                'sale'                   => 'Sale',
            ],

            'carousel' => [
                'next'     => 'Next',
                'previous' => 'Previous',
                'view-all' => 'View All',
            ],

            'ratings' => [
                'title' => 'Ratings',
            ],
        ],

        'range-slider' => [
            'max-range' => 'Max Range',
            'min-range' => 'Min Range',
            'range'     => 'Range:',
        ],

        'carousel' => [
            'image-slide' => 'Image Slide',
            'next'        => 'Next',
            'previous'    => 'Previous',
        ],

        'quantity-changer' => [
            'decrease-quantity' => 'Decrease Quantity',
            'increase-quantity' => 'Increase Quantity',
        ],
    ],

    'products' => [
        'prices' => [
            'grouped' => [
                'starting-at' => 'Starting at',
            ],

            'configurable' => [
                'as-low-as' => 'As low as',
            ],
        ],

        'sort-by' => [
            'title' => 'Sort By',
        ],

        'view' => [
            'type' => [
                'configurable' => [
                    'select-options'       => 'Please select an option',
                    'select-above-options' => 'Please select above options',
                ],

                'bundle' => [
                    'none'         => 'None',
                    'total-amount' => 'Total Amount',
                ],

                'downloadable' => [
                    'links'   => 'Links',
                    'sample'  => 'Sample',
                    'samples' => 'Samples',
                ],

                'grouped' => [
                    'name' => 'Name',
                ],
            ],

            'gallery' => [
                'product-image'   => 'Product Image',
                'thumbnail-image' => 'Thumbnail Image',
            ],

            'reviews' => [
                'attachments'      => 'Attachments',
                'cancel'           => 'Cancel',
                'comment'          => 'Comment',
                'customer-review'  => 'Customer Reviews',
                'empty-review'     => 'No Review found, Be the first to review this product',
                'failed-to-upload' => 'The image failed to upload',
                'load-more'        => 'Load More',
                'name'             => 'Name',
                'rating'           => 'Rating',
                'ratings'          => 'Ratings',
                'submit-review'    => 'Submit Review',
                'success'          => 'Review submitted successfully.',
                'title'            => 'Title',
                'translate'        => 'Translate',
                'translating'      => 'Translating...',
                'write-a-review'   => 'Write a Review',
            ],

            'add-to-cart'            => 'Add To Cart',
            'add-to-compare'         => 'Product added in compare.',
            'add-to-wishlist'        => 'Add To Wishlist',
            'additional-information' => 'Additional Information',
            'already-in-compare'     => 'Product is already added in compare.',
            'buy-now'                => 'Buy Now',
            'compare'                => 'Compare',
            'description'            => 'Description',
            'designer'               => 'Designer',
            'production-timeline'    => 'Production Timeline',
            'production-timeline-disclaimer' => 'Timeline varies by product.',
            'related-product-title'  => 'Related Products',
            'review'                 => 'Reviews',
            'tax-inclusive'          => 'Inclusive of all taxes',
            'up-sell-title'          => 'We found other products you might like!',
        ],

        'type' => [
            'abstract' => [
                'offers' => 'Buy :qty for :price each and save :discount',
            ],
        ],
    ],

    'categories' => [
        'filters' => [
            'clear-all' => 'Clear All',
            'filters'   => 'Filters:',
            'filter'    => 'Filter',
            'sort'      => 'Sort',
        ],

        'toolbar' => [
            'grid' => 'Grid',
            'list' => 'List',
            'show' => 'Show',
        ],

        'view' => [
            'empty'     => 'No products available in this category',
            'load-more' => 'Load More',
        ],
    ],

    'search' => [
        'title'   => 'Search results for : :query',
        'results' => 'Search results',

        'images' => [
            'index' => [
                'only-images-allowed'  => 'Only images (.jpeg, .jpg, .png, ..) are allowed.',
                'search'               => 'Search',
                'size-limit-error'     => 'Size Limit Error',
                'something-went-wrong' => 'Something went wrong, please try again later.',
            ],

            'results' => [
                'analyzed-keywords' => 'Analyzed Keywords:',
            ],
        ],
    ],

    'compare' => [
        'already-added'      => 'Item is already added to compare list',
        'delete-all'         => 'Delete All',
        'empty-text'         => 'You have no items in your compare list',
        'item-add-success'   => 'Item added successfully to compare list',
        'product-compare'    => 'Product Compare',
        'remove-all-success' => 'All items removed successfully.',
        'remove-error'       => 'Something went wrong, please try again later.',
        'remove-success'     => 'Item removed successfully.',
        'title'              => 'Product Compare',
    ],

    'checkout' => [
        'success' => [
            'info'          => 'We will email you, your order details and tracking information',
            'order-id-info' => 'Your order id is #:order_id',
            'thanks'        => 'Thank you for your order!',
            'title'         => 'Order successfully placed',
        ],

        'cart' => [
            'continue-to-checkout'      => 'Continue to Checkout',
            'illegal'                   => 'Quantity cannot be lesser than one.',
            'inactive-add'              => 'Inactive item cannot be added to cart.',
            'inactive'                  => 'The item has been deactivated and subsequently removed from the cart.',
            'inventory-warning'         => 'The requested quantity is not available, please try again later.',
            'item-add-to-cart'          => 'Item Added Successfully',
            'minimum-order-message'     => 'Minimum order amount is',
            'missing-fields'            => 'Some required fields missing for this product.',
            'missing-options'           => 'Options are missing for this product.',
            'paypal-payment-cancelled'  => 'Paypal payment has been cancelled.',
            'qty-missing'               => 'At least one product should have more than 1 quantity.',
            'return-to-shop'            => 'Return To Shop',
            'rule-applied'              => 'Cart rule applied',
            'select-hourly-duration'    => 'Select a slot duration of one hour.',
            'success-remove'            => 'Item is successfully removed from the cart.',
            'suspended-account-message' => 'Your account has been suspended.',

            'index' => [
                'bagisto'                  => 'Bagisto',
                'cart'                     => 'Cart',
                'continue-shopping'        => 'Continue Shopping',
                'empty-product'            => 'You don’t have a product in your cart.',
                'excl-tax'                 => 'Excl. Tax:',
                'home'                     => 'Home',
                'items-selected'           => ':count Items Selected',
                'move-to-wishlist-success' => 'Selected items successfully moved to wishlist.',
                'move-to-wishlist'         => 'Move To Wishlist',
                'price'                    => 'Price',
                'product-name'             => 'Product Name',
                'quantity-update'          => 'Quantity updated successfully',
                'quantity'                 => 'Quantity',
                'remove-selected-success'  => 'Selected items successfully removed from cart.',
                'remove'                   => 'Remove',
                'see-details'              => 'See Details',
                'select-all'               => 'Select All',
                'select-cart-item'         => 'Select Cart Item',
                'tax'                      => 'Tax',
                'total'                    => 'Total',
                'update-cart'              => 'Update Cart',
                'view-cart'                => 'View Cart',

                'cross-sell' => [
                    'title' => 'More choices',
                ],
            ],

            'mini-cart' => [
                'continue-to-checkout' => 'Continue to Checkout',
                'empty-cart'           => 'Your cart is empty',
                'excl-tax'             => 'Excl. Tax:',
                'offer-on-orders'      => 'Get Up To 30% OFF on your 1st order',
                'remove'               => 'Remove',
                'see-details'          => 'See Details',
                'shopping-cart'        => 'Shopping Cart',
                'subtotal'             => 'Subtotal',
                'view-cart'            => 'View Cart',
            ],

            'summary' => [
                'cart-summary'              => 'Cart Summary',
                'delivery-charges-excl-tax' => 'Delivery Charges (Excl. Tax)',
                'delivery-charges-incl-tax' => 'Delivery Charges (Incl. Tax)',
                'delivery-charges'          => 'Delivery Charges',
                'discount-amount'           => 'Discount Amount',
                'grand-total'               => 'Grand Total',
                'place-order'               => 'Place Order',
                'proceed-to-checkout'       => 'Proceed To Checkout',
                'sub-total-excl-tax'        => 'Subtotal (Excl. Tax)',
                'sub-total-incl-tax'        => 'Subtotal (Incl. Tax)',
                'sub-total'                 => 'Subtotal',
                'tax'                       => 'Tax',

                'estimate-shipping' => [
                    'country'        => 'Country',
                    'info'           => 'Enter your destination to get a shipping and tax estimate.',
                    'postcode'       => 'Zip/Postcode',
                    'select-country' => 'Select Country',
                    'select-state'   => 'Select State',
                    'state'          => 'State',
                    'title'          => 'Estimate Shipping and Tax',
                ],
            ],
        ],

        'onepage' => [
            'address' => [
                'add-new'                => 'Add new address',
                'add-new-address'        => 'Add new address',
                'back'                   => 'Back',
                'billing-address'        => 'Billing Address',
                'check-billing-address'  => 'Billing address is missing.',
                'check-shipping-address' => 'Shipping address is missing.',
                'city'                   => 'City',
                'company-name'           => 'Company Name',
                'confirm'                => 'Confirm',
                'country'                => 'Country',
                'email'                  => 'Email',
                'first-name'             => 'First Name',
                'last-name'              => 'Last Name',
                'postcode'               => 'Zip/Postcode',
                'proceed'                => 'Proceed',
                'same-as-billing'        => 'Use same address for shipping?',
                'save'                   => 'Save',
                'save-address'           => 'Save this to address book',
                'select-country'         => 'Select Country',
                'select-state'           => 'Select State',
                'shipping-address'       => 'Shipping Address',
                'state'                  => 'State',
                'street-address'         => 'Street Address',
                'telephone'              => 'Telephone',
                'title'                  => 'Address',
            ],

            'index' => [
                'checkout' => 'Checkout',
                'home'     => 'Home',
            ],

            'payment' => [
                'payment-method' => 'Payment Method',
            ],

            'shipping' => [
                'shipping-method' => 'Shipping Method',
            ],

            'summary' => [
                'cart-summary'              => 'Cart Summary',
                'delivery-charges-excl-tax' => 'Delivery Charges (Excl. Tax)',
                'delivery-charges-incl-tax' => 'Delivery Charges (Incl. Tax)',
                'delivery-charges'          => 'Delivery Charges',
                'discount-amount'           => 'Discount Amount',
                'excl-tax'                  => 'Excl. Tax:',
                'grand-total'               => 'Grand Total',
                'place-order'               => 'Place Order',
                'price_&_qty'               => ':price × :qty',
                'processing'                => 'Processing',
                'sub-total-excl-tax'        => 'Subtotal (Excl. Tax)',
                'sub-total-incl-tax'        => 'Subtotal (Incl. Tax)',
                'sub-total'                 => 'Subtotal',
                'tax'                       => 'Tax',
            ],
        ],

        'coupon' => [
            'already-applied' => 'Coupon code already applied.',
            'applied'         => 'Coupon applied',
            'apply'           => 'Apply Coupon',
            'apply-issue'     => 'Coupon code can\'t be applied.',
            'button-title'    => 'Apply',
            'code'            => 'Coupon code',
            'discount'        => 'Coupon Discount',
            'enter-your-code' => 'Enter your code',
            'error'           => 'Something went wrong',
            'invalid'         => 'Coupon code is invalid.',
            'remove'          => 'Remove Coupon',
            'subtotal'        => 'Subtotal',
            'success-apply'   => 'Coupon code applied successfully.',
        ],

        'login' => [
            'email'    => 'Email',
            'password' => 'Password',
            'title'    => 'Sign In',
        ],
    ],

    'home' => [
        'contact' => [
            'about'         => 'Jot us a note, and we’ll get back to you as quickly as possible',
            'desc'          => ' What’s on your mind?',
            'describe-here' => 'Describe Here',
            'email'         => 'Email',
            'message'       => 'Message',
            'name'          => 'Name',
            'phone-number'  => 'Phone Number',
            'submit'        => 'Submit',
            'title'         => 'Contact Us',
        ],

        'how-it-works' => [
            'title'   => 'How It Works',
            'eyebrow' => 'How it works',
            'heading' => 'From your measurements to your doorstep, made with care at every stage',
            'intro'   => '',
            // 'intro'   => 'Seven steps between choosing a design and wearing it. Here is exactly what happens at each one.',
            'scroll'  => 'See the steps',
            'step'    => 'Step :number',

            'steps' => [
                'choose-design' => [
                    'title' => 'Choose your design',
                    'desc'  => 'Browse our catalogue and pick the piece you want made — from fabric to finish, the design starts with you.',
                ],

                'submit-measurements' => [
                    'title' => 'Submit your measurements',
                    'desc'  => 'Follow our step-by-step guide to take your own measurements at home, no tailor visit required.',
                ],

                'review' => [
                    'title' => 'Aso confirms your measurements',
                    'desc'  => 'Our team checks every figure against your design for fit and flags anything that looks off before cutting begins.',
                ],

                'production' => [
                    'title' => 'Designer produces your outfit',
                    'desc'  => 'Once measurements are confirmed, our designers cut and construct your piece by hand to those exact figures.',
                ],

                'quality-check' => [
                    'title' => 'Aso quality-checks',
                    'desc'  => 'Before anything ships, we inspect the stitching, finish, and fit against your original specification.',
                ],

                'delivery' => [
                    'title' => 'We deliver to your door',
                    'desc'  => 'Your finished piece is packaged and shipped wherever you are, tracked from our workshop to your doorstep.',
                ],

                'support' => [
                    'title' => 'We Support you',
                    'desc'  => 'If something doesn\'t sit right, our team works with you to adjust or remake it until it does.',
                ],
            ],

            'cta' => [
                'title'     => 'Ready when you are',
                'desc'      => '',
                'primary'   => 'Start your design',
                'secondary' => 'Measurement guide',
            ],
        ],

        'how-to-measure' => [
            'title'          => 'How to Measure',
            'heading'        => 'How to',
            'heading-accent' => 'Measure',

            'prerequisites' => [
                'title' => 'Before you start — three things on the table',

                'tape' => [
                    'title' => 'A cloth tape',
                    'desc'  => 'Flexible, marked in inches and centimetres. A phone charging cable is not a substitute.',
                ],

                'layer' => [
                    'title' => 'A fitted layer',
                    'desc'  => 'Measure over a snug t-shirt and slim trousers — not baggy, not bulky.',
                ],

                'helper' => [
                    'title' => 'A second pair of hands',
                    'desc'  => 'A friend gets you a straighter back and cleaner shoulder. Alone works too — stand by a mirror.',
                ],
            ],

            'guide' => [
                'title'              => 'The eight measurements',
                'showing'            => 'Showing',
                'of'                 => 'of',
                'search-placeholder' => 'Search a measurement — neck, waist, sleeve…',
                'no-results'         => 'No measurements match that. Try "neck", "waist", or "sleeve".',
                'tip'                => 'Tip:',
                'start'              => 'Start',
                'done'               => 'Done',
            ],

            'videos' => [
                'title'      => 'Watch it done',
                'intro'      => 'Two short walkthroughs — pick the one that matches you and measure along.',
                'play'       => 'Play video',
                'unsupported' => 'Your browser does not support the video tag.',

                'male' => [
                    'label' => 'Men',
                    'title' => 'Men\'s measurement guide',
                    'desc'  => 'Neck, shoulder, chest, waist, sleeve and trouser lengths, taken step by step.',
                ],

                'female' => [
                    'label' => 'Women',
                    'title' => 'Women\'s measurement guide',
                    'desc'  => 'Bust, waist, hip and length measurements for dresses, tops and skirts.',
                ],
            ],

            'link' => [
                'title'      => 'Measurement Guide',
                'subtitle'   => 'Learn how to measure yourself',
                'eyebrow'    => 'How to Measure',
                'full-guide' => 'Read the full measurement guide',
            ],

            'modal' => [
                'title'          => 'How to Measure',
                'view-full-page' => 'View full page',
                'close'          => 'Close',
                'error'          => 'We couldn\'t load the guide right now.',
                'error-link'     => 'Open the full page instead',
            ],

            'measurements' => [
                'neck' => [
                    'label' => 'Neck',
                    'title' => 'Around the base of the neck',
                    'how'   => 'Circle the tape where a collar would sit — low, not up under the chin. Slide one finger between tape and skin. That finger is the ease we cut in for you.',
                    'tip'   => 'If the reading feels tight, take it again. A neck that pinches is a shirt returned.',
                ],

                'shoulder' => [
                    'label' => 'Shoulder',
                    'title' => 'Bone to bone, across the back',
                    'how'   => 'Stand relaxed, arms at your sides. From the bony point at the top of one shoulder, straight across the back to the same point on the other. Take it over the shoulder blades, not across the chest.',
                    'tip'   => 'This decides your garment\'s silhouette. A friend is worth their weight here.',
                ],

                'chest' => [
                    'label' => 'Chest',
                    'title' => 'Full circle at the fullest point',
                    'how'   => 'Wrap the tape all the way around, level with the ground — front, back, both sides. It should sit at the fullest part of your chest, usually just under the armpits. Breathe normally; don\'t puff out.',
                    'tip'   => 'Check the tape isn\'t riding higher in the back. Ask a mirror or take a photo.',
                ],

                'waist' => [
                    'label' => 'Waist',
                    'title' => 'The natural crease when you bend sideways',
                    'how'   => 'Not where your trousers ride — where your body actually bends. Lean gently to one side; the fold that appears is your natural waist. Measure there, tape parallel to the floor.',
                    'tip'   => 'Most people measure this two inches too low. Trust the crease, not the belt.',
                ],

                'hip' => [
                    'label' => 'Hip',
                    'title' => 'The widest circle below the waist',
                    'how'   => 'Feet together, tape around the fullest part of your seat. This is often lower than people expect — around eight inches below the natural waist. Keep the tape level.',
                    'tip'   => 'Empty your pockets. A phone in there adds an inch we\'d rather not cut in.',
                ],

                'sleeve' => [
                    'label' => 'Sleeve',
                    'title' => 'Shoulder point down to wrist bone',
                    'how'   => 'Arm bent slightly at your side, elbow relaxed. Start where the shoulder measurement ended — the bony point — and run the tape down the outside of your arm, over the elbow, to the bone at your wrist.',
                    'tip'   => 'A straight arm gives a sleeve that binds. Keep the bend natural, as if holding a cup.',
                ],

                'top-length' => [
                    'label' => 'Top length',
                    'title' => 'From the shoulder to where the garment ends',
                    'how'   => 'For a shirt, this is the base of the neck down to just below the belt. For an agbada or kaftan, take it to the point on your leg where the garment should stop.',
                    'tip'   => 'If you have a top you love the length of, measure that flat and use its number.',
                ],

                'inseam' => [
                    'label' => 'Inseam',
                    'title' => 'Crotch seam down to your ankle bone',
                    'how'   => 'Wear the shoes you\'d wear with the garment — this changes the length by half an inch, and half an inch matters. Measure the inside of your leg from the crotch straight down to the bone at your ankle.',
                    'tip'   => 'A pair of trousers you love? Lay them flat and measure the inseam. Faster and more accurate.',
                ],
            ],
        ],

        'index' => [
            'offer'               => 'Get UPTO 40% OFF on your 1st order SHOP NOW',
            'resend-verify-email' => 'Resend Verification Email',
            'verify-email'        => 'Verify your email account',
        ],

        'thanks-for-contact' => 'Thanks for contacting us with your comments and questions. We all respond to you very soon.',
    ],

    'partials' => [
        'pagination' => [
            'pagination-showing' => 'Showing :firstItem to :lastItem of :total entries',
        ],
    ],

    'errors' => [
        'go-to-home' => 'Go To Home',

        '404' => [
            'description' => 'Oops! The page you\'re looking for is on vacation. It seems we couldn\'t find what you were searching for.',
            'title'       => '404 Page Not Found',
        ],

        '401' => [
            'description' => 'Oops! Looks like you\'re not allowed to access this page. It seems you\'re missing the necessary credentials.',
            'title'       => '401 Unauthorized',
        ],

        '403' => [
            'description' => 'Oops! This page is off-limits. It appears you don\'t have the required permissions to view this content.',
            'title'       => '403 Forbidden',
        ],

        '500' => [
            'description' => 'Oops! Something went wrong. It seems we\'re having trouble loading the page you\'re looking for.',
            'title'       => '500 Internal Server Error',
        ],

        '503' => [
            'description' => 'Oops! Looks like we\'re temporarily down for maintenance. Please check back in a bit.',
            'title'       => '503 Service Unavailable',
        ],
    ],

    'layouts' => [
        'address'               => 'Address',
        'downloadable-products' => 'Downloadable Products',
        'my-account'            => 'My Account',
        'orders'                => 'Orders',
        'profile'               => 'Profile',
        'reviews'               => 'Reviews',
        'wishlist'              => 'Wishlist',
    ],

    'subscription' => [
        'already'             => 'You are already subscribed to our newsletter.',
        'subscribe-success'   => 'You have successfully subscribed to our newsletter.',
        'unsubscribe-success' => 'You have successfully unsubscribed to our newsletter.',
    ],

    'emails' => [
        'dear'   => 'Dear :customer_name',
        'thanks' => 'If you need any kind of help please contact us at <a href=":link" style=":style">:email</a>.<br/>Thanks!',

        'customers' => [
            'registration' => [
                'credentials-description' => 'Your account has been created. Your account details are below:',
                'description'             => 'Your account has now been created successfully and you can login using your email address and password credentials. Upon logging in, you will be able to access other services including reviewing past orders, wishlists and editing your account information.',
                'greeting'                => 'Welcome and thank you for registering with us!',
                'password'                => 'Password',
                'sign-in'                 => 'Sign in',
                'subject'                 => 'New Customer Registration',
                'username-email'          => 'Username/Email',
            ],

            'forgot-password' => [
                'description'    => 'You are receiving this email because we received a password reset request for your account.',
                'greeting'       => 'Forgot Password!',
                'reset-password' => 'Reset Password',
                'subject'        => 'Reset Password Email',
            ],

            'update-password' => [
                'description' => 'You are receiving this email because you have updated your password.',
                'greeting'    => 'Password Updated!',
                'subject'     => 'Password Updated',
            ],

            'verification' => [
                'description'  => 'Please click the button below to verify your email address.',
                'greeting'     => 'Welcome!',
                'subject'      => 'Account Verification Email',
                'verify-email' => 'Verify Email Address',
            ],

            'commented' => [
                'description' => 'Note Is - :note',
                'subject'     => 'New comment Added',
            ],

            'subscribed' => [
                'description' => 'Congratulations and welcome to our newsletter community! We\'re excited to have you on board and keep you updated with the latest news, trends, and exclusive offers.',
                'greeting'    => 'Welcome to our newsletter!',
                'subject'     => 'You! Subscribe to Our Newsletter',
                'unsubscribe' => 'Unsubscribe',
            ],

            'reminder' => [
                'already-paid'    => 'If you have already made the payment, please disregard this message.',
                'invoice-overdue' => 'This is a gentle reminder that your invoice is now overdue. We kindly request you to make the payment at your earliest convenience.',
                'subject'         => 'Invoice reminder',
            ],
        ],

        'contact-us' => [
            'contact-from'    => 'via Website Contact Form',
            'reply-to-mail'   => 'please reply to this email.',
            'reach-via-phone' => 'Alternatively, you can reach us by phone at',
            'inquiry-from'    => 'Inquiry from',
            'to'              => 'To contact',
        ],

        'orders' => [
            'created' => [
                'greeting' => 'Thanks for your Order :order_id placed on :created_at',
                'subject'  => 'New Order Confirmation',
                'summary'  => 'Summary of Order',
                'title'    => 'Order Confirmation!',
            ],

            'invoiced' => [
                'greeting' => 'Your invoice #:invoice_id for Order :order_id created on :created_at',
                'subject'  => 'New Invoice Confirmation',
                'summary'  => 'Summary of Invoice',
                'title'    => 'Invoice Confirmation!',
            ],

            'shipped' => [
                'greeting' => 'Your order :order_id placed on :created_at has been shipped',
                'subject'  => 'New Shipment Confirmation',
                'summary'  => 'Summary of Shipment',
                'title'    => 'Order Shipped!',
            ],

            'refunded' => [
                'greeting' => 'Refund has been initiated for the :order_id placed on :created_at',
                'subject'  => 'New Refund Confirmation',
                'summary'  => 'Summary of Refund',
                'title'    => 'Order Refunded!',
            ],

            'canceled' => [
                'greeting' => 'Your Order :order_id placed on :created_at has been canceled',
                'subject'  => 'New Order Canceled',
                'summary'  => 'Summary of Order',
                'title'    => 'Order Canceled!',
            ],

            'commented' => [
                'subject' => 'New comment Added',
                'title'   => 'New comment added to your order :order_id placed on :created_at',
            ],

            'status-updated' => [
                'current-status' => 'Current status',
                'greeting'       => 'There is an update on your order :order_id. Here is its latest status.',
                'subject'        => 'Order #:order_id status update',
                'title'          => 'Your order status has been updated',
                'view-order'     => 'View your order',
            ],

            'billing-address'            => 'Billing Address',
            'carrier'                    => 'Carrier',
            'contact'                    => 'Contact',
            'discount'                   => 'Discount',
            'excl-tax'                   => 'Excl. Tax: ',
            'grand-total'                => 'Grand Total',
            'name'                       => 'Name',
            'payment'                    => 'Payment',
            'price'                      => 'Price',
            'qty'                        => 'Qty',
            'shipping-address'           => 'Shipping Address',
            'shipping-handling-excl-tax' => 'Shipping Handling (Excl. Tax)',
            'shipping-handling-incl-tax' => 'Shipping Handling (Incl. Tax)',
            'shipping-handling'          => 'Shipping Handling',
            'shipping'                   => 'Shipping',
            'sku'                        => 'SKU',
            'subtotal-excl-tax'          => 'Subtotal (Excl. Tax)',
            'subtotal-incl-tax'          => 'Subtotal (Incl. Tax)',
            'subtotal'                   => 'Subtotal',
            'tax'                        => 'Tax',
            'tracking-number'            => 'Tracking Number : :tracking_number',
        ],
    ],
];
