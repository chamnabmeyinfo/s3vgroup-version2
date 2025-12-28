<?php

namespace App\Models;

use App\Database\Connection;

class HeroSlider
{
    private $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function getAll($activeOnly = false)
    {
        $where = $activeOnly ? "WHERE is_active = 1" : "";
        $sql = "SELECT * FROM hero_slides $where ORDER BY display_order ASC, id ASC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM hero_slides WHERE id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO hero_slides (
            title, description, button1_text, button1_url, 
            button2_text, button2_url, background_image,
            background_gradient_start, background_gradient_end,
            content_transparency, is_active, display_order
        ) VALUES (
            :title, :description, :button1_text, :button1_url,
            :button2_text, :button2_url, :background_image,
            :background_gradient_start, :background_gradient_end,
            :content_transparency, :is_active, :display_order
        )";
        
        $params = [
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'button1_text' => $data['button1_text'] ?? null,
            'button1_url' => $data['button1_url'] ?? null,
            'button2_text' => $data['button2_text'] ?? null,
            'button2_url' => $data['button2_url'] ?? null,
            'background_image' => $data['background_image'] ?? null,
            'background_gradient_start' => $data['background_gradient_start'] ?? null,
            'background_gradient_end' => $data['background_gradient_end'] ?? null,
            'content_transparency' => isset($data['content_transparency']) ? (float)$data['content_transparency'] : 0.10,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'display_order' => isset($data['display_order']) ? (int)$data['display_order'] : 0
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        // Check if content_transparency column exists
        $hasTransparency = false;
        try {
            $this->db->fetchOne("SELECT content_transparency FROM hero_slides LIMIT 1");
            $hasTransparency = true;
        } catch (Exception $e) {
            $hasTransparency = false;
        }
        
        $sql = "UPDATE hero_slides SET
            title = :title,
            description = :description,
            button1_text = :button1_text,
            button1_url = :button1_url,
            button2_text = :button2_text,
            button2_url = :button2_url,
            background_image = :background_image,
            background_gradient_start = :background_gradient_start,
            background_gradient_end = :background_gradient_end";
        
        if ($hasTransparency) {
            $sql .= ", content_transparency = :content_transparency";
        }
        
        $sql .= ",
            is_active = :is_active,
            display_order = :display_order,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = :id";
        
        $params = [
            'id' => $id,
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'button1_text' => $data['button1_text'] ?? null,
            'button1_url' => $data['button1_url'] ?? null,
            'button2_text' => $data['button2_text'] ?? null,
            'button2_url' => $data['button2_url'] ?? null,
            'background_image' => $data['background_image'] ?? null,
            'background_gradient_start' => $data['background_gradient_start'] ?? null,
            'background_gradient_end' => $data['background_gradient_end'] ?? null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'display_order' => isset($data['display_order']) ? (int)$data['display_order'] : 0
        ];
        
        if ($hasTransparency) {
            $params['content_transparency'] = isset($data['content_transparency']) ? (float)$data['content_transparency'] : 0.10;
        }
        
        return $this->db->query($sql, $params);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM hero_slides WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    public function toggleActive($id)
    {
        $sql = "UPDATE hero_slides SET is_active = NOT is_active WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    public function updateOrder($id, $order)
    {
        $sql = "UPDATE hero_slides SET display_order = :order WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'order' => $order]);
    }
}

