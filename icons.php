<?php

// Line icons (24px, stroke) and the flat item illustrations.
// ic("search") and illus("Pets") return inline <svg> markup.

const ICONS = [
    'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    'pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    'phone' => '<rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/>',
    'doc' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/>',
    'wallet' => '<path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/>',
    'gem' => '<path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/>',
    'key' => '<circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/>',
    'paw' => '<circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.05Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"/>',
    'shirt' => '<path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/>',
    'box' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
    'sparkle' => '<path d="M12 3l1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2z"/><path d="M19 3v4"/><path d="M21 5h-4"/>',
    'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
    'chat' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
    'usercheck' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/>',
    'shield' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>',
    'bulb' => '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>',
    'bookmark' => '<path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>',
    'printer' => '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>',
    'flag' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><path d="M4 22v-7"/>',
    'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
    'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
    'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
    'arrowRight' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
    'arrowLeft' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
    'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
    'moon' => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
    'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
    'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
    'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
    'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.59 13.51 6.83 3.98"/><path d="m15.41 6.51-6.82 3.98"/>',
    'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
    'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
    'compass' => '<circle cx="12" cy="12" r="10"/><path d="m16.24 7.76-2.12 6.36-6.36 2.12 2.12-6.36z"/>',
    'check' => '<path d="M20 6 9 17l-5-5"/>',
    'checkCircle' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
    'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    'edit' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
    'trash' => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
    'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'grid' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
    'clipboard' => '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
    'lock' => '<rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    'zap' => '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>',
    'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    'gift' => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C10 3 12 8 12 8s2-5 4.5-5a2.5 2.5 0 0 1 0 5"/>',
    'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
    'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'sliders' => '<path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/>',
    'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    'call' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
    'chart' => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
    'palette' => '<circle cx="13.5" cy="6.5" r="1" fill="currentColor"/><circle cx="17.5" cy="10.5" r="1" fill="currentColor"/><circle cx="8.5" cy="7.5" r="1" fill="currentColor"/><circle cx="6.5" cy="12.5" r="1" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.93 0 1.65-.75 1.65-1.69 0-.44-.18-.84-.44-1.13-.29-.29-.44-.65-.44-1.13a1.64 1.64 0 0 1 1.67-1.67h2c3.05 0 5.55-2.5 5.55-5.55C21.97 6.01 17.46 2 12 2z"/>',
    'globe' => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
];

// Category => icon name, and alert type => icon name.
const CATEGORY_ICONS = [
    'Electronics' => 'phone',
    'Documents' => 'doc',
    'Bags & Wallets' => 'wallet',
    'Jewelry' => 'gem',
    'Keys' => 'key',
    'Pets' => 'paw',
    'Clothing' => 'shirt',
    'Other' => 'box',
];

const ALERT_ICONS = [
    'match' => 'sparkle',
    'claim' => 'usercheck',
    'admin' => 'shield',
    'tip' => 'bulb',
    'search' => 'bell',
    'saved' => 'bookmark',
    'message' => 'chat',
];

// One flat drawing per category, shown when a report has no photo.
const ILLUSTRATIONS = [
    'Bags & Wallets' => '<rect x="32" y="26" width="44" height="18" rx="3" fill="#EFE3D1" transform="rotate(-8 54 35)"/><rect x="34" y="30" width="40" height="5" fill="#C8834F" opacity=".55" transform="rotate(-8 54 35)"/><rect x="22" y="36" width="76" height="54" rx="11" fill="#6B4A2E"/><rect x="27" y="41" width="66" height="44" rx="8" fill="none" stroke="#D5C4AE" stroke-opacity=".45" stroke-width="1.3" stroke-dasharray="3 3"/><path d="M60 50h32a6 6 0 0 1 6 6v12a6 6 0 0 1-6 6H60a4 4 0 0 1-4-4V54a4 4 0 0 1 4-4z" fill="#3A2418"/><circle cx="85" cy="62" r="4.5" fill="#D9A441"/><circle cx="85" cy="62" r="1.8" fill="#8A5A2B"/>',
    'Electronics' => '<g transform="rotate(-10 60 58)"><rect x="37" y="14" width="46" height="88" rx="10" fill="#26211E"/><rect x="41" y="20" width="38" height="76" rx="6" fill="#EADCC8"/><rect x="41" y="20" width="38" height="24" rx="6" fill="#C8834F" opacity=".9"/><rect x="46" y="29" width="18" height="3" rx="1.5" fill="#fff" opacity=".85"/><rect x="46" y="35" width="11" height="3" rx="1.5" fill="#fff" opacity=".6"/><rect x="46" y="51" width="8" height="8" rx="2.5" fill="#6B4A2E"/><rect x="56" y="51" width="8" height="8" rx="2.5" fill="#4E7A3A"/><rect x="66" y="51" width="8" height="8" rx="2.5" fill="#C24E2C"/><rect x="46" y="62" width="8" height="8" rx="2.5" fill="#A88462"/><rect x="56" y="62" width="8" height="8" rx="2.5" fill="#3F6FA8"/><rect x="66" y="62" width="8" height="8" rx="2.5" fill="#D9A441"/><rect x="46" y="80" width="28" height="9" rx="4.5" fill="#fff" opacity=".8"/><rect x="53" y="16.5" width="14" height="2" rx="1" fill="#4A4A46"/></g>',
    'Documents' => '<g transform="rotate(9 66 54)"><rect x="44" y="20" width="50" height="66" rx="5" fill="#fff"/><rect x="51" y="29" width="16" height="18" rx="2" fill="#D5C4AE"/><rect x="71" y="31" width="16" height="3" rx="1.5" fill="#D5C4AE"/><rect x="71" y="38" width="12" height="3" rx="1.5" fill="#E7DCCD"/><rect x="51" y="55" width="36" height="3" rx="1.5" fill="#E7DCCD"/><rect x="51" y="62" width="30" height="3" rx="1.5" fill="#E7DCCD"/><rect x="51" y="69" width="33" height="3" rx="1.5" fill="#E7DCCD"/></g><rect x="24" y="30" width="50" height="68" rx="6" fill="#2F5D3A"/><rect x="24" y="30" width="7" height="68" rx="3" fill="#244A2D"/><circle cx="52.5" cy="57" r="11" fill="none" stroke="#D9B45A" stroke-width="2.6"/><circle cx="52.5" cy="57" r="4" fill="#D9B45A"/><rect x="40.5" y="77" width="24" height="3" rx="1.5" fill="#D9B45A"/><rect x="44.5" y="84" width="16" height="2.5" rx="1.25" fill="#D9B45A" opacity=".75"/>',
    'Jewelry' => '<circle cx="60" cy="72" r="23" fill="none" stroke="#C9922E" stroke-width="8"/><circle cx="60" cy="72" r="23" fill="none" stroke="#F0CB6E" stroke-width="2.5" stroke-dasharray="24 120" transform="rotate(200 60 72)"/><rect x="50" y="44" width="5" height="9" rx="2" fill="#C9922E"/><rect x="65" y="44" width="5" height="9" rx="2" fill="#C9922E"/><polygon points="47,22 73,22 82,33 60,52 38,33" fill="#8EC3DC"/><polygon points="47,22 73,22 67,33 53,33" fill="#C7E5F2"/><path d="M38 33h44M53 33l7 19 7-19M47 22l6 11M73 22l-6 11" stroke="#fff" stroke-opacity=".7" stroke-width="1.2" fill="none"/><path d="M92 26l2 5 5 2-5 2-2 5-2-5-5-2 5-2z" fill="#D9A441"/><path d="M28 50l1.5 3.5 3.5 1.5-3.5 1.5-1.5 3.5-1.5-3.5-3.5-1.5 3.5-1.5z" fill="#A88462"/>',
    'Keys' => '<circle cx="42" cy="36" r="14" fill="none" stroke="#A88462" stroke-width="4"/><g transform="rotate(38 52 52)"><circle cx="52" cy="52" r="12" fill="#D9A441"/><circle cx="52" cy="52" r="4.5" fill="#F4EAD9"/><rect x="62" y="49" width="36" height="6.5" rx="2" fill="#D9A441"/><rect x="85" y="55" width="4.5" height="8" rx="1" fill="#D9A441"/><rect x="92" y="55" width="4.5" height="6" rx="1" fill="#D9A441"/></g><g transform="rotate(100 40 54)"><circle cx="40" cy="54" r="10" fill="#B7B3AA"/><circle cx="40" cy="54" r="3.8" fill="#F4EAD9"/><rect x="48" y="51.5" width="30" height="5.5" rx="2" fill="#B7B3AA"/><rect x="66" y="57" width="4" height="7" rx="1" fill="#B7B3AA"/><rect x="72" y="57" width="4" height="5" rx="1" fill="#B7B3AA"/></g><g transform="rotate(-18 28 58)"><rect x="18" y="48" width="18" height="26" rx="4" fill="#C24E2C"/><circle cx="27" cy="54" r="2.4" fill="#F4EAD9"/><rect x="22" y="61" width="10" height="2.5" rx="1.2" fill="#fff" opacity=".6"/></g>',
    'Pets' => '<polygon points="31,52 37,20 56,42" fill="#8E8A84"/><polygon points="89,52 83,20 64,42" fill="#8E8A84"/><polygon points="36,46 39,28 50,41" fill="#E6B3A3"/><polygon points="84,46 81,28 70,41" fill="#E6B3A3"/><ellipse cx="60" cy="64" rx="32" ry="27" fill="#8E8A84"/><path d="M52 40l2 9M60 38v10M68 40l-2 9" stroke="#6E6A65" stroke-width="3" stroke-linecap="round"/><ellipse cx="47" cy="62" rx="5" ry="6" fill="#2A2522"/><ellipse cx="73" cy="62" rx="5" ry="6" fill="#2A2522"/><circle cx="48.6" cy="60" r="1.6" fill="#fff"/><circle cx="74.6" cy="60" r="1.6" fill="#fff"/><polygon points="56,71 64,71 60,76" fill="#E58C8A"/><path d="M60 76q-4 5-9 2M60 76q4 5 9 2" stroke="#2A2522" stroke-width="1.6" fill="none" stroke-linecap="round"/><path d="M30 70l12 2M30 77l12-1M90 70l-12 2M90 77l-12-1" stroke="#D5D0C8" stroke-width="1.3" stroke-linecap="round"/><rect x="38" y="86" width="44" height="8" rx="4" fill="#3F6FA8"/><circle cx="60" cy="97" r="5.5" fill="#D9A441"/><path d="M57 98h6" stroke="#8A5A2B" stroke-width="1.3"/>',
    'Clothing' => '<path d="M42 26l12-6q6 8 12 0l12 6 16 18-11 9-5-5v50H42V48l-5 5-11-9z" fill="#4F6E9A"/><path d="M54 20q6 12 12 0l-3-2q-3 6-6 0z" fill="#3B5579"/><path d="M60 30v68" stroke="#3B5579" stroke-width="2"/><rect x="44" y="50" width="12" height="12" rx="2" fill="none" stroke="#A9BEDA" stroke-width="1.3" stroke-dasharray="2.5 2"/><rect x="64" y="50" width="12" height="12" rx="2" fill="none" stroke="#A9BEDA" stroke-width="1.3" stroke-dasharray="2.5 2"/><circle cx="60" cy="44" r="1.9" fill="#D9A441"/><circle cx="60" cy="58" r="1.9" fill="#D9A441"/><circle cx="60" cy="72" r="1.9" fill="#D9A441"/><circle cx="60" cy="86" r="1.9" fill="#D9A441"/><path d="M42 92h36" stroke="#3B5579" stroke-width="3"/>',
    'Other' => '<polygon points="30,44 90,44 82,30 38,30" fill="#DDB68A"/><rect x="30" y="44" width="60" height="50" rx="3" fill="#C89B6D"/><polygon points="55,30 65,30 66,44 54,44" fill="#EEDDC4"/><rect x="54" y="44" width="12" height="50" fill="#EEDDC4"/><rect x="36" y="72" width="14" height="14" rx="2" fill="#fff" opacity=".85"/><path d="M39 77h8M39 81h5" stroke="#A88462" stroke-width="1.4"/><path d="M74 60l6 6M80 60l-6 6" stroke="#8A5A2B" stroke-width="1.6" stroke-linecap="round" opacity=".6"/>',
];

function ic($name, $class = "")
{
    $paths = isset(ICONS[$name]) ? ICONS[$name] : ICONS["box"];
    return '<svg class="ic' . ($class != "" ? " " . $class : "") . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . "</svg>";
}

function category_icon($category, $class = "")
{
    return ic(isset(CATEGORY_ICONS[$category]) ? CATEGORY_ICONS[$category] : "box", $class);
}

function illus($category)
{
    $art = isset(ILLUSTRATIONS[$category]) ? ILLUSTRATIONS[$category] : ILLUSTRATIONS["Other"];
    return '<svg class="illus" viewBox="0 0 120 120" aria-hidden="true">'
        . '<circle cx="60" cy="60" r="46" fill="#fff" opacity=".55"/>'
        . '<ellipse cx="60" cy="106" rx="30" ry="4.5" fill="#1A120E" opacity=".10"/>'
        . $art . "</svg>";
}
