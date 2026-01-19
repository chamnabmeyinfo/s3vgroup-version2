-- Demo/Sample Data for Quality Certifications
-- Run this after creating the quality_certifications table
-- Sample Quality Certifications
INSERT INTO quality_certifications (
        name,
        logo,
        reference_url,
        description,
        sort_order,
        is_active
    )
VALUES (
        'ISO 9001:2015',
        'storage/uploads/demo/cert-iso9001.png',
        'https://www.iso.org/iso-9001-quality-management.html',
        'Quality Management System Certification',
        1,
        1
    ),
    (
        'ISO 14001:2015',
        'storage/uploads/demo/cert-iso14001.png',
        'https://www.iso.org/iso-14001-environmental-management.html',
        'Environmental Management System Certification',
        2,
        1
    ),
    (
        'CE Marking',
        'storage/uploads/demo/cert-ce.png',
        'https://ec.europa.eu/growth/single-market/ce-marking_en',
        'European Conformity Marking',
        3,
        1
    ),
    (
        'RIML',
        'storage/uploads/demo/cert-riml.png',
        'https://www.riml.org',
        'RIML Quality Certification',
        4,
        1
    ),
    (
        'OHSAS 18001',
        'storage/uploads/demo/cert-ohsas.png',
        'https://www.iso.org/iso-45001-occupational-health-and-safety.html',
        'Occupational Health and Safety Management',
        5,
        1
    ),
    (
        'ISO 45001:2018',
        'storage/uploads/demo/cert-iso45001.png',
        'https://www.iso.org/iso-45001-occupational-health-and-safety.html',
        'Occupational Health and Safety Management System',
        6,
        1
    ),
    (
        'ISO 27001',
        'storage/uploads/demo/cert-iso27001.png',
        'https://www.iso.org/isoiec-27001-information-security.html',
        'Information Security Management System',
        7,
        1
    ),
    (
        'IATF 16949',
        'storage/uploads/demo/cert-iatf.png',
        'https://www.iatfglobaloversight.org',
        'Automotive Quality Management System',
        8,
        1
    );
