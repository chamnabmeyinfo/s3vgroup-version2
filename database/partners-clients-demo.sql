-- Demo/Sample Data for Partners & Clients
-- Run this after creating the partners table
-- Sample Partners
INSERT INTO partners (
        name,
        logo,
        website_url,
        type,
        sort_order,
        is_active
    )
VALUES (
        'Toyota Material Handling',
        'storage/uploads/demo/partner-toyota.png',
        'https://www.toyotaforklift.com',
        'partner',
        1,
        1
    ),
    (
        'Caterpillar',
        'storage/uploads/demo/partner-caterpillar.png',
        'https://www.cat.com',
        'partner',
        2,
        1
    ),
    (
        'Komatsu',
        'storage/uploads/demo/partner-komatsu.png',
        'https://www.komatsu.com',
        'partner',
        3,
        1
    ),
    (
        'Hyster',
        'storage/uploads/demo/partner-hyster.png',
        'https://www.hyster.com',
        'partner',
        4,
        1
    ),
    (
        'Yale',
        'storage/uploads/demo/partner-yale.png',
        'https://www.yale.com',
        'partner',
        5,
        1
    ),
    (
        'Crown',
        'storage/uploads/demo/partner-crown.png',
        'https://www.crown.com',
        'partner',
        6,
        1
    ),
    (
        'Raymond',
        'storage/uploads/demo/partner-raymond.png',
        'https://www.raymondcorp.com',
        'partner',
        7,
        1
    ),
    (
        'Linde',
        'storage/uploads/demo/partner-linde.png',
        'https://www.linde-mh.com',
        'partner',
        8,
        1
    );
-- Sample Clients
INSERT INTO partners (
        name,
        logo,
        website_url,
        type,
        sort_order,
        is_active
    )
VALUES (
        'Amazon Logistics',
        'storage/uploads/demo/client-amazon.png',
        'https://www.amazon.com',
        'client',
        1,
        1
    ),
    (
        'Walmart Distribution',
        'storage/uploads/demo/client-walmart.png',
        'https://www.walmart.com',
        'client',
        2,
        1
    ),
    (
        'FedEx Supply Chain',
        'storage/uploads/demo/client-fedex.png',
        'https://www.fedex.com',
        'client',
        3,
        1
    ),
    (
        'DHL Supply Chain',
        'storage/uploads/demo/client-dhl.png',
        'https://www.dhl.com',
        'client',
        4,
        1
    ),
    (
        'UPS Logistics',
        'storage/uploads/demo/client-ups.png',
        'https://www.ups.com',
        'client',
        5,
        1
    ),
    (
        'Target Corporation',
        'storage/uploads/demo/client-target.png',
        'https://www.target.com',
        'client',
        6,
        1
    ),
    (
        'Home Depot',
        'storage/uploads/demo/client-homedepot.png',
        'https://www.homedepot.com',
        'client',
        7,
        1
    ),
    (
        'Costco Wholesale',
        'storage/uploads/demo/client-costco.png',
        'https://www.costco.com',
        'client',
        8,
        1
    );