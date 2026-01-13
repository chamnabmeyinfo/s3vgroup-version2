-- Demo/Sample Data for Services
-- Run this after creating the services table
-- Sample Services
INSERT INTO services (
        title,
        slug,
        description,
        content,
        icon,
        sort_order,
        is_active,
        meta_title,
        meta_description
    )
VALUES (
        'Forklift Sales',
        'forklift-sales',
        'Wide selection of new and used forklifts from top manufacturers',
        '<h2>Comprehensive Forklift Sales</h2><p>We offer a wide range of forklifts including electric, diesel, and gas-powered models. From compact warehouse forklifts to heavy-duty industrial machines, we have the perfect solution for your business needs.</p><ul><li>New and used forklifts</li><li>All major brands available</li><li>Flexible financing options</li><li>Trade-in programs</li></ul>',
        'fas fa-truck',
        1,
        1,
        'Forklift Sales - New & Used Forklifts',
        'Browse our extensive selection of new and used forklifts. Best prices and flexible financing available.'
    ),
    (
        'Equipment Rental',
        'equipment-rental',
        'Short-term and long-term rental solutions for all your equipment needs',
        '<h2>Flexible Equipment Rental</h2><p>Need equipment for a short-term project? Our rental program offers flexible terms from daily to monthly rentals. Perfect for seasonal peaks or special projects.</p><ul><li>Daily, weekly, and monthly rentals</li><li>Well-maintained equipment</li><li>Delivery and pickup service</li><li>Operator training included</li></ul>',
        'fas fa-hand-holding-usd',
        2,
        1,
        'Equipment Rental Services',
        'Flexible rental solutions for forklifts and industrial equipment. Short-term and long-term options available.'
    ),
    (
        'Maintenance & Repairs',
        'maintenance-repairs',
        'Expert maintenance and repair services to keep your equipment running smoothly',
        '<h2>Professional Maintenance & Repairs</h2><p>Our certified technicians provide comprehensive maintenance and repair services. We offer scheduled maintenance programs to prevent costly breakdowns and extend equipment life.</p><ul><li>Preventive maintenance programs</li><li>Emergency repair services</li><li>Original parts and components</li><li>24/7 support available</li></ul>',
        'fas fa-tools',
        3,
        1,
        'Forklift Maintenance & Repair Services',
        'Expert maintenance and repair services for all forklift brands. Preventive maintenance programs available.'
    ),
    (
        'Parts & Accessories',
        'parts-accessories',
        'Genuine parts and quality accessories for all equipment brands',
        '<h2>Genuine Parts & Accessories</h2><p>We stock a comprehensive inventory of genuine OEM parts and quality aftermarket alternatives. Fast shipping and competitive prices on all parts and accessories.</p><ul><li>Genuine OEM parts</li><li>Quality aftermarket alternatives</li><li>Fast shipping available</li><li>Competitive pricing</li></ul>',
        'fas fa-cog',
        4,
        1,
        'Forklift Parts & Accessories',
        'Genuine parts and accessories for all forklift brands. Fast shipping and competitive prices.'
    ),
    (
        'Operator Training',
        'operator-training',
        'Comprehensive training programs to ensure safe and efficient equipment operation',
        '<h2>Professional Operator Training</h2><p>Safety is our priority. Our certified instructors provide comprehensive training programs covering operation, safety protocols, and maintenance basics.</p><ul><li>OSHA-compliant training</li><li>Certification programs</li><li>On-site or classroom training</li><li>Refresher courses available</li></ul>',
        'fas fa-user-graduate',
        5,
        1,
        'Forklift Operator Training & Certification',
        'OSHA-compliant forklift operator training and certification programs. On-site and classroom options available.'
    ),
    (
        'Consulting Services',
        'consulting-services',
        'Expert advice to optimize your warehouse and material handling operations',
        '<h2>Material Handling Consulting</h2><p>Our experienced consultants help you optimize your warehouse operations, improve efficiency, and reduce costs through better equipment selection and layout planning.</p><ul><li>Warehouse optimization</li><li>Equipment selection guidance</li><li>Layout planning</li><li>Cost analysis</li></ul>',
        'fas fa-chart-line',
        6,
        1,
        'Material Handling Consulting Services',
        'Expert consulting services for warehouse optimization and material handling solutions.'
    );