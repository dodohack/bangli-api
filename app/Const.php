<?php
/**
 * Definitions of global constants
 */

// These constants match $request->input('etype').
// See client side bangli-spa/app/models/entity.ts for more detail.
define ("ETYPE_TOPIC",      "topic");
define ("ETYPE_POST",       "post");
define ("ETYPE_OFFER",      "offer");
define ("ETYPE_PAGE",       "page");
define ("ETYPE_ADVERTISE",  "advertise");
define ("ETYPE_NEWSLETTER", "newsletter");
define ("ETYPE_ATTACHMENT", "attachment");
define ("ETYPE_COMMENT",    "comment");


// Topic type constant
define ("TT_BRAND",    "brand");    // TODO: May not need
define ("TT_MERCHANT", "merchant");
define ("TT_PRODUCTS", "products"); // TODO: May not need
define ("TT_PRODUCT",  "product");  // TODO: May not need
define ("TT_GENERIC",  "generic");  // General topic
define ("TT_COUNTRY",  "country");
define ("TT_CITY",     "city");     // Highlighted travel city
define ("TT_ROUTE",    "route");    // Highlighted travel route
