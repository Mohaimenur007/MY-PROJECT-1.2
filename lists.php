<?php

// The fixed lists used by forms, filters and validation.

$categories = [
    "Electronics", "Documents", "Bags & Wallets", "Jewelry",
    "Keys", "Pets", "Clothing", "Other"
];

$divisions = [
    "Dhaka", "Chattogram", "Rajshahi", "Khulna",
    "Barishal", "Sylhet", "Rangpur", "Mymensingh"
];

// All 64 districts, grouped by division (used as suggestions in the District field).
$districts = [
    "Dhaka" => ["Dhaka", "Faridpur", "Gazipur", "Gopalganj", "Kishoreganj", "Madaripur", "Manikganj",
                "Munshiganj", "Narayanganj", "Narsingdi", "Rajbari", "Shariatpur", "Tangail"],
    "Chattogram" => ["Bandarban", "Brahmanbaria", "Chandpur", "Chattogram", "Cox's Bazar", "Cumilla",
                     "Feni", "Khagrachhari", "Lakshmipur", "Noakhali", "Rangamati"],
    "Rajshahi" => ["Bogura", "Chapainawabganj", "Joypurhat", "Naogaon", "Natore", "Pabna", "Rajshahi", "Sirajganj"],
    "Khulna" => ["Bagerhat", "Chuadanga", "Jashore", "Jhenaidah", "Khulna", "Kushtia", "Magura",
                 "Meherpur", "Narail", "Satkhira"],
    "Barishal" => ["Barguna", "Barishal", "Bhola", "Jhalokathi", "Patuakhali", "Pirojpur"],
    "Sylhet" => ["Habiganj", "Moulvibazar", "Sunamganj", "Sylhet"],
    "Rangpur" => ["Dinajpur", "Gaibandha", "Kurigram", "Lalmonirhat", "Nilphamari", "Panchagarh",
                  "Rangpur", "Thakurgaon"],
    "Mymensingh" => ["Jamalpur", "Mymensingh", "Netrokona", "Sherpur"]
];

// Colour schemes offered by the palette button (the colours live in css/theme.css).
$palettes = [
    ["id" => "stone", "name" => "Stone & Mocha", "desc" => "Warm grey, mocha and caramel", "colors" => ["#f7f5f2", "#6B4A2E", "#A88462", "#1c1917"]],
    ["id" => "teal", "name" => "Mocha & Teal", "desc" => "Cream, deep teal and copper", "colors" => ["#f6f1ea", "#11615c", "#c8834f", "#0b2624"]],
    ["id" => "gold", "name" => "Espresso & Gold", "desc" => "Ivory, espresso and gold", "colors" => ["#faf7f2", "#2a1d16", "#c79c45", "#2e7d5a"]]
];
