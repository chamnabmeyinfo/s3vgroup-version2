<?php
/**
 * Sample Data Generator for Factory Equipment
 * This script populates the database with realistic sample data
 */
require_once __DIR__ . '/../bootstrap/app.php';

echo "🚀 Starting Sample Data Generation...\n\n";

try {
    $db = db();
    
    // Sample Categories
    echo "📁 Creating Categories...\n";
    $categories = [
        [
            'name' => 'Electric Forklifts',
            'slug' => 'electric-forklifts',
            'description' => 'Environmentally friendly electric forklifts perfect for indoor operations',
            'image' => null
        ],
        [
            'name' => 'Gas Forklifts',
            'slug' => 'gas-forklifts',
            'description' => 'Powerful gas-powered forklifts for heavy-duty outdoor operations',
            'image' => null
        ],
        [
            'name' => 'Pallet Trucks',
            'slug' => 'pallet-trucks',
            'description' => 'Manual and electric pallet trucks for efficient material handling',
            'image' => null
        ],
        [
            'name' => 'Reach Trucks',
            'slug' => 'reach-trucks',
            'description' => 'Narrow aisle reach trucks for maximizing warehouse space',
            'image' => null
        ],
        [
            'name' => 'Stackers',
            'slug' => 'stackers',
            'description' => 'Manual and electric stackers for vertical storage solutions',
            'image' => null
        ],
        [
            'name' => 'Order Pickers',
            'slug' => 'order-pickers',
            'description' => 'Specialized equipment for efficient order picking operations',
            'image' => null
        ],
        [
            'name' => 'Sideloaders',
            'slug' => 'sideloaders',
            'description' => 'Side-loading forklifts for handling long loads',
            'image' => null
        ],
        [
            'name' => 'Attachments',
            'slug' => 'attachments',
            'description' => 'Various forklift attachments and accessories',
            'image' => null
        ]
    ];
    
    $categoryIds = [];
    foreach ($categories as $cat) {
        $existing = $db->fetchOne("SELECT id FROM categories WHERE slug = :slug", ['slug' => $cat['slug']]);
        if (!$existing) {
            $id = $db->insert('categories', $cat);
            $categoryIds[$cat['slug']] = $id;
            echo "  ✓ Created: {$cat['name']}\n";
        } else {
            $categoryIds[$cat['slug']] = $existing['id'];
            echo "  - Exists: {$cat['name']}\n";
        }
    }
    
    // Sample Products
    echo "\n📦 Creating Products...\n";
    $products = [
        // Electric Forklifts
        [
            'name' => 'Yale ERP25VT Electric Forklift',
            'slug' => 'yale-erp25vt-electric-forklift',
            'sku' => 'YALE-ERP25VT',
            'category_id' => $categoryIds['electric-forklifts'],
            'short_description' => 'Compact 2.5-ton electric forklift with excellent maneuverability and zero emissions. Perfect for indoor warehouse operations.',
            'description' => 'The Yale ERP25VT is a high-performance electric forklift designed for indoor material handling. Features advanced AC drive technology, ergonomic operator compartment, and maintenance-free design. Ideal for warehouses, distribution centers, and manufacturing facilities.',
            'price' => 28500.00,
            'sale_price' => 26500.00,
            'stock_status' => 'in_stock',
            'is_featured' => 1,
            'is_active' => 1,
            'weight' => 3850,
            'dimensions' => '90" L x 47" W x 82" H',
            'specifications' => json_encode([
                'Capacity' => '2,500 lbs',
                'Lift Height' => '189 inches',
                'Power Source' => '48V Battery',
                'Travel Speed' => '8.5 mph',
                'Lift Speed' => '90 ft/min'
            ]),
            'features' => json_encode([
                'Zero emissions',
                'AC drive technology',
                'Ergonomic controls',
                'Low maintenance',
                'Excellent visibility'
            ])
        ],
        [
            'name' => 'Toyota 8FGU25 Electric Forklift',
            'slug' => 'toyota-8fgu25-electric-forklift',
            'sku' => 'TOY-8FGU25',
            'category_id' => $categoryIds['electric-forklifts'],
            'short_description' => 'Reliable 5,000 lb capacity electric forklift with System of Active Stability (SAS). Industry-leading safety and performance.',
            'description' => 'Toyota 8FGU25 electric forklift offers exceptional reliability and operator comfort. Features System of Active Stability (SAS) for enhanced safety, smooth acceleration, and excellent energy efficiency. Perfect for medium to heavy-duty applications.',
            'price' => 42500.00,
            'sale_price' => null,
            'stock_status' => 'in_stock',
            'is_featured' => 1,
            'is_active' => 1,
            'weight' => 8900,
            'dimensions' => '108" L x 52" W x 95" H',
            'specifications' => json_encode([
                'Capacity' => '5,000 lbs',
                'Lift Height' => '204 inches',
                'Power Source' => '48V Battery',
                'Travel Speed' => '9.5 mph',
                'Lift Speed' => '95 ft/min'
            ]),
            'features' => json_encode([
                'System of Active Stability',
                'Energy efficient',
                'Comfortable operator compartment',
                'Durable construction',
                'Excellent warranty'
            ])
        ],
        [
            'name' => 'Crown SC 4500 Electric Forklift',
            'slug' => 'crown-sc-4500-electric-forklift',
            'sku' => 'CRW-SC4500',
            'category_id' => $categoryIds['electric-forklifts'],
            'short_description' => 'Heavy-duty 4,500 lb capacity electric forklift with advanced technology and superior visibility.',
            'description' => 'Crown SC 4500 electric forklift combines power and precision. Features advanced operator controls, excellent visibility, and durable construction. Ideal for demanding warehouse and manufacturing environments.',
            'price' => 38500.00,
            'sale_price' => 34500.00,
            'stock_status' => 'in_stock',
            'is_featured' => 0,
            'is_active' => 1,
            'weight' => 7200,
            'dimensions' => '102" L x 49" W x 88" H',
            'specifications' => json_encode([
                'Capacity' => '4,500 lbs',
                'Lift Height' => '195 inches',
                'Power Source' => '48V Battery',
                'Travel Speed' => '9.0 mph',
                'Lift Speed' => '88 ft/min'
            ]),
            'features' => json_encode([
                'Advanced controls',
                'Superior visibility',
                'Durable design',
                'Easy maintenance',
                'Quiet operation'
            ])
        ],
        
        // Gas Forklifts
        [
            'name' => 'CAT EP16C2 Gas Forklift',
            'slug' => 'cat-ep16c2-gas-forklift',
            'sku' => 'CAT-EP16C2',
            'category_id' => $categoryIds['gas-forklifts'],
            'short_description' => 'Rugged 16,000 lb capacity gas forklift built for outdoor heavy-duty applications. Powerful and reliable.',
            'description' => 'Caterpillar EP16C2 gas forklift delivers exceptional performance for outdoor and heavy-duty applications. Features powerful engine, durable construction, and excellent fuel efficiency. Ideal for construction sites, lumber yards, and heavy manufacturing.',
            'price' => 68500.00,
            'sale_price' => null,
            'stock_status' => 'in_stock',
            'is_featured' => 1,
            'is_active' => 1,
            'weight' => 18500,
            'dimensions' => '132" L x 78" W x 112" H',
            'specifications' => json_encode([
                'Capacity' => '16,000 lbs',
                'Lift Height' => '220 inches',
                'Engine' => 'Gasoline/LPG',
                'Horsepower' => '95 HP',
                'Travel Speed' => '12.5 mph'
            ]),
            'features' => json_encode([
                'Heavy-duty construction',
                'Excellent lifting capacity',
                'Outdoor rated',
                'Fuel efficient',
                'Low maintenance'
            ])
        ],
        [
            'name' => 'Hyster H50XM Gas Forklift',
            'slug' => 'hyster-h50xm-gas-forklift',
            'sku' => 'HYS-H50XM',
            'category_id' => $categoryIds['gas-forklifts'],
            'short_description' => 'Versatile 10,000 lb capacity gas forklift perfect for mixed indoor/outdoor operations.',
            'description' => 'Hyster H50XM gas forklift offers versatility and reliability. Features comfortable operator compartment, excellent visibility, and smooth operation. Suitable for both indoor and outdoor material handling tasks.',
            'price' => 52500.00,
            'sale_price' => 49500.00,
            'stock_status' => 'in_stock',
            'is_featured' => 0,
            'is_active' => 1,
            'weight' => 12500,
            'dimensions' => '118" L x 65" W x 98" H',
            'specifications' => json_encode([
                'Capacity' => '10,000 lbs',
                'Lift Height' => '208 inches',
                'Engine' => 'Gasoline/LPG',
                'Horsepower' => '75 HP',
                'Travel Speed' => '11.0 mph'
            ]),
            'features' => json_encode([
                'Versatile operation',
                'Comfortable controls',
                'Good visibility',
                'Reliable performance',
                'Easy servicing'
            ])
        ],
        
        // Pallet Trucks
        [
            'name' => 'Raymond Easi Pallet Truck',
            'slug' => 'raymond-easi-pallet-truck',
            'sku' => 'RAY-EASI',
            'category_id' => $categoryIds['pallet-trucks'],
            'short_description' => 'Manual pallet truck with easy-lift technology. Ergonomic design reduces operator fatigue.',
            'description' => 'Raymond Easi manual pallet truck features innovative easy-lift technology that reduces effort by up to 50%. Ergonomic handle design and durable construction make this an excellent choice for material handling operations.',
            'price' => 850.00,
            'sale_price' => 750.00,
            'stock_status' => 'in_stock',
            'is_featured' => 1,
            'is_active' => 1,
            'weight' => 165,
            'dimensions' => '48" L x 27" W x 48" H',
            'specifications' => json_encode([
                'Capacity' => '5,500 lbs',
                'Fork Length' => '48 inches',
                'Lowered Height' => '3.25 inches',
                'Lift Height' => '7.5 inches',
                'Type' => 'Manual'
            ]),
            'features' => json_encode([
                'Easy-lift technology',
                'Ergonomic handle',
                'Durable wheels',
                'Low maintenance',
                'Lightweight design'
            ])
        ],
        [
            'name' => 'Crown WP 3000 Electric Pallet Truck',
            'slug' => 'crown-wp-3000-electric-pallet-truck',
            'sku' => 'CRW-WP3000',
            'category_id' => $categoryIds['pallet-trucks'],
            'short_description' => 'Electric walkie pallet truck with 6,000 lb capacity. Perfect for long-distance transport.',
            'description' => 'Crown WP 3000 electric pallet truck combines power and efficiency. Features walk-behind operation, excellent battery life, and smooth handling. Ideal for moving loads over longer distances in warehouses.',
            'price' => 4200.00,
            'sale_price' => null,
            'stock_status' => 'in_stock',
            'is_featured' => 0,
            'is_active' => 1,
            'weight' => 385,
            'dimensions' => '52" L x 28" W x 58" H',
            'specifications' => json_encode([
                'Capacity' => '6,000 lbs',
                'Fork Length' => '48 inches',
                'Power Source' => '24V Battery',
                'Travel Speed' => '5.5 mph',
                'Type' => 'Electric Walkie'
            ]),
            'features' => json_encode([
                'Long battery life',
                'Smooth operation',
                'Easy controls',
                'Durable design',
                'Quiet operation'
            ])
        ],
        
        // Reach Trucks
        [
            'name' => 'Toyota 8FBRT25 Reach Truck',
            'slug' => 'toyota-8fbrt25-reach-truck',
            'sku' => 'TOY-8FBRT25',
            'category_id' => $categoryIds['reach-trucks'],
            'short_description' => 'Narrow aisle reach truck with 5,000 lb capacity. Maximizes warehouse storage density.',
            'description' => 'Toyota 8FBRT25 reach truck excels in narrow aisle operations. Features advanced mast design, excellent visibility, and precise controls. Perfect for maximizing vertical storage in warehouses.',
            'price' => 48500.00,
            'sale_price' => 45500.00,
            'stock_status' => 'in_stock',
            'is_featured' => 1,
            'is_active' => 1,
            'weight' => 9250,
            'dimensions' => '95" L x 41" W x 82" H',
            'specifications' => json_encode([
                'Capacity' => '5,000 lbs',
                'Lift Height' => '315 inches',
                'Aisle Width' => '96 inches',
                'Power Source' => '48V Battery',
                'Travel Speed' => '8.5 mph'
            ]),
            'features' => json_encode([
                'Narrow aisle operation',
                'High lift capability',
                'Precise controls',
                'Excellent visibility',
                'Energy efficient'
            ])
        ],
        
        // Stackers
        [
            'name' => 'Yale MSW025 Stacker',
            'slug' => 'yale-msw025-stacker',
            'sku' => 'YALE-MSW025',
            'category_id' => $categoryIds['stackers'],
            'short_description' => 'Electric walkie stacker with 5,500 lb capacity. Perfect for vertical storage solutions.',
            'description' => 'Yale MSW025 electric stacker offers reliable vertical material handling. Features walk-behind operation, excellent lift height, and smooth controls. Ideal for warehouses needing efficient stacking operations.',
            'price' => 12500.00,
            'sale_price' => null,
            'stock_status' => 'in_stock',
            'is_featured' => 0,
            'is_active' => 1,
            'weight' => 1450,
            'dimensions' => '58" L x 31" W x 78" H',
            'specifications' => json_encode([
                'Capacity' => '5,500 lbs',
                'Lift Height' => '157 inches',
                'Power Source' => '24V Battery',
                'Travel Speed' => '4.5 mph',
                'Type' => 'Electric Walkie'
            ]),
            'features' => json_encode([
                'Easy operation',
                'Good lift height',
                'Reliable performance',
                'Compact design',
                'Affordable price'
            ])
        ],
        
        // Order Pickers
        [
            'name' => 'Raymond 102XM Order Picker',
            'slug' => 'raymond-102xm-order-picker',
            'sku' => 'RAY-102XM',
            'category_id' => $categoryIds['order-pickers'],
            'short_description' => 'High-level order picker with 3,000 lb capacity. Maximizes picking efficiency.',
            'description' => 'Raymond 102XM order picker is designed for efficient order picking at height. Features comfortable platform, excellent visibility, and precise controls. Perfect for distribution centers and warehouses.',
            'price' => 38500.00,
            'sale_price' => null,
            'stock_status' => 'in_stock',
            'is_featured' => 1,
            'is_active' => 1,
            'weight' => 7200,
            'dimensions' => '88" L x 38" W x 82" H',
            'specifications' => json_encode([
                'Capacity' => '3,000 lbs',
                'Lift Height' => '360 inches',
                'Platform Height' => '335 inches',
                'Power Source' => '48V Battery',
                'Travel Speed' => '6.5 mph'
            ]),
            'features' => json_encode([
                'High lift capability',
                'Comfortable platform',
                'Excellent visibility',
                'Precise controls',
                'Safety features'
            ])
        ],
        
        // Sideloaders
        [
            'name' => 'Kalmar DRF 450-1200 Sideloader',
            'slug' => 'kalmar-drf-450-1200-sideloader',
            'sku' => 'KAL-DRF450',
            'category_id' => $categoryIds['sideloaders'],
            'short_description' => 'Heavy-duty sideloader for long loads up to 45,000 lbs. Perfect for lumber and steel.',
            'description' => 'Kalmar DRF 450-1200 sideloader handles long loads efficiently. Features side-loading capability, excellent stability, and powerful performance. Ideal for lumber yards, steel fabrication, and handling long materials.',
            'price' => 185000.00,
            'sale_price' => null,
            'stock_status' => 'in_stock',
            'is_featured' => 0,
            'is_active' => 1,
            'weight' => 45000,
            'dimensions' => '312" L x 96" W x 145" H',
            'specifications' => json_encode([
                'Capacity' => '45,000 lbs',
                'Load Length' => 'Up to 40 feet',
                'Engine' => 'Diesel',
                'Horsepower' => '250 HP',
                'Travel Speed' => '18 mph'
            ]),
            'features' => json_encode([
                'Side-loading design',
                'Heavy-duty capacity',
                'Long load capability',
                'Stable operation',
                'Outdoor rated'
            ])
        ],
        
        // Attachments
        [
            'name' => 'Rotating Clamp Attachment',
            'slug' => 'rotating-clamp-attachment',
            'sku' => 'ATT-ROTCLAMP',
            'category_id' => $categoryIds['attachments'],
            'short_description' => '360-degree rotating clamp for handling paper rolls, bales, and cylindrical loads.',
            'description' => 'Rotating clamp attachment provides 360-degree rotation for handling various cylindrical loads. Features adjustable pressure, durable construction, and easy installation. Compatible with most forklift models.',
            'price' => 12500.00,
            'sale_price' => 11000.00,
            'stock_status' => 'in_stock',
            'is_featured' => 0,
            'is_active' => 1,
            'weight' => 850,
            'dimensions' => '72" L x 48" W x 32" H',
            'specifications' => json_encode([
                'Capacity' => '5,000 lbs',
                'Rotation' => '360 degrees',
                'Opening Width' => '48 inches',
                'Pressure' => 'Adjustable',
                'Compatibility' => 'Most forklifts'
            ]),
            'features' => json_encode([
                '360-degree rotation',
                'Adjustable pressure',
                'Easy installation',
                'Durable design',
                'Versatile application'
            ])
        ],
        [
            'name' => 'Fork Positioner Attachment',
            'slug' => 'fork-positioner-attachment',
            'sku' => 'ATT-FORKPOS',
            'category_id' => $categoryIds['attachments'],
            'short_description' => 'Hydraulic fork positioner for quick and precise load handling. Increases efficiency.',
            'description' => 'Fork positioner attachment allows operators to adjust fork spacing hydraulically without leaving the seat. Reduces handling time and increases productivity. Perfect for mixed pallet sizes.',
            'price' => 4800.00,
            'sale_price' => null,
            'stock_status' => 'in_stock',
            'is_featured' => 0,
            'is_active' => 1,
            'weight' => 425,
            'dimensions' => '60" L x 44" W x 18" H',
            'specifications' => json_encode([
                'Capacity' => '6,000 lbs',
                'Fork Spacing' => '12-48 inches',
                'Operation' => 'Hydraulic',
                'Fork Length' => '48 inches',
                'Compatibility' => 'Most forklifts'
            ]),
            'features' => json_encode([
                'Hydraulic operation',
                'Quick adjustment',
                'Time saving',
                'Precise control',
                'Easy installation'
            ])
        ]
    ];
    
    foreach ($products as $product) {
        $existing = $db->fetchOne("SELECT id FROM products WHERE slug = :slug", ['slug' => $product['slug']]);
        if (!$existing) {
            $id = $db->insert('products', $product);
            echo "  ✓ Created: {$product['name']}\n";
        } else {
            echo "  - Exists: {$product['name']}\n";
        }
    }
    
    // Sample FAQs (only if table exists)
    echo "\n❓ Creating FAQs...\n";
    try {
        $testFaq = $db->fetchOne("SELECT 1 FROM faqs LIMIT 1");
    } catch (Exception $e) {
        echo "  - FAQs table not found, skipping...\n";
        $testFaq = null;
    }
    
    if ($testFaq !== null) {
    $faqs = [
        [
            'question' => 'What is the difference between electric and gas forklifts?',
            'answer' => 'Electric forklifts are powered by batteries and produce zero emissions, making them ideal for indoor use. They are quieter and require less maintenance. Gas forklifts are powered by gasoline or LPG and are better suited for outdoor applications and heavy-duty tasks requiring more power.',
            'category' => 'General',
            'display_order' => 1,
            'is_active' => 1
        ],
        [
            'question' => 'How often should I service my forklift?',
            'answer' => 'Regular maintenance is crucial for forklift safety and performance. We recommend service intervals every 250 operating hours or every 3 months, whichever comes first. Daily pre-shift inspections are also essential.',
            'category' => 'Maintenance',
            'display_order' => 2,
            'is_active' => 1
        ],
        [
            'question' => 'What capacity forklift do I need?',
            'answer' => 'Forklift capacity depends on your typical load weight. Choose a forklift with at least 20% more capacity than your heaviest load to account for load distribution and ensure safe operation. Our experts can help you determine the right capacity.',
            'category' => 'General',
            'display_order' => 3,
            'is_active' => 1
        ],
        [
            'question' => 'Do you offer forklift rental services?',
            'answer' => 'Yes, we offer flexible rental options for both short-term and long-term needs. Contact us to discuss your requirements and get a custom rental quote.',
            'category' => 'Services',
            'display_order' => 4,
            'is_active' => 1
        ],
        [
            'question' => 'What warranty comes with new forklifts?',
            'answer' => 'All new forklifts come with a comprehensive manufacturer warranty, typically covering parts and labor for 12-24 months depending on the model. We also offer extended warranty options for additional protection.',
            'category' => 'Warranty',
            'display_order' => 5,
            'is_active' => 1
        ]
    ];
    
        foreach ($faqs as $faq) {
            $existing = $db->fetchOne("SELECT id FROM faqs WHERE question = :question", ['question' => $faq['question']]);
            if (!$existing) {
                $db->insert('faqs', $faq);
                echo "  ✓ Created: {$faq['question']}\n";
            }
        }
    }
    
    // Sample Testimonials (only if table exists)
    echo "\n⭐ Creating Testimonials...\n";
    try {
        $testTest = $db->fetchOne("SELECT 1 FROM testimonials LIMIT 1");
    } catch (Exception $e) {
        echo "  - Testimonials table not found, skipping...\n";
        $testTest = null;
    }
    
    if ($testTest !== null) {
    $testimonials = [
        [
            'customer_name' => 'John Smith',
            'company' => 'Smith Manufacturing Co.',
            'testimonial' => 'Excellent service and quality equipment. The electric forklifts we purchased have significantly improved our warehouse efficiency. Highly recommend!',
            'rating' => 5,
            'is_featured' => 1,
            'is_active' => 1,
            'display_order' => 1
        ],
        [
            'customer_name' => 'Sarah Johnson',
            'company' => 'ABC Distribution',
            'testimonial' => 'The reach trucks we bought are perfect for our narrow aisle warehouse. Great product quality and outstanding customer support throughout the process.',
            'rating' => 5,
            'is_featured' => 1,
            'is_active' => 1,
            'display_order' => 2
        ],
        [
            'customer_name' => 'Mike Davis',
            'company' => 'Davis Logistics',
            'testimonial' => 'Fast delivery and competitive pricing. The gas forklifts handle our outdoor operations perfectly. Very satisfied with our purchase.',
            'rating' => 5,
            'is_featured' => 0,
            'is_active' => 1,
            'display_order' => 3
        ]
    ];
    
        foreach ($testimonials as $testimonial) {
            $existing = $db->fetchOne("SELECT id FROM testimonials WHERE customer_name = :name AND company = :company", 
                ['name' => $testimonial['customer_name'], 'company' => $testimonial['company']]);
            if (!$existing) {
                $db->insert('testimonials', $testimonial);
                echo "  ✓ Created: {$testimonial['customer_name']}\n";
            }
        }
    }
    
    // Sample Blog Posts (only if table exists)
    echo "\n📰 Creating Blog Posts...\n";
    try {
        $testBlog = $db->fetchOne("SELECT 1 FROM blog_posts LIMIT 1");
    } catch (Exception $e) {
        echo "  - Blog posts table not found, skipping...\n";
        $testBlog = null;
    }
    
    if ($testBlog !== null) {
    $blogPosts = [
        [
            'title' => 'Choosing the Right Forklift for Your Warehouse',
            'slug' => 'choosing-right-forklift-warehouse',
            'excerpt' => 'Learn how to select the perfect forklift based on your operational needs, capacity requirements, and workspace constraints.',
            'content' => '<h2>Introduction</h2><p>Selecting the right forklift is crucial for maximizing warehouse efficiency and safety. This guide will help you make an informed decision.</p><h2>Key Considerations</h2><ul><li>Load capacity requirements</li><li>Operating environment (indoor/outdoor)</li><li>Available space and aisle width</li><li>Fuel type preferences</li><li>Budget constraints</li></ul><h2>Conclusion</h2><p>Consult with our experts to find the perfect forklift solution for your needs.</p>',
            'category' => 'Buying Guide',
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ],
        [
            'title' => 'Top 5 Forklift Safety Tips',
            'slug' => 'top-5-forklift-safety-tips',
            'excerpt' => 'Essential safety guidelines every forklift operator should follow to prevent accidents and ensure workplace safety.',
            'content' => '<h2>Safety First</h2><p>Forklift safety is paramount in any warehouse or industrial setting. Follow these essential tips:</p><ol><li>Always perform pre-shift inspections</li><li>Wear proper safety equipment</li><li>Maintain clear visibility</li><li>Never exceed rated capacity</li><li>Keep a safe distance from edges</li></ol><p>Remember, safety is everyone\'s responsibility.</p>',
            'category' => 'Safety',
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
        ],
        [
            'title' => 'Electric vs Gas Forklifts: Complete Comparison',
            'slug' => 'electric-vs-gas-forklifts-comparison',
            'excerpt' => 'Compare electric and gas forklifts to determine which power source is best for your operations.',
            'content' => '<h2>Electric Forklifts</h2><p>Electric forklifts offer zero emissions, quiet operation, and lower maintenance costs. Perfect for indoor use.</p><h2>Gas Forklifts</h2><p>Gas forklifts provide more power and are ideal for outdoor applications and heavy-duty tasks.</p><h2>Which to Choose?</h2><p>Consider your operating environment, capacity needs, and budget when making your decision.</p>',
            'category' => 'Comparison',
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
        ]
    ];
    
        foreach ($blogPosts as $post) {
            $existing = $db->fetchOne("SELECT id FROM blog_posts WHERE slug = :slug", ['slug' => $post['slug']]);
            if (!$existing) {
                $db->insert('blog_posts', $post);
                echo "  ✓ Created: {$post['title']}\n";
            }
        }
    }
    
    echo "\n✅ Sample Data Generation Complete!\n";
    echo "📊 Summary:\n";
    echo "   - Categories: " . count($categories) . "\n";
    echo "   - Products: " . count($products) . "\n";
    echo "   - FAQs: " . (isset($faqs) ? count($faqs) : '0 (table not created)') . "\n";
    echo "   - Testimonials: " . (isset($testimonials) ? count($testimonials) : '0 (table not created)') . "\n";
    echo "   - Blog Posts: " . (isset($blogPosts) ? count($blogPosts) : '0 (table not created)') . "\n";
    echo "\n🎉 Your database is now populated with sample factory equipment data!\n";
    echo "💡 Tip: Run database/even-more-features.sql to create additional tables for FAQs, Testimonials, and Blog posts.\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

