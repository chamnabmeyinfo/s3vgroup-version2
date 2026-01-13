-- Demo CEO Message Data
-- This file contains demo data for the CEO message feature
-- You can import this to populate the ceo_message table with sample content
-- First, clear any existing demo data (optional - comment out if you want to keep existing data)
-- DELETE FROM ceo_message WHERE ceo_name = 'John Smith';
-- Insert demo CEO message
INSERT INTO ceo_message (
        ceo_name,
        ceo_title,
        greeting,
        message_content,
        signature_name,
        signature_title,
        is_active
    )
VALUES (
        'John Smith',
        'Chief Executive Officer',
        'Dear Valued Customers, Partners, and Friends,',
        '<p>It is with immense pleasure and deep gratitude that I welcome you to <strong>S3V Group</strong>. As the Chief Executive Officer, I am honored to lead a team of exceptional professionals who share an unwavering commitment to excellence, innovation, and customer satisfaction.</p>

<p>Since our founding, we have built our reputation on three fundamental pillars: <strong>Quality</strong>, <strong>Integrity</strong>, and <strong>Partnership</strong>. These core values are not just words on our website—they are the guiding principles that drive every decision we make, every product we deliver, and every relationship we build.</p>

<p>In the dynamic world of industrial equipment and material handling solutions, we understand that your success depends on more than just reliable machinery. It requires a trusted partner who understands your unique challenges, anticipates your needs, and provides comprehensive support at every stage of your journey.</p>

<p>Our commitment to you extends far beyond the point of sale. We invest heavily in:</p>

<ul style="margin-left: 2rem; margin-top: 1rem; margin-bottom: 1rem;">
    <li><strong>Training & Education:</strong> Ensuring your team is fully equipped to maximize the potential of every piece of equipment</li>
    <li><strong>Maintenance & Support:</strong> Proactive service programs that minimize downtime and extend equipment life</li>
    <li><strong>Innovation:</strong> Continuously exploring new technologies and solutions to keep you ahead of the competition</li>
    <li><strong>Customer Service:</strong> A dedicated support team available when you need us most</li>
</ul>

<p>As we look toward the future, we remain steadfast in our mission to be your most trusted partner in industrial excellence. We are constantly evolving, investing in our people, our technology, and our processes to ensure we can serve you better today than we did yesterday.</p>

<p>Your success is our success. When you thrive, we thrive. This philosophy has been the cornerstone of our growth and will continue to guide us as we expand our services, enhance our product offerings, and strengthen our commitment to the communities we serve.</p>

<p>I invite you to explore our website, connect with our team, and discover how we can help elevate your operations to new heights. Whether you are a long-standing partner or considering us for the first time, we are here to listen, understand, and deliver solutions that exceed your expectations.</p>

<p>Thank you for being part of our journey. Together, we will continue to build a future defined by excellence, innovation, and mutual success.</p>',
        'John Smith',
        'Chief Executive Officer',
        1
    ) ON DUPLICATE KEY
UPDATE ceo_name =
VALUES(ceo_name),
    ceo_title =
VALUES(ceo_title),
    greeting =
VALUES(greeting),
    message_content =
VALUES(message_content),
    signature_name =
VALUES(signature_name),
    signature_title =
VALUES(signature_title),
    is_active =
VALUES(is_active);