<?php


namespace Vehica\Managers;


use Vehica\Core\Manager;

/**
 * Class UploadImageManager
 * @package Vehica\Managers
 */
class UploadImageManager extends Manager
{

    public function boot()
    {
        add_action('admin_post_vehica_upload_image', [$this, 'upload']);

        add_action('admin_post_nopriv_vehica_upload_image', [$this, 'upload']);
    }

    public function upload()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vehica_upload_image')) {
            return;
        }

        echo esc_html(self::uploadImage());
    }

    /**
     * @return int
     */
    public static function uploadImage()
    {
        $file = wp_handle_upload($_FILES['file'], ['test_form' => false]);

        $attachment = [
            'guid' => $file['url'],
            'post_mime_type' => $file['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($file['url'])),
            'post_content' => '',
            'post_status' => 'inherit'
        ];

        $imageId = wp_insert_attachment($attachment, $file['file']);

        if (is_wp_error($imageId)) {
            return 0;
        }

        update_post_meta($imageId, 'vehica_source', 'panel');

        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $imageData = wp_generate_attachment_metadata($imageId, $file['file']);
        wp_update_attachment_metadata($imageId, $imageData);

        return $imageId;
    }

}