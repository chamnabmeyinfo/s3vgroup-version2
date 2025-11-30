<?php
/**
 * Email Helper Class
 * Handles email notifications
 */
namespace App\Helpers;

class EmailHelper {
    /**
     * Queue an email to be sent
     */
    public static function queue($to, $subject, $body) {
        try {
            db()->insert('email_queue', [
                'to_email' => $to,
                'subject' => $subject,
                'body' => $body,
                'status' => 'pending'
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Send notification email for new quote request
     */
    public static function sendQuoteNotification($quoteData) {
        $subject = "New Quote Request - " . ($quoteData['product_name'] ?? 'General Inquiry');
        $body = "
        <h2>New Quote Request</h2>
        <p><strong>Name:</strong> {$quoteData['name']}</p>
        <p><strong>Email:</strong> {$quoteData['email']}</p>
        <p><strong>Phone:</strong> " . ($quoteData['phone'] ?? 'N/A') . "</p>
        <p><strong>Company:</strong> " . ($quoteData['company'] ?? 'N/A') . "</p>
        <p><strong>Message:</strong></p>
        <p>" . nl2br($quoteData['message'] ?? '') . "</p>
        ";
        
        $settingModel = new \App\Models\Setting();
        $adminEmail = $settingModel->get('admin_email', 'admin@example.com');
        return self::queue($adminEmail, $subject, $body);
    }
    
    /**
     * Send notification email for new contact message
     */
    public static function sendContactNotification($messageData) {
        $subject = "New Contact Message - " . ($messageData['subject'] ?? 'No Subject');
        $body = "
        <h2>New Contact Message</h2>
        <p><strong>Name:</strong> {$messageData['name']}</p>
        <p><strong>Email:</strong> {$messageData['email']}</p>
        <p><strong>Phone:</strong> " . ($messageData['phone'] ?? 'N/A') . "</p>
        <p><strong>Message:</strong></p>
        <p>" . nl2br($messageData['message'] ?? '') . "</p>
        ";
        
        $settingModel = new \App\Models\Setting();
        $adminEmail = $settingModel->get('admin_email', 'admin@example.com');
        return self::queue($adminEmail, $subject, $body);
    }
    
    /**
     * Send order confirmation email
     */
    public static function sendOrderConfirmation($orderData) {
        $subject = "Order Confirmation - " . ($orderData['order_number'] ?? '');
        $body = "
        <h2>Thank you for your order!</h2>
        <p>Your order #{$orderData['order_number']} has been received.</p>
        <p>We will contact you shortly to confirm your order and arrange payment.</p>
        ";
        
        return self::queue($orderData['email'], $subject, $body);
    }
}

